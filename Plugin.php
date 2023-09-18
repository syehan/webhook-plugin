<?php namespace Syehan\Webhook;

use Backend;
use System\Classes\PluginBase;


/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    public $require = ['RainLab.User'];
    
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Webhook',
            'description' => 'Webhooks allow external services to be notified when certain events happen. When the specified events happen, we’ll send a POST request to each of the URLs you provide.',
            'author'      => 'Syehan',
            'icon'        => 'icon-anchor'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | The Example how to implement webhook to your model
        |--------------------------------------------------------------------------
        |
        | This example able to make your model lifecycle such as, created, updated, deleted, etc. 
        | It's only available for past event.
        | For More about Observer lifecycle: check this -> https://laravel.com/docs/9.x/eloquent#observers
        | The Example to observe your model at below.
        */

        \RainLab\User\Models\User::observe(\Syehan\Webhook\Observers\WebhookObserver::class);

        /*
        |--------------------------------------------------------------------------
        | Implement as Behavior
        |--------------------------------------------------------------------------
        |
        | Maybe you want make another unique events for your model. Just pass this behavior into your model.
        | You will able to call $this->webhook() to anything you like as long as your model called.
        | The Example below.
        */

        \RainLab\User\Models\User::extend(function($model) {
            $model->implement[] = 'Syehan.Webhook.Behaviors.WebhookBehavior';         
        });
    }

    /**
     * Registers any backend permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'syehan.webhook' => [
                'tab'   => 'Webhook',
                'label' => 'Manage The Webhook'
            ],
        ];
    }

    /**
     * registerSettings
     */
    public function registerSettings()
    {
        return [
            'syehan_webhooks' => [
                'label'       => 'Webhook',
                'description' => "Webhooks allow external services to be notified when certain events happen. When the specified events happen, we’ll send a POST request to each of the URLs you provide.",
                'category'    => "Webhook",
                'icon'        => 'octo-icon-anchor',
                'url'         => Backend::url('syehan/webhook/webhooks'),
                'permissions' => ['syehan.webhooks'],
                'order'       => 610
            ],
        ];
    }
}
