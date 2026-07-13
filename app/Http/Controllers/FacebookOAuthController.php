<?php

namespace App\Http\Controllers;

use App\Models\FacebookSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookOAuthController extends Controller
{
    private string $appId;
    private string $appSecret;

    public function __construct()
    {
        $this->appId = config('services.facebook.client_id');
        $this->appSecret = config('services.facebook.client_secret');
    }

    private function getRedirectUri(): string
    {
        return url('/facebook/callback');
    }

    public function redirect()
    {
        $params = http_build_query([
            'client_id'     => $this->appId,
            'redirect_uri'  => $this->getRedirectUri(),
            'scope'         => 'pages_show_list,pages_messaging,pages_read_engagement',
            'response_type' => 'code',
            'state'         => csrf_token(),
        ]);

        return redirect("https://www.facebook.com/v21.0/dialog/oauth?{$params}");
    }

    public function callback(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'state' => 'required|string',
        ]);

        if ($request->state !== csrf_token()) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Invalid state parameter. Aborting.');
        }

        $tokenResponse = $this->exchangeCodeForToken($request->code);

        if (! $tokenResponse) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Facebook theke token pawa jayn। আবার চেষ্টা করুন।');
        }

        $pages = $this->getPages($tokenResponse['access_token']);

        if (! $pages || empty($pages)) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Kono Facebook Page pawa jayn। অনুগ্রহ করে একটি Page তৈরি করুন।');
        }

        session([
            'fb_access_token' => $tokenResponse['access_token'],
            'fb_pages'        => $pages,
        ]);

        if (count($pages) === 1) {
            return $this->connectPage($pages[0]);
        }

        return redirect()->route('facebook.select.page');
    }

    public function selectPage()
    {
        $pages = session('fb_pages', []);

        if (empty($pages)) {
            return redirect()->route('facebook.settings')
                ->with('error', 'No pages found. Please reconnect.');
        }

        return view('tenant.facebook-select-page', ['pages' => $pages]);
    }

    public function connectSelectedPage(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string',
        ]);

        $pages = session('fb_pages', []);
        $selected = collect($pages)->firstWhere('id', $request->page_id);

        if (! $selected) {
            return redirect()->route('facebook.select.page')
                ->with('error', 'Invalid page selected.');
        }

        return $this->connectPage($selected);
    }

    private function connectPage(array $page): \Illuminate\Http\RedirectResponse
    {
        $accessToken = session('fb_access_token');

        $pageToken = $this->getPageAccessToken($page['id'], $accessToken);

        if (! $pageToken) {
            return redirect()->route('facebook.settings')
                ->with('error', 'Page access token pawa jayn।');
        }

        FacebookSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'app_id'            => $this->appId,
                'app_secret'        => $this->appSecret,
                'verify_token'      => 'socialboost_verify_token_2026',
                'page_id'           => $page['id'],
                'page_access_token' => $pageToken,
            ]
        );

        session()->forget(['fb_access_token', 'fb_pages']);

        return redirect()->route('facebook.settings')
            ->with('success', " '{$page['name']}' page সফলভাবে সংযুক্ত হয়েছে!");
    }

    private function exchangeCodeForToken(string $code): ?array
    {
        try {
            $response = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'client_id'     => $this->appId,
                'client_secret' => $this->appSecret,
                'redirect_uri'  => $this->getRedirectUri(),
                'code'          => $code,
            ]);

            if (! $response->successful()) {
                Log::error('Facebook token exchange failed', ['response' => $response->json()]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Facebook token exchange error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getPages(string $accessToken): ?array
    {
        try {
            $response = Http::get("https://graph.facebook.com/v21.0/me/accounts", [
                'access_token' => $accessToken,
                'fields'       => 'id,name,category,access_token',
            ]);

            if (! $response->successful()) {
                Log::error('Facebook pages fetch failed', ['response' => $response->json()]);
                return null;
            }

            $data = $response->json('data', []);

            return empty($data) ? null : $data;
        } catch (\Exception $e) {
            Log::error('Facebook pages fetch error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getPageAccessToken(string $pageId, string $userToken): ?string
    {
        try {
            $response = Http::get("https://graph.facebook.com/v21.0/{$pageId}", [
                'access_token' => $userToken,
                'fields'       => 'access_token',
            ]);

            if (! $response->successful()) {
                Log::error('Facebook page token failed', ['response' => $response->json()]);
                return null;
            }

            return $response->json('access_token');
        } catch (\Exception $e) {
            Log::error('Facebook page token error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
