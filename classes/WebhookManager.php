<?php namespace Syehan\Webhook\Classes;

use ApplicationException;
use Str;
use Spatie\WebhookServer\WebhookCall;
use Syehan\Webhook\Models\Webhook;

class WebhookManager
{
    use \October\Rain\Support\Traits\Singleton;

    protected $headers = [
        'Webhook-Event' => 'undefined'
    ];

    protected $event, $model, $payload;

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function setEvent($event)
    {
        $event_name = $this->getLowerModelName() . "_" . $event;

        $this->headers['Webhook-Event'] = $this->event = Str::snake($event_name);

        return $this;
    }

    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function setPayload($payload = [])
    {
        $this->payload = $payload;

        return $this;
    }
    
    public function call()
    {
        throw_if(!isset($this->model) || !isset($this->event), ApplicationException::class, 'You must set the model and event name first to begin request call.');

        $this->fetchWebhooks()->each(fn($webhook) => $this->webhookEngine($webhook));

        self::forgetInstance();
    }

    protected function getLowerModelName()
    {
        $model_name = (new \ReflectionClass($this->model))->getShortName();
        return strtolower($model_name);
    }
    
    protected function fetchWebhooks()
    {
        $webhooks = Webhook::query()
        ->active()
        ->filterEvent($this->event)
        ->get();
        
        return $webhooks;
    }

    protected function webhookEngine($webhook)
    {
        WebhookCall::create()
        ->url($webhook->payload_url)
        ->withHeaders($this->headers)
        ->useSecret($webhook->secret)
        ->payload([
            $this->getLowerModelName() => $this->payload
        ])
        ->dispatch();
    }
}