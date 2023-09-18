<?php namespace Syehan\Webhook\Models;

use Model;

/**
 * Webhook Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class Webhook extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string table name
     */
    public $table = 'syehan_webhook_webhooks';

    /**
     * @var array rules for validation
     */
    public $rules = [];

    /**
     * @var array jsonable for column
     */
    protected $jsonable = [
        'events'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFilterEvent($query, $event)
    {
        return $query->whereJsonContains('events', [$event]);
    }

    public function getContentTypeOptions()
    {
        return config('webhook-server.content_types');
    }

    public function getEventsOptions()
    {
        $events = array_keys(config('webhook-server.events'));
        return array_combine($events, $events);
    }
}
