<?php

namespace App\Http\Controllers;

use App\Services\AmoCRM\AmoCRMService;
use http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TokenController
{
    protected $amo = null;

    public function saveToken(Request $request): RedirectResponse
    {
        $this->amo = (new AmoCRMService())->get_client();

        if ($request->has("referer")) {
            $this->amo->setAccountBaseDomain($request->get("referer"));
        }

        /**
         * Ловим обратный код
         */
        try {
            $code = $request->get("code");

            $accessToken = $this->amo->getOAuthClient()->getAccessTokenByCode($code);

            if (!$accessToken->hasExpired()) {

                $this->save([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $this->amo->getAccountBaseDomain(),
                ]);
            }

        } catch (\Exception $e) {
            die((string)$e);
        }

        return \response()->redirectToRoute("home");
    }

    public function getToken(Request $request)
    {

        $this->amo = (new AmoCRMService())->get_client();

        if ($request->has("referer")) {
            $this->amo->setAccountBaseDomain($request->get("referer"));
        }

        if (!$request->has("code")) {

            $state = bin2hex(random_bytes(16));

            $request->session()->put("oauth2state", $state);

            if ($request->has("button")) {

                return view("install_integration", [
                    "amo" => $this->amo,
                    "state" => $state,
                ]);

            } else {

                $authorizationUrl = $this->amo->getOAuthClient()->getAuthorizeUrl([
                    'state' => $state,
                    'mode' => 'post_message',
                ]);

                return response()->redirectTo($authorizationUrl);
            }

        } elseif (
            !$request->has("from_widget") &&
            (empty($request->get("state")) ||
                empty($request->session()->get("oauth2state")) ||
                ($request->get("state") !== $request->session()->get("oauth2state"))
            )
        ) {

            $request->session()->remove("oauth2state");

            return view("invalid", [
                "message" => "Invalid state",
            ]);
        }

        return view("invalid", [
            "message" => "Invalid state 2",
        ]);
    }

    private function save($accessToken): void
    {
        $token_path = (new AmoCRMService())->getTokenPath();

        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];

            file_put_contents($token_path, json_encode($data));

        } else {

            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }
}
