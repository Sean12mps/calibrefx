<?php
/**
 * CalibreFx
 *
 * WordPress Themes Framework by CalibreWorks Team
 *
 * @package		CalibreFx
 * @author		CalibreWorks Team
 * @copyright           Copyright (c) 2012, CalibreWorks. (http://www.calibreworks.com/)
 * @link		http://www.calibrefx.com
 * @filesource 
 *
 * WARNING: This file is part of the core CalibreFx framework. DO NOT edit
 * this file under any circumstances. 
 * 
 * @package CalibreFx
 */

/**
 * Calibrefx Class
 *
 * @package		Calibrefx
 * @subpackage          Core
 * @author		CalibreWorks Team
 * @since		Version 1.0
 * @link		http://www.calibrefx.com
 */

final class Calibrefx {

    /**
     * Reference to the global Plugin instance
     *
     * @var	object
     */
    protected static $instance;

    /**
     * Return the Calibrefx object
     *
     * @return	object
     */
    public static function &get_instance() {
        if(self::$instance === null){
            self::$instance = new Calibrefx();
        }
        
        return self::$instance;
    }

    /**
     * Constructor
     */
    function __construct() {
        self::$instance = & $this;

        $this->config = & calibrefx_load_class('Config', 'core');
        $this->load = & calibrefx_load_class('Loader', 'core');
        //Since admin is abstract we don't instantiate
        calibrefx_load_class('Admin', 'core');
        calibrefx_load_class('Adapter', 'core');
        calibrefx_load_class('Driver', 'core');
        
        //We fire the engine
        $this->intialize();
        
        calibrefx_log_message('debug', 'Calibrefx Class Initialized');
    }

    /**
     * Initialize our hooks
     */
    public function intialize() {
        add_action('calibrefx_init', array(&$this, 'calibrefx_theme_support'));
        
        
    }

    /**
     * Add our calibrefx theme support
     */
    public function calibrefx_theme_support() {
        add_theme_support('menus');
        add_theme_support('post-thumbnails');
        add_theme_support('calibrefx-admin-menu');
        add_theme_support('calibrefx-custom-header');
        add_theme_support('calibrefx-default-styles');
        add_theme_support('calibrefx-inpost-layouts');
        //add_theme_support('calibrefx-preformance');

        if (!current_theme_supports('calibrefx-menus')) {
            add_theme_support('calibrefx-menus', array(
                'primary' => __('Primary Navigation Menu', 'calibrefx'),
                'secondary' => __('Secondary Navigation Menu', 'calibrefx')
                )
            );
        }

        if (!current_theme_supports('calibrefx-wraps'))
            add_theme_support('calibrefx-wraps', array('header', 'nav', 'subnav', 'inner', 'footer', 'footer-widget'));
        
        //@TODO: Will do in better ways for custom post type
        add_post_type_support('post', array('calibrefx-seo', 'calibrefx-layouts'));
        add_post_type_support('page', array('calibrefx-seo', 'calibrefx-layouts'));
    }
}