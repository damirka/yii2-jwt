<?php

namespace damirka\JWT;

/**
 * Declaration of methods needed for correct JWT handling
 */
interface IdentityInterface extends \yii\web\IdentityInterface
{
    /**
     * Getter for secret key that's used for generation of JWT
     * @return string Secret key used to generate JWT
     */
    public static function getSecretKey();

    /**
     * Getter for "header" array that's used for generation of JWT
     * @return array JWT Header Token param, see http://jwt.io/ for details
     */
    public static function getHeaderToken();
}
