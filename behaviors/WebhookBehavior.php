<?php namespace Syehan\Webhook\Behaviors;

use October\Rain\Extension\ExtensionBase;
use Syehan\Webhook\Classes\WebhookManager;

class WebhookBehavior extends ExtensionBase
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function webhook($model, $event, $payload = [])
    {
        $webhook = WebhookManager::instance();
        $webhook->setModel($model)->setEvent($event)->setPayload($payload)->call();
    }
}