<?php

namespace damirka\JWT;

/**
 * REST Access filter for handling JWT authorization
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
     * Cuts off first 7 symbols of header string as they must be 'Bearer\ '
     * @param  string $header Correctly formed header
     * @return string         Parsed JWT
     */
    public static function cutHeader($header)
    {
        return str_replace("Bearer ", "", $header);
    }
}
