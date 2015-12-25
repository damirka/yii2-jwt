# yii2-jwt
JWT implementation for Yii2 Authorization process

## Installation

To install (only master is available now) run:
```
    composer require "damirka/yii2-jwt:dev-master"
```
Or add this line to *require* section of composer.json:
```
    "damirka/yii2-jwt:dev-master": "dev-master"
```

## Usage

There is only one trait - *UserTrait* - which gives you 5 methods for
authorization and JWT-management in User model

Set up:

In controller:

```PHP
<?php

// ...

use yii\filters\auth\HttpBearerAuth;

class BearerAuthController extends \yii\rest\ActiveController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'bearerAuth' => [
                'class' => HttpBearerAuth::className()
            ]
        ]);
    }
}
```

In User model:

```PHP
<?php

// ...

use yii\db\ActiveRecord;
use yii\web\IdentityInterface

class User extends ActiveRecord implements IdentityInterface
{
    // Just use trait in User model, implement two abstract methods and that's
    // all you've got to do
    use \damirka\JWT\UserTrait;

    // ...
}
```
