<?php namespace Dstokesy\Franchises\Controllers;

use Event;
use BackendMenu;
use Backend\Classes\Controller;

/**
 * Info Back-end Controller
 */
class Info extends Controller
{
	public $implement = [
		'Backend.Behaviors.FormController',
		'Backend.Behaviors.ListController',
		'Backend.Behaviors.RelationController',
		'Dstokesy.Behaviors.ListToggleController',
		'Dstokesy.Behaviors.ToolbarButtonsController'
	];

	public $relationConfig      = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Dstokesy.Franchises', 'franchises', 'info');

        $user = $this->user;
        Event::fire('dstokesy.franchises.info.afterConstruct', [$this, $user]);
    }
}
