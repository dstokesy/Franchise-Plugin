<?php namespace Dstokesy\Franchises;

use Backend;
use System\Classes\PluginBase;
use Event;
use Cache;
use Config;
use Artisan;
use BackendAuth;
use Backend\Models\User as BackendUser;
use Backend\Models\UserRole as BackendUserRole;
use Dstokesy\Franchises\Classes\Franchiser;
use ValidationException;
use RainLab\Translate\Models\Locale;

/**
 * Franchises Plugin Information File
 */
class Plugin extends PluginBase
{
	public $franchise;

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'dstokesy.franchises::lang.plugin.name',
            'description' => 'dstokesy.franchises::lang.plugin.description',
            'author'      => 'Dstokesy',
            'icon'        => 'icon-sitemap'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
    	$this->registerConsoleCommand('dstokesy.franchisesCreateColumns', 'Dstokesy\Franchises\Console\CreateColumns');
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
		$this->bootFranchiser();

    	$this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware('Dstokesy\Franchises\Classes\Middleware');

    	$this->extendDeploymentRunner();
    	$this->extendAdminBar();
    	$this->extendBackendUsers();
    	$this->extendLanguages();
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'dstokesy.franchises.manage_franchises' => [
                'tab' => 'Franchises',
                'label' => 'dstokesy.franchises::lang.permissions.manage_franchises'
            ],
            'dstokesy.franchises.manage_franchise_info' => [
                'tab' => 'Franchises',
                'label' => 'dstokesy.franchises::lang.permissions.manage_franchise_info'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'franchises' => [
                'label'       => 'dstokesy.franchises::lang.menu.franchises',
                'url'         => Backend::url('dstokesy/franchises/franchises'),
                'icon'        => 'icon-sitemap',
                'permissions' => ['dstokesy.franchises.*'],
                'order'       => 500,
                'sideMenu'	=> [
                	'franchises' => [
		                'label'       => 'dstokesy.franchises::lang.menu.franchises',
		                'url'         => Backend::url('dstokesy/franchises/franchises'),
		                'icon'        => 'icon-sitemap',
		                'permissions' => ['dstokesy.franchises.manage_franchises'],
		            ],
		            'info' => [
		                'label'       => 'dstokesy.franchises::lang.menu.franchise_info',
		                'url'         => Backend::url('dstokesy/franchises/info'),
		                'icon'        => 'icon-file-text',
		                'permissions' => ['dstokesy.franchises.manage_franchise_info'],
		            ],
                ]
            ],
        ];
    }

    public function bootFranchiser()
    {
    	$franchiser = Franchiser::instance();
		$franchiser->setFranchise();
		$this->franchise = $franchiser->getFranchise();
    }

    public function extendDeploymentRunner()
    {
    	Event::listen('dstokesy.boilerplate.afterDeploymentRun', function($widget) {
			Artisan::call('franchises:createColumns');
    	});
    }

    public function extendAdminBar()
    {
    	Event::listen('dstokesy.boilerplate.adminBar.afterPrepareVars', function($widget) {
    		if ($this->franchise) {
	    		$widget->adminBase = $this->franchise->host . '/' . Config::get('cms.backendUri');
    		}
    	});
    }

    public function extendBackendUsers()
    {
    	BackendUser::extend(function($model) {
    		$model->implement = ['Dstokesy.Franchises.Behaviors.FranchisableModel'];

    		$model->bindEvent('model.beforeDelete', function() use($model) {
    			$adminUser = BackendAuth::getUser();

    			if ($adminUser->franchise) {
    				if ($model->franchise_id != $adminUser->franchise->id) {
    					throw new ValidationException(['login' => 'You do not have permission to delete this user']);
    				}
    			}
    		});

    		$model->bindEvent('model.beforeCreate', function() use($model) {
    			$model->franchise_id = ($this->franchise ? $this->franchise->id : null);
    		});

    		$model->bindEvent('model.beforeSave', function() use($model) {
    			$adminUser = BackendAuth::getUser();

    			if ($adminUser->franchise) {
    				if ($model->franchise_id != $adminUser->franchise->id) {
    					throw new ValidationException(['login' => 'You do not have permission to create this user']);
    				}
    			}
    		});
    	});

    	Event::listen('backend.list.extendQuery', function ($listWidget, $query) {
    		if ($listWidget->model instanceof BackendUser && $this->franchise) {
		    	$query->where('franchise_id', $this->franchise->id);
    		}
		});


    	Event::listen('backend.form.extendFieldsBefore', function($widget) {
    		if($widget->model instanceof BackendUser) {

    			$options = [];

		        foreach (BackendUserRole::all() as $role) {
		            $options[$role->id] = [$role->name, $role->description];
		        }

		        if ($this->franchise) {
		        	unset($options[1]);
		        	unset($options[2]);
		        }

    			$widget->tabs['fields']['role']['options'] = $options;
            }
    	});
    }

    public function extendLanguages()
    {
    	Event::listen('rainlab.translate.locale.beforeScopeIsEnabled', function($model, &$query) {
    		$franchiser = Franchiser::instance();
    		$franchise = $franchiser->getFranchise();

    		if ($franchise) {
    			if ($languages = $franchise->info->languages) {
    				$query->where(function($q) use($languages) {
    					return $q->where('is_enabled', true)
    						->orWhereIn('id', $languages);
    				});

    				return $query;
    			}
    		}
    	});

    	Event::listen('rainlab.translate.locale.beforeListEnabled', function() {
    		$franchiser = Franchiser::instance();
    		$franchise = $franchiser->getFranchise();

    		if ($franchise) {
		        if (Locale::$cacheListEnabled) {
		            return Locale::$cacheListEnabled;
		        }

		        $expiresAt = now()->addMinutes(1440);
		        $isEnabled = Cache::remember('rainlab.translate.locales.franchise_' . $franchise->id, $expiresAt, function() {
		            return Locale::isEnabled()->order()->pluck('name', 'code')->all();
		        });

		        return Locale::$cacheListEnabled = $isEnabled;
    		}
    	});
    }
}
