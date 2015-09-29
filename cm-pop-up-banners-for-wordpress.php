<?php

/*
  Plugin Name: CM Pop-Up banners for WordPress
  Description: This plugin adds the option to add the on-site Pop-Up or Fly In Bottom Ads
  Version: 1.0.5
  Author: CreativeMindsSolutions
  Licence: GPL
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class file.
 * What it does:
 * - checks which part of the plugin should be affected by the query frontend or backend and passes the control to the right controller
 * - manages installation
 * - manages uninstallation
 * - defines the things that should be global in the plugin scope (settings etc.)
 * @author CreativeMindsSolutions - Remigiusz Wojtyla
 */
class CMPopUpFlyIn {

    public static $calledClassName;
    protected static $instance = NULL;
    public static $usersColumnMetaName = 'cm-access-restricted';
    public static $messageOptionName = 'cm-access-restricted-message';

    /**
     * Main Instance
     *
     * Insures that only one instance of class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0
     * @static
     * @staticvar array $instance
     * @return The one true CMPopUpFlyIn
     */
    public static function instance() {
        $class = __CLASS__;
        if (!isset(self::$instance) && !( self::$instance instanceof $class )) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct() {
        if (empty(self::$calledClassName)) {
            self::$calledClassName = __CLASS__;
        }

        self::setupConstants();


        /*
         * Shared
         */
        include_once CMPOPFLY_PLUGIN_DIR . '/shared/classes/Labels.php';
        include_once CMPOPFLY_PLUGIN_DIR . '/backend/classes/Settings.php';
        include_once CMPOPFLY_PLUGIN_DIR . '/shared/cm-pop-up-banners-for-wordpress-shared.php';
        include_once CMPOPFLY_PLUGIN_DIR . '/shared/functions.php';

        $cMPopUpFlyInSharedInstance = CMPopUpFlyInShared::instance();

        if (is_admin()) {
            /*
             * Backend
             */
            require_once CMPOPFLY_PLUGIN_DIR . '/backend/cm-pop-up-banners-for-wordpress-backend.php';
            $cMPopUpFlyInBackendInstance = CMPopUpFlyInBackend::instance();
        } else {
                /*
                 * Frontend
                 */
                require_once CMPOPFLY_PLUGIN_DIR . '/backend/cm-pop-up-banners-for-wordpress-backend.php';
                require_once CMPOPFLY_PLUGIN_DIR . '/frontend/cm-pop-up-banners-for-wordpress-frontend.php';
                $cMPopUpFlyInFrontendInstance = CMPopUpFlyInFrontend::instance();
        }
    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupConstants() {
        /**
         * Define Plugin Version
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_VERSION')) {
            define('CMPOPFLY_VERSION', '1.0.5');
        }

        /**
         * Define Plugin Directory
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_PLUGIN_DIR')) {
            define('CMPOPFLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }

        /**
         * Define Plugin URL
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_PLUGIN_URL')) {
            define('CMPOPFLY_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

        /**
         * Define Plugin File Name
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_PLUGIN_FILE')) {
            define('CMPOPFLY_PLUGIN_FILE', __FILE__);
        }

        /**
         * Define Plugin Slug name
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_SLUG_NAME')) {
            define('CMPOPFLY_SLUG_NAME', 'cm-popupflyin');
        }

        /**
         * Define Plugin name
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_NAME')) {
            define('CMPOPFLY_NAME', 'CM Pop-Up Banners for WordPress');
        }

        /**
         * Define Plugin name
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_PLUGIN_NAME')) {
            define('CMPOPFLY_PLUGIN_NAME', 'CM Pop-Up banners for WordPress');
        }

        /**
         * Define Plugin basename
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_PLUGIN')) {
            define('CMPOPFLY_PLUGIN', plugin_basename(__FILE__));
        }

        /**
         * Define Plugin basename
         *
         * @since 1.0
         */
        if (!defined('CMPOPFLY_URL')) {
            define('CMPOPFLY_URL', 'https://www.cminds.com/store/cm-pop-up-banners-for-wordpress-pro/');
        }
        /*
         * define additional database tables
         */
        global $table_prefix;
        if(!isset($table_prefix)){
            $table_prefix = '';
        }
        if (!defined('CMPOPFLY_HISTORY_TABLE')) {
            define('CMPOPFLY_HISTORY_TABLE', $table_prefix . 'cm_popfly_history');
        }
        if (!defined('CMPOPFLY_POST_TABLE')) {
            define('CMPOPFLY_POST_TABLE', $table_prefix . 'posts');
        }
        if (!defined('CMPOPFLY_POST_META_TABLE')) {
            define('CMPOPFLY_POST_META_TABLE', $table_prefix . 'postmeta');
        }
        // Constants for expressing human-readable intervals
	// in their respective number of seconds.
        if (!defined('MINUTE_IN_SECONDS')) {
            define( 'MINUTE_IN_SECONDS', 60 );
        }
        if (!defined('HOUR_IN_SECONDS')) {
            define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
        }
        if (!defined('DAY_IN_SECONDS')) {
            define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
        }
        if (!defined('WEEK_IN_SECONDS')) {
            define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
        }
        if (!defined('YEAR_IN_SECONDS')) {
            define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
        }
    }

    public static function _install() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $table_prefix;
        dbDelta('CREATE TABLE ' . CMPOPFLY_HISTORY_TABLE . ' (
			  event_id bigint(20) NOT NULL AUTO_INCREMENT,
			  event_type enum("cl","im") NOT NULL,
			  campaign_id bigint DEFAULT NULL,
			  amount int(11) DEFAULT 1,
			  banner_id bigint DEFAULT NULL,
			  referer_url varchar(150) NOT NULL,
			  remote_ip varchar(20) NOT NULL,
			  webpage_url varchar(200) NOT NULL,
			  remote_country varchar(20) NOT NULL,
			  remote_city varchar(30) NOT NULL DEFAULT "",
			  regdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY  (event_id)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
        return;
    }

    public static function _uninstall() {
        /*
         * do we need this?
         */
//        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//        global $table_prefix;
//        dbDelta('DROP TABLE ' . CMPOPFLY_HISTORY_TABLE . ';');
        return;
    }

    /**
     * Get localized string.
     *
     * @param string $msg
     * @return string
     */
    public static function __($msg) {
        return __($msg, CMPOPFLY_SLUG_NAME);
    }

    /**
     * Get item meta
     *
     * @param string $msg
     * @return string
     */
    public static function meta($id, $key, $default = null) {
        $result = get_post_meta($id, $key, true);
        if ($default !== null) {
            $result = !empty($result) ? $result : $default;
        }
        return $result;
    }

}

/**
 * The main function responsible for returning the one true plugin class
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $marcinPluginPrototype = MarcinPluginPrototypePlugin(); ?>
 *
 * @since 1.0
 * @return object The one true EDD_Remarkety Instance
 */
function CMPopUpFlyInInit() {
    return CMPopUpFlyIn::instance();
}

// Get CMPopUpFlyInInit
$cMPopUpFlyIn = CMPopUpFlyInInit();

//Installation
register_activation_hook(__FILE__, array('CMPopUpFlyIn', '_install'));
//Uninstallation
register_deactivation_hook(__FILE__, array('CMPopUpFlyIn', '_uninstall'));
