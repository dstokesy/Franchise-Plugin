<?php namespace Dstokesy\Franchises\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Franchises Back-end Controller
 */
class Franchises extends Controller
{

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Dstokesy.Franchises', 'franchises', 'franchises');
    }
}
