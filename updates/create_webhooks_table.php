<?php namespace Syehan\Webhook\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateWebhooksTable Migration
 *
 * @link https://docs.octobercms.com/3.x/extend/database/structure.html
 */
return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        if (!Schema::hasTable('syehan_webhook_webhooks')) {
            Schema::create('syehan_webhook_webhooks', function(Blueprint $table) {
                $table->id();
                $table->string('payload_url');
                $table->string('content_type')->default('application/x-www-form-urlencoded');
                $table->string('secret')->nullable();
                $table->longText('events')->nullable();
                $table->boolean('is_active')->default(false)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('syehan_webhook_webhooks');
    }
};
