<?php

declare(strict_types=1);

/*
Plugin Name: Store Locator
Plugin URI: https://github.com/devbaze
Description: Plugin with custom post type for locate and contact dealers, using google maps
Version: 1.0.0
Author: Benjamin Pelto
Author URI: https://github.com/devbaze
Licence: GPLV3 or later
Text Domain: fwsdealerplugin
 */

/**
 * @package BENLocator
 */

defined('ABSPATH') or die('Hey, what are you doing here? You silly human!');

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/* Global Constants */
define('FWS_URL', plugins_url('fwsdealerplugin', dirname(__FILE__)));
define('FWS_BASE_FILE', __FILE__);
define('FWS_PATH', dirname(FWS_BASE_FILE));
/* End of Global Constants */

use BENInc\Activate;
use BENInc\Deactivate;

if (!class_exists('devBENLocator')) {
    class devBENLocator
    {
        public $plugin;

        public function __construct()
        {
            $this->plugin = plugin_basename(__FILE__);

            /* Functions */
            require plugin_dir_path(__FILE__) . 'inc/functions.php';
            /* End of Functions */
        }

        public function fws_register()
        {
            add_action('wp_enqueue_scripts', [ $this, 'fws_enqueue' ]);

            add_action('admin_menu', [ $this, 'fws_add_admin_pages' ]);

            add_filter("plugin_action_links_$this->plugin", [ $this, 'fws_settings_link' ]);

            add_action('init', [ $this, 'fws_custom_post_type' ]);

            add_action('admin_init', [ $this, 'fws_register_settings' ]);

            add_action('admin_notices', [ $this, 'cls_error_notice' ]);
        }


        public function fws_register_settings()
        {
            add_option('fws_map_api_key', '');
            register_setting('fws_options_group', 'fws_map_api_key', 'fws_callback');

            add_option('fws_map_default_radius', '');
            register_setting('fws_options_group', 'fws_map_default_radius', 'fws_callback');

            add_option('fws_map_type', '');
            register_setting('fws_options_group', 'fws_map_type', 'fws_callback');
        }

    
        public function cls_error_notice()
        {
            if (empty(get_option('fws_map_api_key'))) {
                ?>
            <div class="error notice">
                <p><?php _e('Please add google map api key on setting for map to work.', 'my_plugin_textdomain'); ?></p>
            </div>
                <?php
            }
        }

        public function fws_settings_link($links)
        {
            $settings_link = '<a href="admin.php?page=BENLocator_plugin">Settings</a>';
            array_push($links, $settings_link);
            return $links;
        }

        public function fws_add_admin_pages()
        {
            add_menu_page('Dealer locations', 'Dealer locations', 'manage_options', 'BENLocator_plugin', [ $this, 'fws_admin_index' ], 'dashicons-store', 110);
            add_submenu_page('BENLocator_plugin', 'About', 'About', 'manage_options', 'BENLocator_plugin');
            add_submenu_page('BENLocator_plugin', 'Settings', 'Settings', 'manage_options', 'BENLocator_plugin_settings', [ $this, 'fws_admin_settings' ]);
        }

        public function fws_admin_index()
        {
            require_once plugin_dir_path(__FILE__) . 'templates/admin.php';
        }

        public function fws_admin_settings()
        {
            require_once plugin_dir_path(__FILE__) . 'templates/adminsettings.php';
        }

        public function fws_custom_post_type()
        {
            register_post_type(
                'fws_locations',
                ['public' => true,
                'menu_icon' => 'dashicons-location-alt',
                'label' => 'Locations',
                'supports' => [ 'title', 'author'],
                ]
            );
        }

        public function fws_enqueue()
        {
            // enqueue all our scripts
            if (! wp_script_is('jquery', 'enqueued')) {
                wp_enqueue_script('jquery');
            }

            wp_enqueue_style('fws-pluginstyle', plugins_url('/assets/custom-style.css', __FILE__), [], null);
            wp_enqueue_style('fws-customstyle', plugins_url('/assets/astyle.min.css', __FILE__), [], null);
            $args_map = [
                'key' => get_option('fws_map_api_key'),
                'libraries' => 'geometry',
            ];
            wp_enqueue_script('fws-gmapscript', add_query_arg($args_map, 'https://maps.googleapis.com/maps/api/js'), null, null, true);
            wp_enqueue_script('fws-mapscript', plugins_url('/assets/map-functions.js', __FILE__), [ 'jquery', 'fws-gmapscript' ], null, true);
        }
    }
    $BENLocator = new devBENLocator();
    $BENLocator->fws_register();
    // trigger when plugin activate
    register_activation_hook(__FILE__, [ $BENLocator, 'activate' ]);
    // trigger when plugin deactivate
    register_deactivation_hook(__FILE__, [ $BENLocator, 'deactivate' ]);
}
