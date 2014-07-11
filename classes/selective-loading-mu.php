<?php

/**
 * @package Selective_loading
 * @version 0.1 beta
 */
   
    if( !class_exists('Selective_loading_mu', TRUE)){
        class Selective_loading_mu {
            
            public  $hijacked_active_plugins,
                    $active_plugins;
        
            public function __construct() {
                
                $this->active_plugins = get_option('active_plugins', array());
                
                $this->_check_hijacks();
                $this->_set_active_plugins();
            }
            
            public function list_active() {
                return array_merge( array('selective-loading/selective-loading.php'), get_option('hijacked_active_plugins', array()) );
            }
            
            private function _set_active_plugins() {
                
                $templates  = get_option( 'selective_loading_templates', array() );
                $count      = count($templates['selective_loading_global']);
                
                if($count > 0) {
                    update_option( 'active_plugins', array_merge(array('selective-loading/selective-loading.php'),$templates['selective_loading_global']) );
                }
                else {
                   update_option('active_plugins', array('selective-loading/selective-loading.php'));
                }
            }
            
            private function _check_hijacks() {
                
                if(count($this->active_plugins) > 1) {
                    
                    $this->hijacked_active_plugins  = array_unique( 
                                                        array_merge( 
                                                                    get_option( 'active_plugins', array() ), 
                                                                    get_option( 'hijacked_active_plugins', array() ) 
                                                                   )
                                                                  );
                    
                    $key = array_search('selective-loading/selective-loading.php' ,$this->hijacked_active_plugins);
                    unset($this->hijacked_active_plugins[$key]);
                    
                    update_option('hijacked_active_plugins', $this->hijacked_active_plugins);
                }
            }
        }
    }

    if(class_exists('Selective_loading_mu', TRUE)) {
        $selective_loading_mu = new Selective_loading_mu;
    }