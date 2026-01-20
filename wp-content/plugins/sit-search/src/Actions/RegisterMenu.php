<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;
use SIT\Search\Services\Template;
use SIT\Search\Actions\Dashboard;

class RegisterMenu extends Hook
{
    public static array $hooks = ['admin_menu'];

    public static int $priority = 10;

    public function __invoke()
    {
        add_menu_page(
            'Study in Turkiye',
            'Study in Turkiye',
            'manage_options',
            'sit-search',
            array($this, 'menuPage'),
            'dashicons-admin-site',
            6
        );

        // Dashboard Submenu
        add_submenu_page(
            'sit-search',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'sit-search',
            array($this, 'menuPage')
        );

        //Mapping Page
        add_submenu_page(
            'sit-search',
            'Mapping',
            'Mapping',
            'manage_options',
            'sit-search-mapping',
            array($this, 'mappingPage')
        );
    }

    public function menuPage()
    {
        (new Dashboard())->render();
    }

    public function mappingPage()
    {
        Template::render('admin/mapping');
    }
}