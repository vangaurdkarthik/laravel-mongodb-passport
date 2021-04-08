<?php

namespace MongoPassport\Mongodb;

use Illuminate\Support\ServiceProvider;
use MongoPassport\Mongodb\Passport\AuthCode;
use MongoPassport\Mongodb\Passport\Bridge\RefreshTokenRepository;
use MongoPassport\Mongodb\Passport\Client;
use MongoPassport\Mongodb\Passport\PersonalAccessClient;
use MongoPassport\Mongodb\Passport\RefreshToken;
use MongoPassport\Mongodb\Passport\Token;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Laravel\Passport\Passport;

class MongodbPassportServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::useClientModel(Client::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        Passport::useTokenModel(Token::class);

        $this->app->bind(PassportRefreshTokenRepository::class, function () {
            return $this->app->make(RefreshTokenRepository::class);
        });
    }
}
