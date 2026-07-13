<?php

namespace App\Http\Controllers;

use App\Models\FacebookSetting;
use App\Services\ZernioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $zernio = new ZernioService($apiKey);
        if (! $zernio->verifyKey()) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Zernio API key সঠিক নয়। আবার চেষ্টা করুন।');
        }

        $user = Auth::user();
        $existingSetting = FacebookSetting::where('user_id', $user->id)->first();

        if ($existingSetting && $existingSetting->zernio_api_key === $apiKey) {
            return redirect()->route('facebook.settings')
                ->with('success', 'Zernio API key আগেই সংরক্ষিত আছে।');
        }

        $profiles = $zernio->listProfiles();
        $profileId = null;

        if (! empty($profiles)) {
            $profileId = $profiles[0]['_id'];
        } else {
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

        $redirectUrl = route('zernio.facebook.callback');
        $result = $zernio->getFacebookConnectUrl($facebookSetting->zernio_profile_id, $redirectUrl);

        if (! $result || ! isset($result['authUrl'])) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Facebook সংযোগ URL তৈরি করা যায়নি। আবার চেষ্টা করুন।');
        }

        session(['zernio_state' => $result['state'] ?? null]);

        return redirect($result['authUrl']);
    }

    /**
     * Step 3: Facebook OAuth callback from Zernio.
     * Zernio redirects here after user authorizes Facebook.
     * Callback includes tempToken which we use to list pages.
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

        $tempToken = $request->query('tempToken') ?? $request->query('temp_token') ?? $request->query('token');
        $code = $request->query('code');

        Log::info('Zernio Facebook callback received', [
            'has_temp_token' => $tempToken !== null,
            'has_code' => $code !== null,
            'all_params' => $request->query(),
        ]);

        $zernio = new ZernioService($facebookSetting->zernio_api_key);

        // If we have a tempToken, use it to get page list directly
        if ($tempToken) {
            session(['zernio_temp_token' => $tempToken]);

            $pageResult = $zernio->getFacebookSelectPageUrl(
                $facebookSetting->zernio_profile_id,
                $tempToken
            );

            Log::info('Zernio select-page result', ['result' => $pageResult]);

            if ($pageResult && isset($pageResult['pages']) && ! empty($pageResult['pages'])) {
                session(['zernio_pages' => $pageResult['pages']]);

                return redirect()->route('zernio.select.page');
            }

            // Even if no pages endpoint, the account might be connected — try listing accounts
        }

        // Fallback: list connected accounts
        $accounts = $zernio->listAccounts($facebookSetting->zernio_profile_id);
        $facebookAccounts = array_values(array_filter($accounts, fn ($a) => ($a['platform'] ?? '') === 'facebook'));

        Log::info('Zernio accounts list', [
            'total' => count($accounts),
            'facebook' => count($facebookAccounts),
            'accounts' => $facebookAccounts,
        ]);

        if (! empty($facebookAccounts)) {
            $facebookAccount = $facebookAccounts[0];
            $accountId = $facebookAccount['_id'] ?? null;

            if ($accountId) {
                session(['zernio_selected_account_id' => $accountId]);

                // Try to get pages for this account
                $pages = $zernio->listFacebookPages($accountId);

                Log::info('Zernio Facebook pages', ['pages' => $pages, 'account_id' => $accountId]);

                if (! empty($pages)) {
                    session(['zernio_pages' => $pages]);
                }
            }
        }

        return redirect()->route('zernio.select.page');
    }

    /**
     * Step 4: Show Facebook page selection.
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

        // Pages from session (set during callback)
        $pages = session('zernio_pages', []);
        $accountId = session('zernio_selected_account_id');
        $tempToken = session('zernio_temp_token');

        // If no pages in session, try fetching
        if (empty($pages) && $accountId) {
            $zernio = new ZernioService($facebookSetting->zernio_api_key);
            $pages = $zernio->listFacebookPages($accountId);
            session(['zernio_pages' => $pages]);
        }

        if (empty($pages)) {
            return redirect()->route('facebook.settings')
                ->with('error', 'কোনো Facebook Page পাওয়া যায়নি। Zernio থেকে Facebook আবার সংযুক্ত করুন।');
        }

        return view('dashboard.facebook-select-page', [
            'pages' => $pages,
            'accountId' => $accountId,
            'tempToken' => $tempToken,
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
            'account_id' => 'nullable|string',
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

        // If we have a tempToken, use the select-page API to complete connection
        $tempToken = session('zernio_temp_token');
        $accountId = $request->account_id ?? session('zernio_selected_account_id');

        if ($tempToken && $facebookSetting->zernio_profile_id) {
            $zernio->selectFacebookPage(
                $facebookSetting->zernio_profile_id,
                $request->page_id,
                $tempToken,
            );
        }

        // If we have an accountId, set the default page
        if ($accountId) {
            $zernio->setDefaultFacebookPage($accountId, $request->page_id);
        }

        $facebookSetting->update([
            'zernio_account_id' => $accountId,
            'page_id' => $request->page_id,
            'page_name' => $request->page_name,
        ]);

        session()->forget([
            'zernio_facebook_accounts',
            'zernio_selected_account_id',
            'zernio_state',
            'zernio_temp_token',
            'zernio_pages',
        ]);

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
