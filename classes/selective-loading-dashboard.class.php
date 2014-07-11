<?php

/**
 * @package Selective_loading
 * @version 0.1 beta
 */

if ( !class_exists('Selective_loading_dashboard', TRUE) ) {
    class Selective_loading_dashboard {
        
        private $arr_post_type  = array(),
                $arr_plugins    = array();
        
        public function settings_link( $links ) {
            $settings_link = '<a href="options-general.php?page=selective_loading_slug.php">Settings</a>'; 
            $links[]       = $settings_link;
            
            return $links;
        }
        
        public function settings() {
            add_options_page('Selective Loading','Selective Loading','manage_options','selective_loading_slug',array($this, 'settings_page'));
        }
        
        public function settings_page() {
            $this->_essentials();
            $this->_settings_enque_js_css();
            
            echo '<h1>Settings Selective Loading of Plugins</h1>
                    <h3 id="plugin_heading">Plugins</h3>
                    <ol id="list_of_plugins">';
                foreach($this->arr_plugins as $plugin_file) {
                    
                    $plugin_path = WP_PLUGIN_DIR.'/'.$plugin_file;
                    $data = get_plugin_data( $plugin_path );
                    echo '<li slp-value="'. $plugin_file .'" class="plugin_listed" id="'. basename($plugin_file, ".php") .'" title="' . $data['Name'] . '">' . $data['Name'] . '</li>';
                }
            echo '</ol>';
            
            echo '<div id="the_list">';
            echo '<h3 class="slp_heading">Templates</h3>';
            echo '<div class="template" id="global">Global</div>';
            echo '<div class="templates">';
            foreach(get_page_templates() as $template => $temp_val) {
                echo '<div class="template" id="'. basename($temp_val, ".php") .'">
                        <div title="' . $template . '" class="title">' . substr($template, 0, 22) . '</div>
                      </div>';
            }
            echo '</div>';
            
            echo '<h3 class="slp_heading">Categories</h3>';
            echo '<div class="templates">';
            foreach(get_categories() as $category) {
                echo '<div title="' . get_category_link($category->cat_ID) . '" class="category-template" id="'. $category->cat_ID .'">'. substr($category->name, 0, 22) .'</div>';
            }
            echo '  </div></div>
                <div id="save_btn">SAVE</div>
                ';
        }
        
        public function edit_meta_box() {
            $this->_essentials();
            $this->_enque_js_css();
            
            // If there is more than this plugin
            if(count($this->arr_plugins) > 1) {
                
                foreach ($this->arr_post_type as $post_type) {
                    
                    add_meta_box(   'selectiveloading_id', 
                                    'Selective Loading', 
                                    array($this, 'select_post'), 
                                    $post_type, 
                                    'advanced', 
                                    'high'
                                );
                }
            }
        }
        
        public function select_post( $post ) {
           $plugins     = $this->_load_post_plugins($post);
           $plugin_loop = $this->_load_plugin_loop($plugins);

           $html = sprintf('<div class="slp_plugins" id="post-formats-select-plugins">
                                <select multiple="multiple" class="multiselect settings" id="select_plugins" name="selected_plugins[]">
                                    %s
                                </select>
                            </div>',
                        $plugin_loop
                   );
           
           echo $html;
        }
        
        public function save_from_post ( $post_id ) {
            
            if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
                return $post_id;
            
            $id     = esc_attr($_POST["post_ID"]);
            $link   = get_permalink($id);
            $guids  = get_option('selective_loading_guids', array() );
            
            if( !empty($_POST["selected_plugins"])) {
                
                foreach($_POST["selected_plugins"] as $plugin)
                    $post_plugins[] = esc_html($plugin);

                $guids[$link]["post"] = $post_plugins;
            }
            
            else {
                unset($guids[$link]["post"]);
            }
            
            update_option('selective_loading_guids', $guids);
        }

        public function get_plugins() {
            $template   = SELECTIVELOADING_PREFIX . basename( esc_attr($_POST['template']), ".php" );
            $templates  = get_option('selective_loading_templates', array());
            $this->_load_template($templates[$template]);
        }
        
        public function get_plugins_category() {
            $template  = esc_attr($_POST['template']);
            $templates = get_option('selective_loading_templates', array());
            $this->_load_template($templates[$template]);
        }
        
        public function save_ajax() {
            $this->_essentials();
            $saved_plugins  = array();
            $page           = SELECTIVELOADING_PREFIX .esc_attr( $_POST["index"]);
            $template       = str_replace(SELECTIVELOADING_PREFIX, '', $page).".php";
            
            $pages = get_pages( array(
                'meta_key'      => '_wp_page_template',
                'meta_value'    => $template,
                'hierarchical' => false
            ) );

            // Get the options
            $selective_loading_templates    = get_option('selective_loading_templates', array());
            $selective_loading_dashboardds  = get_option('selective_loading_guids', array());

            if(is_array($_POST["arr"])) {
                foreach ($_POST["arr"] as $plugin) {
                    $saved_plugins[] = esc_html($plugin);
                }
                $selective_loading_templates[$page] = $saved_plugins;
            }
            
            else {
                unset($selective_loading_templates[$page]);
            }
            
            foreach($pages as $obj_page) {
                $get_page   = get_page($obj_page->post_id);
                $link       = get_permalink($get_page->ID);
                
                if(array_key_exists($page, $selective_loading_templates) )
                    $selective_loading_dashboardds[$link]["template"] = $page;
                else
                    unset($selective_loading_dashboardds[$link]["template"]);
                
                if( empty($selective_loading_dashboardds[$link]["template"]) && empty($selective_loading_dashboardds[$link]["post"]) )
                    unset($selective_loading_dashboardds[$link]);
            }
            
            update_option('selective_loading_templates', $selective_loading_templates);
            update_option('selective_loading_guids', $selective_loading_dashboardds);
            print_r($saved_plugins);
            wp_die();
        }
 
        public function save_ajax_category() {
            $this->_essentials();
            $saved_plugins  = array();
            $category       = esc_attr( $_POST["index"] );
            $link           = get_category_link($category);

            $selective_loading_templates    = get_option('selective_loading_templates', array());
            $selective_loading_dashboards   = get_option('selective_loading_guids', array());
            
            if(is_array($_POST["arr"])) {
                foreach ($_POST["arr"] as $plugin) {
                    $saved_plugins[] = esc_html($plugin);
                }
                $selective_loading_templates[$category] = $saved_plugins;
            }
            
            else {
                unset($selective_loading_templates[$category]);
            }
            
            
            if(array_key_exists($category, $selective_loading_templates) )
                $selective_loading_dashboards[$link]["template"] = $category;
            else
                unset($selective_loading_dashboards[$link]);

            update_option('selective_loading_templates', $selective_loading_templates);
            update_option('selective_loading_guids', $selective_loading_dashboards);
            print_r($saved_plugins);
            wp_die("updated");
        }
        
        private function _load_template($template) {
            $answer     = array();
            
            foreach ($template as $plugin) {
                $answer[] = basename($plugin, ".php");
            }
            
            echo implode(",", $answer);
            
            wp_die();
        }

        private function _load_plugin_loop( $plugins ) {
            
            $html           = '';
            $global         = '<optgroup label="Global Plugins">';
            $global_opt     = FALSE;
            $template       = '<optgroup label="Template plugins">';
            $template_opt   = FALSE;
            $post           = '';
            $end_opt        = '</optgroup>';
            
            foreach( $this->arr_plugins as $plugin ) {
                
                $plugin_name    = basename( $plugin, ".php" );
                $display        = get_plugin_data( WP_PLUGIN_DIR.'/'. $plugin );
                
                if( is_array( $plugins["post"] ) && in_array( $plugin_name, $plugins["post"] ) ) {
                    $html .= '<option value="'.$plugin.'" selected="selected" >'. $display['Name'] .'</option>';
                    continue;
                }
                
                if( is_array( $plugins["global"] ) && in_array( $plugin_name, $plugins["global"] ) ) {
                    $global_opt = TRUE;
                    $global .= '<option value="'.$plugin.'" disabled="disabled" >'. $display['Name'] .'</option>';
                    continue;
                }
                
                if( is_array( $plugins["template"] ) && in_array( $plugin_name, $plugins["template"] ) ) {
                    $template_opt = TRUE;
                    $template .= '<option value="'.$plugin.'" disabled="disabled" >'. $display['Name'] .'</option>';
                    continue;
                }
                
                $html .= '<option value="'.$plugin.'" >'. $display['Name'] .'</option>';
                
            }
            
            $html .= $global_opt ? $global.$end_opt : '';
            $html .= $template_opt ? $template.$end_opt : '';
            
            return $html;
        }
        
        private function _load_post_plugins( $post ) {
            
            $id         = esc_attr($_GET['post']);
            $link       = get_permalink($id);
            $template   = SELECTIVELOADING_PREFIX.basename($post->page_template, ".php");
            $templates  = get_option( 'selective_loading_templates', array() );
            $guids      = get_option( 'selective_loading_guids', array() );
            
            $global_plugins     = array();
            $template_plugins   = array();
            $post_plugins       = array();
            
            if( key_exists(SELECTIVELOADING_PREFIX.'global', $templates) ) {
                $global_plugins = $templates[SELECTIVELOADING_PREFIX.'global'];
                
                foreach ( $global_plugins as $plugin ) {
                    $return_arr["global"][] = basename($plugin, ".php");
                }
            }
            
            if( key_exists($template, $templates) ) {
                $template_plugins = $templates[$template];
                
                foreach( $template_plugins as $plugin ) {
                    $return_arr["template"][] = basename($plugin, ".php");
                }
            }
            
            if( key_exists($link, $guids) && key_exists("post", $guids[$link]) ) {
                
                $post_plugins = $guids[$link]["post"];
                
                foreach( $post_plugins as $plugin ) {
                    $return_arr["post"][] = basename($plugin, ".php");
                }
            }

            return $return_arr;
        }
        
        private function _essentials() {
            
            $this->arr_post_type    = array("post", "page", "template");
            $this->arr_plugins      = $this->_set_arr_plugins();
        }
        
        private function _enque_js_css() {

            wp_register_style('ms-css', plugins_url( 'css/multi-select.css' , SELECTIVELOADING_PATH ));
            wp_enqueue_style('ms-css');
            wp_register_script('ms-js', plugins_url( 'js/jquery.multi-select.js', SELECTIVELOADING_PATH ));
            wp_enqueue_script('ms-js', plugins_url( 'js/jquery.multi-select.js' , SELECTIVELOADING_PATH ), array('jquery') , FALSE, TRUE);

            wp_register_style('ms-css-hack', plugins_url( 'css/multi-select-hack.css' , SELECTIVELOADING_PATH ));
            wp_enqueue_style('ms-css-hack');

            wp_register_script('ms-hack', plugins_url( 'js/multi-select-hacks.js' , SELECTIVELOADING_PATH ));
            wp_enqueue_script('ms-hack', plugins_url( 'js/multi-select-hacks.js' , SELECTIVELOADING_PATH ), array('jquery','ms-js') , FALSE, TRUE);
        }
        
        private function _settings_enque_js_css() {
            wp_register_style('css-slp', plugins_url( 'css/slp.css' , SELECTIVELOADING_PATH ));
            wp_enqueue_style('css-slp');
            wp_register_script('js-slp', plugins_url( 'js/slp.js' , SELECTIVELOADING_PATH ));
            wp_enqueue_script('js-slp', plugins_url( 'js/slp.js' , SELECTIVELOADING_PATH ), array('jquery') , FALSE, TRUE);
        }

        private function _set_arr_plugins() {
            return get_option('hijacked_active_plugins', array());
        }
    }
}

if( class_exists('Selective_loading_dashboard', TRUE) ) {
    $selective_loading_dashboard = new Selective_loading_dashboard();
}