<?php namespace Dstokesy\Franchises\Console;

use Illuminate\Console\Command;
use System\Classes\PluginManager;
use Schema;
use October\Rain\Database\Schema\Blueprint;

class CreateColumns extends Command
{
	/**
     * @var string The console command name.
     */
    protected $name = 'franchises:createColumns';

    /**
     * @var string The console command description.
     */
    protected $description = 'Creates franchise columns in database for models that have been franchised';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
    	$pluginManager = PluginManager::instance();
    	$plugins = $pluginManager->getPlugins();

    	if ($plugins) {
    		foreach ($plugins as $name => $plugin) {
    			if ($name != 'RainLab.Translate') {
	    			$ds = DIRECTORY_SEPARATOR;
	    			$namePath = strtolower(str_replace('.', $ds, $name));
	    			$path = plugins_path($namePath . $ds . 'models');
	    			$fullPath = $path . $ds . '*.php';
	    			$files = glob($fullPath);

	    			if ($files) {
	    				foreach ($files as $file) {
	    					$modelName = str_replace(plugins_path(), '', $file);
	    					$modelName = str_replace('.php', '', $modelName);
	    					$modelName = str_replace('/', ' ', $modelName);
	    					$modelName = str_replace('\\', ' ', $modelName);
	    					$modelName = ucwords($modelName);
	    					$modelName = str_replace(' ', '\\', $modelName);

	    					if (!is_subclass_of($modelName, 'October\Rain\Database\Pivot')) {
		    					$model = new $modelName;

		    					if ($model->implement && array_search('@Dstokesy.Franchises.Behaviors.FranchisableModel', $model->implement) !== false) {
		    						$tableName = $model->table;

		    						if (!Schema::hasColumn($tableName, 'franchise_id')) {
		    							Schema::table($tableName, function (Blueprint $table) {
										    $table->integer('franchise_id')->nullable()->after('id')->index();
										});
		    						}
		    					}
	    					}
	    				}
	    			}
    			}
    		}
    	}

    	// Output this on the console
        $this->output->writeln('Completed successfully!');
    }
}
