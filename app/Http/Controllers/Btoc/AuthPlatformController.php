<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ECPlatforms\PlatformFactory;
use Illuminate\Http\Request;

class AuthPlatformController extends Controller
{
    private PlatformFactory $platformFactory;

    public function __construct(PlatformFactory $platformFactory)
    {
        $this->platformFactory = $platformFactory;
    }

    /**
     * Redirect the user to the EC platform's OAuth login page.
     */
    public function redirectToProvider(Request $request, Shop $shop)
    {
        $platformCode  = $shop->ecPlatform->code;
        $authenticator = $this->platformFactory->makeAuthenticator($platformCode);

        return redirect()->away($authenticator->getAuthUrl($shop));
    }

    /**
     * Handle the OAuth callback from the EC platform.
     */
    public function handleProviderCallback(Request $request, Shop $shop)
    {
        $platformCode  = $shop->ecPlatform->code;
        $authenticator = $this->platformFactory->makeAuthenticator($platformCode);

        try {
            $authenticator->handleCallback($shop, $request->all());

            return redirect()
                ->route('btoc.shop.show', $shop->id)
                ->with('success', "Shop 「{$shop->shop_name}」 has been connected to {$shop->ecPlatform->name} successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->route('btoc.shop.show', $shop->id)
                ->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }
}
