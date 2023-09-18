<?php
 
namespace Syehan\Webhook\Observers;

use Syehan\Webhook\Classes\WebhookManager;

class WebhookObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;
 
    /**
     * Handle the Model "created" event.
     *
     * @param $model
     * @return void
     */
    public function created($model)
    {
        $this->webhook($model, 'created');
    }

    /**
     * Handle the Model "saved" event.
     * @param $model
     * @return void
     */
    public function saved($model): void
    {
        $this->webhook($model, 'saved');
    }

    /**
     * Handle the Model "updated" event.
     *
     * @param $model
     * @return void
     */
    public function updated($model)
    {
        $this->webhook($model, 'updated');
    }

    /**
     * Handle the Model "deleted" event.
     *
     * @param $model
     * @return void
     */
    public function deleted($model)
    {
        $this->webhook($model, 'deleted');
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored($model): void
    {
        $this->webhook($model, 'restored');
    }

    protected function webhook($model, $event)
    {
        $webhook = WebhookManager::instance();
        $webhook->setModel($model)->setEvent($event)->setPayload($model->toArray())->call();
    }
}