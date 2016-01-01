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
     * Getter for secret key that's used for generation of JWT
     * @return string secret key used to generate JWT
     */
    abstract protected static function getSecretKey();

    /**
     * Getter for "header" array that's used for generation of JWT
     * @return array JWT Header Token param, see http://jwt.io/ for details
     */
    abstract protected static function getHeaderToken();

    /**
     * Logins user by given JWT encoded string. If string is correctly decoded
     * - array (token) must contain 'jti' param - the id of existing user
     * @param  string $accessToken access token to decode
     * @return mixed|null          User model or null if there's no user
     * @throws \yii\web\ForbiddenHttpException if anything went wrong
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        $decodedArray = static::decodeJWT($token);

        // If there's no jti param - exception
        if (!isset($decodedArray['jti'])) {
            throw new UnauthorizedHttpException($errorText);
        }

        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = $decodedArray['jti'];

        return static::findByJTI($id);
    }

    /**
     * Decode JWT token
     * @param  string $token access token to decode
     * @return array decoded token
     */
    public static function decodeJWT($token){
        $secret = static::getSecretKey();
        $errorText = "Incorrect token";

        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [static::getAlgo()]);
        }
        catch (\Exception $e) {
            throw new UnauthorizedHttpException($errorText);
        }

        $decodedArray = (array) $decoded;

        return $decodedArray;
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
        $model = static::findOne($id);
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
        //use primary key for JTI
        return $this->getPrimaryKey();
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @param  array $payloads payloads data to set, default value is empty array. See registered claim names for payloads at https://tools.ietf.org/html/rfc7519#section-4.1
     * @return sting encoded JWT
     */
    public function getJWT($payloads = [])
    {
        $secret = static::getSecretKey();

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge($payloads, static::getHeaderToken());

        // Set up id user
        $token['jti'] = $this->getJTI();

        return JWT::encode($token, $secret, static::getAlgo());
    }

    /**
    * Get payload data in a JWT string
    * @param string|null $payload_id Payload ID that want to return, the default value is NULL. If NULL it will return all the payloads data
    * @return mixed payload data
    */
    public static function getPayload($payload_id = null){
        $authHeader = static::getHeaderToken();
        //replace Bearer in header token
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $authHeader = $matches[1]; //token
        }
        $decoded_array = static::decodeJWT($authHeader);

        if($payload_id != null){
            return isset($decoded_array[$payload_id]) ? $decoded_array[$payload_id] : null;
        }else{
            return $decoded_array;
        }
    }
}
