## OctoberCMS Webhook 

Use `syehan/webhook-plugin`. A webhook is a way for an app to provide information to another app about a particular event. The way the two apps communicate is with a simple HTTP request.


### Installation

**1** - You can install the package via composer:

```bash
$ composer require syehan/webhook-plugin
```
**2** - Now publish the migration for webhook tables:

```
php artisan october:migrate
```

*Note:* It will generate migration table `syehan_webhook_webhooks`.

### Required Dependency
This plugin required dependency by Spatie Webhook Server (https://github.com/spatie/laravel-webhook-server) If there's some trouble, here's another way to do :

You can install it by :

```composer
composer require spatie/laravel-webhook-server "^3.4"
```

---

Or, Install composer merge plugin like this :

```composer
composer require wikimedia/composer-merge-plugin "^2.0"
```

Next, adding allow plugins configuration in your composer.json :

```composer
"config": {
    "preferred-install": "dist",
    "allow-plugins": {
        "composer/installers": true,
        "wikimedia/composer-merge-plugin": true
    }
},
```

Then, adding extra configuration in your composer.json :

```composer
"extra": {
    "merge-plugin": {
        "include": [
            "plugins/*/*/composer.json"
        ],
        "recurse": true,
        "replace": false,
        "merge-dev": false
    }
},
```
Last but not least,

```
composer update
```

### Config Webhook


If your have any setup for webhook server please make sure to copy our config below and paste in `config/webhook-server.php`.

```php
<?php

return [

    /*
     *  The default queue that should be used to send webhook requests.
     */
    'queue' => 'default',

    /*
     *  The default queue connection that should be used to send webhook requests.
     */
    'connection' => env('QUEUE_CONNECTION', 'sync'),

    /*
     * The default http verb to use.
     */
    'http_verb' => 'post',

    /*
     * Proxies to use for request.
     *
     * See https://docs.guzzlephp.org/en/stable/request-options.html#proxy
     */
    'proxy' => null,

    /*
     * This class is responsible for calculating the signature that will be added to
     * the headers of the webhook request. A webhook client can use the signature
     * to verify the request hasn't been tampered with.
     */
    'signer' => \Spatie\WebhookServer\Signer\DefaultSigner::class,

    /*
     * This is the name of the header where the signature will be added.
     */
    'signature_header_name' => 'Signature',

    /*
     * These are the headers that will be added to all webhook requests.
     */
    'headers' => [
        'Content-Type' => 'application/json',
    ],

    /*
     * If a call to a webhook takes longer that this amount of seconds
     * the attempt will be considered failed.
     */
    'timeout_in_seconds' => 3,

    /*
     * The amount of times the webhook should be called before we give up.
     */
    'tries' => 3,

    /*
     * This class determines how many seconds there should be between attempts.
     */
    'backoff_strategy' => \Spatie\WebhookServer\BackoffStrategy\ExponentialBackoffStrategy::class,

    /*
     * This class is used to dispatch webhooks on to the queue.
     */
    'webhook_job' => \Spatie\WebhookServer\CallWebhookJob::class,

    /*
     * By default we will verify that the ssl certificate of the destination
     * of the webhook is valid.
     */
    'verify_ssl' => true,

    /*
     * When set to true, an exception will be thrown when the last attempt fails
     */
    'throw_exception_on_failure' => false,

    /*
     * When using Laravel Horizon you can specify tags that should be used on the
     * underlying job that performs the webhook request.
     */
    'tags' => [],

    /*
     * At least you must have one event in this array. When you creating the events, make sure the format of the event is {LowerCaseModelName}_{EventName}
     * For example, I want to make event for user that has been created, simply you register it like user_created.
     * Another example is below.
     */
    'events' => [
        'user_created' => 'user_created',
        'user_updated' => 'user_updated',
        'user_deleted' => 'user_deleted',
    ],
    
    /*
     * You can add another content types you want, just insert anything you like.
     * Another example is below.
     */
    'content_types' => [
        'application/x-www-form-urlencoded' => 'application/x-www-form-urlencoded',
        'application/json' => 'application/json',
    ]
];
```

### Registering your model

In Default we already registered the RainLab.User Model to this plugin. And there's two way to implement the webhook :

**1** - By observing your model :

```php
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
}
```
**2** - Or, more creative way is :

```php
public function boot()
{
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
```

The second way of implementation is more flexible, we created function inside the behavior class like this :

```php
public function webhook($model, $event_name, $payload = [])
{
    $webhook = WebhookManager::instance();
    $webhook->setModel($model)->setEvent($event)->setPayload($payload)->call();
}
```

It means, you able to call your webhook in your own way, for example :

```php
public function beforeSave()
{
    $this->webhook($this, 'email_changed');
}
```

or

```php
public function notifyUserSickToSlack()
{
    $payload = [
        'description' => 'User is sick',
        'name' => $model->name,
        'submitted_at' => now()->format('j F Y H:i:s'),
    ];
    
    $model->webhook($model, 'notify_to_slack', $payload);
}
```
after that, you must registering your event in `config/webhook-server.php` like this :

```php
/*
 * At least you must have one event in this array. When you creating the events, make sure the format of the event is {LowerCaseModelName}_{EventName}
 * For example, I want to make event for user that has been created, simply you register it like user_created.
 * Another example is below.
 */
'events' => [
    'user_updated' => 'user_updated',
    'user_email_changed' => 'user_email_changed',
    'user_notify_to_slack' => 'user_notify_to_slack',
],
```

Lastly, create your webhook in the menu settings, create some request payload to other app domain and activate it. 

### Testing The Webhook

We recommend you to test using request baskets (https://rbaskets.in/web) to make sure the webhook is running well.

### Security

If you discover any security related issues, please email sehanlim@outlook.com instead of using the issue tracker.

### Credits

- [Syehan](https://github.com/syehan) (Author)

### License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
