<?php

/**
 * @package Selective_loading
 * @version 0.1 beta
 */
/*
Plugin Name: Selective Loading
Plugin URI: 
Description: Selective Loading
Author: Magnus Aberg
Contributor: Jonathan Petersson <jpetersson@garnser.se>
Version: 0.1 beta
Author URI: http://

Copyright 2013  Magnus Aberg  (email : magnuspostbox@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// This is for the active/deactivate/uninstall
include_once ('classes/selective-loading-install.class.php');
$install = 'Selective_loading_install';

register_activation_hook(   __FILE__,   array($install, 'activate') );
register_deactivation_hook( __FILE__,   array($install, 'deactivate') );
register_uninstall_hook(    __FILE__,   array($install, 'uninstall') );
// End for the active/deactivate/uninstall


// Check if The Must Use Plugin is loaded
if( $selective_loading_mu ){
    
    include_once ( 'classes/selective-loading-dashboard.class.php' );
    global $LANG;
    global $pagenow;
    global $wpdb;
    
    define( 'SELECTIVELOADING_PATH', __FILE__ );
    define( 'SELECTIVELOADING_PREFIX', 'selective_loading_' );
    
    $guid           = str_replace( '?'.$_SERVER['QUERY_STRING'], '', 'http://'.$_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI'] );
    $global         = SELECTIVELOADING_PREFIX.'global';
    $guids          = get_option( 'selective_loading_guids', array() );
    $templates      = get_option( 'selective_loading_templates', array() );
    
    // qTranslate fix
    $url_mode = get_option( 'qtranslate_url_mode', 0 );

    $global_plugins     = array();
    $post_plugins       = array();
    $template_plugins   = array();
    $admin_screen       = array();
    
    // Show preview with plugins
    if($_GET['preview'] === "true" && !isset($_GET['preview_id'])) {
        $guid = str_replace('&preview=true', '', 'http://'.$_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']);
    }
    
    if( is_admin() ) {
        $admin_screen  = get_option( 'hijacked_active_plugins', array() );
        foreach($admin_screen as $plugin)
            include_once ( WP_PLUGIN_DIR . '/' . $plugin );   
    }
    
    if( $url_mode ) {
        
        $enabled_lang       = get_option( 'qtranslate_enabled_languages' );
        $enabled_pattern    = implode( '|', $enabled_lang );
        
        switch($url_mode) {
            case 2:
                    $lang_path = substr( $_SERVER['REQUEST_URI'], 0, 4 );
                
                    if( preg_match("/($enabled_pattern)/", $lang_path) ) {
                        $guid = str_replace($lang_path, '/', $guid);
                        $LANG = substr($lang_path, 1,2);
                    }
                break;
            case 3:
                    $lang_path = substr( $_SERVER['SERVER_NAME'], 0, 2 );
                    if( preg_match("/($enabled_pattern)\./", $lang_path) )
                        $LANG = $lang_path;
                break;
            default:
        }
    }

    // FrontPage
    if ( $guid == get_option('siteurl') || $guid == get_option('siteurl').'/' ) {
        $page_id = get_option( 'page_on_front' );
        
        if($page_id > 0) {
            $page = get_post( $page_id );
            
            if( key_exists( $page->guid, $guids ) && key_exists( "post", $guids[$page->guid] ) ) {
                $front_page = $guids[$page->guid]["post"];
                
                foreach( $front_page as $plugin )
                    include_once ( WP_PLUGIN_DIR . '/' . $plugin ); 
            }
        }
    }
    
    if( key_exists( $global, $templates ) ) {
        $global_plugins = $templates[$global];

        foreach($global_plugins as $plugin) {
            include_once ( WP_PLUGIN_DIR . '/' . $plugin );
        }  
    }

    // Check if paged category
    $pos = stripos($guid, '/page/');
    if( $pos !== FALSE )
        $guid = substr($guid, 0, $pos).'/';
    
    if( key_exists( $guid, $guids ) ) {
        
        if( key_exists( "template", $guids[$guid] ) ) {
            $template = $guids[$guid]["template"];
            $template_plugins = $templates[$template];

            foreach( $template_plugins as $plugin ) {
                include_once ( WP_PLUGIN_DIR . '/' . $plugin );
            }
        }

        if( key_exists( "post", $guids[$guid] ) ) {
            $post_plugins = $guids[$guid]["post"];
            foreach( $post_plugins as $plugin ) {
                include_once ( WP_PLUGIN_DIR . '/' . $plugin );
            }
        }
    }
        
    
    // Actions
    add_action( 'wp_ajax_get_plugins', array( $selective_loading_dashboard, 'get_plugins' ) );
    add_action( 'wp_ajax_get_plugins_category', array( $selective_loading_dashboard, 'get_plugins_category' ) );
    add_action( 'wp_ajax_save_ajax', array( $selective_loading_dashboard, 'save_ajax' ) );
    add_action( 'wp_ajax_save_ajax_category', array( $selective_loading_dashboard, 'save_ajax_category' ) );
    add_action( 'save_post', array( $selective_loading_dashboard, 'save_from_post' ) );
    add_action( 'admin_menu', array( $selective_loading_dashboard, 'settings' ) );
    add_action( 'add_meta_boxes', array( $selective_loading_dashboard, 'edit_meta_box' ) );
    
    // Filters
    add_filter( 'option_active_plugins', array($selective_loading_mu, 'list_active') );
    add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), array($selective_loading_dashboard, 'settings_link') );
    
    //Hijacking plugins hooks
    if( $pagenow == 'plugins.php' ) {

        $plugins = get_option('hijacked_active_plugins', array());

        foreach( $plugins as $plugin ) {
            register_deactivation_hook( WP_PLUGIN_DIR.'/'.$plugin, array($install, 'deactivate_hijack') );
            register_uninstall_hook( WP_PLUGIN_DIR.'/'.$plugin, array($install, 'deactivate_hijack') );
        }
    }

}
