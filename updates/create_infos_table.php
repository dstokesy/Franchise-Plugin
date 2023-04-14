<?php namespace Dstokesy\Franchises\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateInfosTable extends Migration
{
    public function up()
    {

        if (!Schema::hasTable('dstokesy_franchises_info')) {
            Schema::create('dstokesy_franchises_info', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('franchise_id')->nullable()->index();
                $table->string('phone_number')->nullable();
                $table->string('email')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('dstokesy_franchises_info');
    }
}
