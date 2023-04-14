<?php namespace Dstokesy\Franchises\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFranchisesTable extends Migration
{
    public function up()
    {

        if (!Schema::hasTable('dstokesy_franchises')) {
            Schema::create('dstokesy_franchises', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('slug')->nullable()->index();
                $table->string('domain')->nullable()->index();
                $table->boolean('is_live');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('dstokesy_franchises');
    }
}
