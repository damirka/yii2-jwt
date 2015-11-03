<?php

namespace damirka\JWT;

use Firebase\JWT\JWT;

use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * Trait to handle JWT-authorization process. Should be attached to User model.
 * If there are many applications using user model in different ways - best way
 * is to use this trait only in the JWT related part.
 */
trait UserTrait
{
    /**
     * Logins user by given JWT encoded string. If string is correctly decoded
     * - array (token) must contain 'jti' param - the id of existing user
     * @param  string $accessToken access token to decode
     * @return mixed|null          User model or null if there's no user
     * @throws \yii\web\ForbiddenHttpException if anything went wrong
     */
    public static function findIdentityByAccessToken($token, $type = null) {

        $secret = self::getSecretKey();
        $errorText = "Incorrect token";

        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [self::getAlgo()]);
        }
        catch (\Exception $e) {
            throw new UnauthorizedHttpException($errorText);
        }

        $decodedArray = (array) $decoded;

        // If there's no jti param - exception
        if (!isset($decodedArray['jti'])) {
            throw new UnauthorizedHttpException($errorText);
        }

        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = $decodedArray['jti'];

        return self::findByJTI($id);
    }

    /**
     * Finds User model using static method findOne
     * Override this method in model if you need to complicate id-management
     * @param  integer $id if of user to search
     * @return mixed       User model
     * @throws \yii\web\ForbiddenHttpException if model is not found
     */
    public static function findByJTI($id)
    {
        $model = self::findOne($id);
        $errorText = "Incorrect token";

        // Throw error if user is missing
        if (empty($model)) {
            throw new UnauthorizedHttpException($errorText);
        }

        return $model;
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo()
    {
        return 'HS256';
    }

    /**
     * Returns some 'id' to encode to token. By default is current model id.
     * If you override this method, be sure that findByJTI is updated too
     * @return integer any unique integer identifier of user
     */
    public function getJTI()
    {
        return $this->getId();
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @return sting encoded JWT
     */
    public function getJWT()
    {
        // Collect all the data
        $secret = self::getSecretKey();
        $currentTime = time();
        $hostInfo = 'example.com';//Yii::$app->request->hostInfo;

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([

            'iss' => $hostInfo,
            'aud' => $hostInfo,
            'iat' => $currentTime,
            'nbf' => $currentTime

        ], self::getHeaderToken());

        // Set up id
        $token['jti'] = $this->getJTI();

        return JWT::encode($token, $secret, self::getAlgo());
    }
}
