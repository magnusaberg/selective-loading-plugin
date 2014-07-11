<?php

/**
 * @package Selective_loading
 * @version 0.1 beta
 */

if( !class_exists('Selective_loading_install', true) ) {
    
    class Selective_loading_install {
    
        var $selectiveMU,
            $selectivePD;
    
        public function __construct( $case = false) {

            switch($case) {
                case 'activate':
                        $this->_activate_me();
                    break;
                
                case 'deactivate':
                        $this->_deactivate_me();
                    break;
                
                case 'uninstall':
                        $this->_uninstall_me();
                    break;
                case 'deactivate_plugin':
                        $this->_deactivate_plugin();
                    break;
                default:
                    wp_die("Something went wrong!");
            }
        }

        public function activate() {
            new Selective_loading_install( 'activate' );
        }
        
        public function deactivate() {
            new Selective_loading_install( 'deactivate' );
        }
        
        public function uninstall() {
            if ( __FILE__ != WP_UNINSTALL_PLUGIN )
                return 0;

            new Selective_loading_install( 'uninstall' );
        }
        
        public function deactivate_hijack() {
            new Selective_loading_install( 'deactivate_plugin' );
        }
        
        private function _init() {
                        
            if( !is_dir(WPMU_PLUGIN_DIR) )
                mkdir(WPMU_PLUGIN_DIR);
            
            $this->selectiveMU      = WPMU_PLUGIN_DIR.'/selective-loading-mu.php';
            $this->selectivePD      = WP_PLUGIN_DIR.'/selective-loading/move_me/selective-loading.mu.php';
        }

        private function _activate_me() {
            
            $this->_init();
            
            if(!file_exists($this->selectiveMU))
                rename ($this->selectivePD, $this->selectiveMU);
            
            
            update_option( 'hijacked_active_plugins', get_option( 'active_plugins', array() ) );
            update_option( 'selective_loading_templates', array("selective_loading_global" => get_option('hijacked_active_plugins', array()) ) );
            update_option( 'selective_loading_guids', array() );
        }
        
        private function _deactivate_me() {
            
            $this->_init();
            
            if(file_exists($this->selectiveMU))
                rename($this->selectiveMU, $this->selectivePD);
        }
        
        private function _uninstall_me() {
            
            $this->_deactivate_me();
            
            delete_option('hijacked_active_plugins');
            delete_option('selective_loading_templates');
            delete_option('selective_loading_guids');
        }
        
        private function _deactivate_plugin() {
            
            $plugin = esc_html($_GET['plugin']);
            $option = 'hijacked_active_plugins';
            $hijacked_plugins = get_option($option, array());
            $key = array_search($plugin, $hijacked_plugins);
            unset($hijacked_plugins[$key]);
            
            update_option($option, $hijacked_plugins);
        }
    }
    
}