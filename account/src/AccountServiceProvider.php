<?php

namespace Trax\Account;

use Illuminate\Contracts\Debug\ExceptionHandler;

use Trax\Account\Exceptions\AccountExceptionHandler;
use Trax\Account\Routes\AuthRoutes;
use Trax\Account\Validators\AccountValidator;
use Trax\Account\Ldap\LdapGuard;
use Trax\DataStore\DataStoreServiceProvider;

class AccountServiceProvider extends DataStoreServiceProvider
{
    /**
     * Plugin code. 
     */
    protected $plugin = 'trax-account';

    /**
     * Namespace. 
     */
    protected $namespace = __NAMESPACE__;

    /**
     * Directory. 
     */
    protected $dir = __DIR__;

    /**
     * Register UI.
     */
    protected $hasUI = true;
    
    /**
     * Models > Tables.
     */
    protected $models = [
        'Entity' => 'trax_account_entities',
        'Role' => 'trax_account_roles',
        'User' => 'trax_account_users',
        'BasicClient' => 'trax_account_basic_clients',
        'Group' => 'trax_account_groups',
        'Agreement' => 'trax_account_agreements',
    ];
    
    /**
     * The service provider commands.
     */
    protected $commands = [

        // Users
        'Trax\Account\Console\UserAdminCreateCommand',
        'Trax\Account\Console\UserListCommand',
        'Trax\Account\Console\UserDeleteCommand',

        // BasicClients
        'Trax\Account\Console\ClientListCommand',
        'Trax\Account\Console\ClientCreateCommand',
        'Trax\Account\Console\ClientDeleteCommand',
    ];
    
    /**
     * The service provider middlewares. Must be declared manually in Lumen!
     */
    protected $middlewares = [
        'auth.basic.once' => \Trax\Account\Http\Middleware\BasicAuthMiddleware::class,
        'account.agreement' => \Trax\Account\Http\Middleware\UserAgreementMiddleware::class,
    ];

    
    /**
     * Register services.
     */
    protected function registerServices()
    {
        $plugin = $this->plugin;
        $models = $this->models;
        $this->app->singleton('Trax\Account\AccountServices', function ($app) use($plugin , $models) {
            return new AccountServices($app, $plugin, $models);
        });

        // Extend the Web Guard in order to implement LDAP alternative
        $this->app['auth']->extend('ldap', function ($app, $name, $config) {
            $provider = $app['auth']->createUserProvider($config['provider'] ?? null);
            return new LdapGuard($name, $provider, $app['session.store']);
        });
    }

    /**
     * Register routes.
     */
    protected function registerRoutes($models = null)
    {
        // Data routes
        parent::registerRoutes($models);

        // Auth routes
        (new AuthRoutes)->register($this->router);
    }

    /**
     * Register a custom exception handler.
     */
    protected function registerExceptionHandler()
    {
        $this->app->bind(ExceptionHandler::class, AccountExceptionHandler::class);
    }

    /**
     * Register additional validation rules.
     */
    protected function registerValidationRules()
    {
        (new AccountValidator($this->app))->register();
    }    

}
