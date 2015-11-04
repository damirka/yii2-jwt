<?php

namespace damirka\JWT;

/**
 * Filter for handling JWT authorization in REST controller
 */
class AccessFilter extends \yii\filters\auth\AuthMethod
{
    /**
     * Name of HTTP header where to get token from
     * @var string
     */
    public $tokenHeader = 'Authorization';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $headerString = $request->getHeaders()->get($this->tokenHeader);
        $accessToken = self::cutHeader($headerString);

        // Check for 'false' value
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }

        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }

    /**
     * Removes 'Bearer\ ' from the header as it must be according to RFC 7519
     * @param  string $header correctly formed header
     * @return string         raw JWT token
     */
    public static function cutHeader($header)
    {
        return str_replace("Bearer ", "", $header);
    }
}
