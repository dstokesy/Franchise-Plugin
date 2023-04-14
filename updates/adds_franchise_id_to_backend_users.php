<?php namespace Dstokesy\Franchises\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddsFranchiseIdToBackendUsers extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('backend_users', 'franchise_id')) {
			Schema::table('backend_users', function (Blueprint $table) {
			    $table->integer('franchise_id')->nullable()->after('id')->index();
			});
		}
    }

    public function down()
    {
		if (Schema::hasColumn('backend_users', 'franchise_id')) {
        	Schema::table('backend_users', function(Blueprint $table) {
                $table->dropColumn('franchise_id');
        	});
		}
    }
}
