<?php

namespace App\Http\Controllers;

use App\Models\FacebookSetting;
use App\Services\ZernioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZernioOAuthController extends Controller
{
    /**
     * Step 1: Save Zernio API key and verify it.
     */
    public function storeApiKey(Request $request)
    {
        $request->validate([
            'zernio_api_key' => 'required|string',
        ]);

        $apiKey = $request->input('zernio_api_key');

        // Verify the API key works
        $zernio = new ZernioService($apiKey);
        if (! $zernio->verifyKey()) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Zernio API key সঠিক নয়। আবার চেষ্টা করুন।');
        }

        // Get or create a profile for this tenant
        $user = Auth::user();
        $existingSetting = FacebookSetting::where('user_id', $user->id)->first();

        // If already has a Zernio setting with same key, just return
        if ($existingSetting && $existingSetting->zernio_api_key === $apiKey) {
            return redirect()->route('facebook.settings')
                ->with('success', 'Zernio API key আগেই সংরক্ষিত আছে।');
        }

        // First, try to use an existing profile
        $profiles = $zernio->listProfiles();
        $profileId = null;

        if (! empty($profiles)) {
            // Use the first existing profile
            $profileId = $profiles[0]['_id'];
        } else {
            // Create a new profile with a unique name
            $profileName = ($user->company ?? $user->name ?? 'SocialBoost').' '.time();
            $profile = $zernio->createProfile($profileName, 'SocialBoost AI managed profile');

            if ($profile && isset($profile['_id'])) {
                $profileId = $profile['_id'];
            }
        }

        if (! $profileId) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Zernio profile তৈরি করা যায়নি। আবার চেষ্টা করুন।');
        }

        // Save or update facebook_settings
        $facebookSetting = $existingSetting ?: new FacebookSetting;
        $facebookSetting->user_id = $user->id;
        $facebookSetting->connection_type = 'zernio';
        $facebookSetting->zernio_api_key = $apiKey;
        $facebookSetting->zernio_profile_id = $profileId;
        $facebookSetting->save();

        return redirect()->route('facebook.settings')
            ->with('success', 'Zernio সফলভাবে সংযুক্ত হয়েছে! এখন Facebook Page সংযুক্ত করুন।');
    }

    /**
     * Step 2: Start Facebook OAuth via Zernio.
     * Redirects user to Zernio's Facebook authorization page.
     */
    public function connectFacebook()
    {
        $user = Auth::user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)
            ->where('connection_type', 'zernio')
            ->first();

        if (! $facebookSetting || ! $facebookSetting->zernio_api_key) {
            return redirect()->route('facebook.settings')
                ->with('error', 'প্রথমে Zernio API key সংরক্ষণ করুন।');
        }

        $zernio = new ZernioService($facebookSetting->zernio_api_key);

        // Ensure we have a profile
        if (! $facebookSetting->zernio_profile_id) {
            // First, try to use an existing profile
            $profiles = $zernio->listProfiles();
            if (! empty($profiles)) {
                $facebookSetting->zernio_profile_id = $profiles[0]['_id'];
            } else {
                $profileName = ($user->company ?? $user->name ?? 'SocialBoost').' '.time();
                $profile = $zernio->createProfile($profileName);
                if (! $profile || ! isset($profile['_id'])) {
                    return redirect()->route('facebook.settings')
                        ->with('error', 'Zernio profile তৈরি করা যায়নি।');
                }
                $facebookSetting->zernio_profile_id = $profile['_id'];
            }
            $facebookSetting->save();
        }

        // Get Facebook connect URL from Zernio
        $redirectUrl = route('zernio.facebook.callback');
        $result = $zernio->getFacebookConnectUrl($facebookSetting->zernio_profile_id, $redirectUrl);

        if (! $result || ! isset($result['authUrl'])) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Facebook সংযোগ URL তৈরি করা যায়নি। আবার চেষ্টা করুন।');
        }

        // Store state in session for verification
        session(['zernio_state' => $result['state'] ?? null]);

        return redirect($result['authUrl']);
    }

    /**
     * Step 3: Facebook OAuth callback from Zernio.
     * After user authorizes Facebook, Zernio redirects back here.
     */
    public function facebookCallback(Request $request)
    {
        $user = Auth::user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)
            ->where('connection_type', 'zernio')
            ->first();

        if (! $facebookSetting || ! $facebookSetting->zernio_api_key) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Zernio সেটিংস পাওয়া যায়নি।');
        }

        // Check for error from Zernio
        if ($request->has('error')) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Facebook সংযোগ ব্যর্থ হয়েছে: '.($request->query('error', 'Unknown error')));
        }

        $zernio = new ZernioService($facebookSetting->zernio_api_key);

        // List connected accounts to find the Facebook account
        $accounts = $zernio->listAccounts($facebookSetting->zernio_profile_id);

        $facebookAccounts = array_filter($accounts, fn ($a) => $a['platform'] === 'facebook');

        if (empty($facebookAccounts)) {
            return redirect()->route('facebook.settings')
                ->with('error', 'কোনো Facebook Account পাওয়া যায়নি। আবার চেষ্টা করুন।');
        }

        // Store accounts in session for page selection
        $facebookAccount = collect($facebookAccounts)->first();
        session([
            'zernio_facebook_accounts' => array_values($facebookAccounts),
            'zernio_selected_account_id' => $facebookAccount['_id'],
        ]);

        // If only one account, auto-select and go to page selection
        if (count($facebookAccounts) === 1) {
            return redirect()->route('zernio.select.page');
        }

        return redirect()->route('zernio.select.page');
    }

    /**
     * Step 4: Show Facebook page selection (from Zernio connected account).
     */
    public function selectPage()
    {
        $user = Auth::user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)
            ->where('connection_type', 'zernio')
            ->first();

        if (! $facebookSetting || ! $facebookSetting->zernio_api_key) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Zernio সেটিংস পাওয়া যায়নি।');
        }

        $zernio = new ZernioService($facebookSetting->zernio_api_key);

        // Get available Facebook pages from the connected account
        $accounts = $zernio->listAccounts($facebookSetting->zernio_profile_id);
        $facebookAccounts = array_filter($accounts, fn ($a) => $a['platform'] === 'facebook');

        if (empty($facebookAccounts)) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Facebook Account পাওয়া যায়নি।');
        }

        $accountId = session('zernio_selected_account_id') ?? collect($facebookAccounts)->first()['_id'];

        // Try to get pages from the account
        $pages = $zernio->listFacebookPages($accountId);

        // If no pages found via API, the account itself is the page
        if (empty($pages)) {
            // Use the account info as page info
            $account = collect($facebookAccounts)->firstWhere('_id', $accountId);
            if ($account) {
                $pages = [
                    [
                        'id' => $accountId,
                        'name' => $account['displayName'] ?? $account['username'] ?? 'Facebook Page',
                    ],
                ];
            }
        }

        return view('dashboard.facebook-select-page', [
            'pages' => $pages,
            'accountId' => $accountId,
            'source' => 'zernio',
        ]);
    }

    /**
     * Step 5: Connect the selected Facebook page.
     */
    public function connectSelectedPage(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string',
            'page_name' => 'nullable|string',
            'account_id' => 'required|string',
        ]);

        $user = Auth::user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)
            ->where('connection_type', 'zernio')
            ->first();

        if (! $facebookSetting || ! $facebookSetting->zernio_api_key) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Zernio সেটিংস পাওয়া যায়নি।');
        }

        $zernio = new ZernioService($facebookSetting->zernio_api_key);

        // Set the default Facebook page
        $zernio->setDefaultFacebookPage($request->account_id, $request->page_id);

        // Update facebook_settings
        $facebookSetting->update([
            'zernio_account_id' => $request->account_id,
            'page_id' => $request->page_id,
            'page_name' => $request->page_name,
        ]);

        // Clean up session
        session()->forget(['zernio_facebook_accounts', 'zernio_selected_account_id', 'zernio_state']);

        return redirect()->route('facebook.settings')
            ->with('success', "'{$request->page_name}' Facebook Page সফলভাবে Zernio দিয়ে সংযুক্ত হয়েছে!");
    }

    /**
     * Disconnect Zernio and remove Facebook settings.
     */
    public function disconnect()
    {
        $user = Auth::user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)->first();

        if ($facebookSetting) {
            // If connected via Zernio, disconnect the account from Zernio
            if ($facebookSetting->connection_type === 'zernio' && $facebookSetting->zernio_account_id) {
                $zernio = new ZernioService($facebookSetting->zernio_api_key);
                $zernio->disconnectAccount($facebookSetting->zernio_account_id);
            }

            $facebookSetting->delete();
        }

        return redirect()->route('facebook.settings')
            ->with('success', 'Facebook সংযোগ সফলভাবে বিচ্ছিন্ন হয়েছে।');
    }
}
