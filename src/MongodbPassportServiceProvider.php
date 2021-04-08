<?php

namespace MongoPassport\Mongodb;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\ServiceProvider;
use MongoPassport\Mongodb\Passport\AuthCode;
use MongoPassport\Mongodb\Passport\Bridge\RefreshTokenRepository;
use MongoPassport\Mongodb\Passport\Client;
use MongoPassport\Mongodb\Passport\PersonalAccessClient;
use MongoPassport\Mongodb\Passport\RefreshToken;
use MongoPassport\Mongodb\Passport\Token;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportUserProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use MongoPassport\Mongodb\Passport\ClientRepository;
use MongoPassport\Mongodb\Passport\Console\ClientCommand;

class MongodbPassportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->commands([
                ClientCommand::class
            ]);
        }
    }

    /**
     * @return void
     */
    public function register()
    {
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::useClientModel(Client::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        Passport::useTokenModel(Token::class);
        $this->registerClientRepository();
        $this->app->bind(PassportRefreshTokenRepository::class, function () {
            return $this->app->make(RefreshTokenRepository::class);
        });
    }

    /**
     * Make the authorization service instance.
     *
     * @return \League\OAuth2\Server\AuthorizationServer
     */
    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            $this->app->make(Bridge\ClientRepository::class),
            $this->app->make(Bridge\AccessTokenRepository::class),
            $this->app->make(Bridge\ScopeRepository::class),
            $this->makeCryptKey('private'),
            app('encrypter')->getKey()
        );
    }

    /**
     * Register the client repository.
     *
     * @return void
     */
    protected function registerClientRepository()
    {
        $this->app->singleton(ClientRepository::class, function ($container) {
            $config = $container->make('config')->get('passport.personal_access_client');

            return new ClientRepository($config['id'] ?? null, $config['secret'] ?? null);
        });
    }

    /**
     * Create a CryptKey instance without permissions check.
     *
     * @param  string  $type
     * @return \League\OAuth2\Server\CryptKey
     */
    protected function makeCryptKey($type)
    {
        $key = str_replace('\\n', "\n", $this->app->make(Config::class)->get('passport.'.$type.'_key'));

        if (! $key) {
            $key = 'file://'.Passport::keyPath('oauth-'.$type.'.key');
        }

        return new CryptKey($key, null, false);
    }

    /**
     * Make an instance of the token guard.
     *
     * @param  array  $config
     * @return \Illuminate\Auth\RequestGuard
     */
    protected function makeGuard(array $config)
    {
        return new RequestGuard(function ($request) use ($config) {
            return (new TokenGuard(
                $this->app->make(ResourceServer::class),
                new PassportUserProvider(Auth::createUserProvider($config['provider']), $config['provider']),
                $this->app->make(TokenRepository::class),
                $this->app->make(ClientRepository::class),
                $this->app->make('encrypter')
            ))->user($request);
        }, $this->app['request']);
    }
}
