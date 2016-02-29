<?php
/**
 * Super Forms Calculator
 *
 * @package   Super Forms Calculator
 * @author    feeling4design
 * @link      http://codecanyon.net/item/super-forms-drag-drop-form-builder/13979866
 * @copyright 2015 by feeling4design
 *
 * @wordpress-plugin
 * Plugin Name: Super Forms Calculator
 * Plugin URI:  http://codecanyon.net/item/super-forms-drag-drop-form-builder/13979866
 * Description: Adds an extra element that allows you to do calculations on any of your fields
 * Version:     1.0.0
 * Author:      feeling4design
 * Author URI:  http://codecanyon.net/user/feeling4design
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!class_exists('SUPER_Calculator')) :


    /**
     * Main SUPER_Calculator Class
     *
     * @class SUPER_Calculator
     * @version	1.0.0
     */
    final class SUPER_Calculator {
    
        
        /**
         * @var string
         *
         *	@since		1.0.0
        */
        public $version = '1.0.0';

        
        /**
         * @var SUPER_Calculator The single instance of the class
         *
         *	@since		1.0.0
        */
        protected static $_instance = null;

        
        /**
         * Contains an array of registered script handles
         *
         * @var array
         *
         *	@since		1.0.0
        */
        private static $scripts = array();
        
        
        /**
         * Contains an array of localized script handles
         *
         * @var array
         *
         *	@since		1.0.0
        */
        private static $wp_localize_scripts = array();
        
        
        /**
         * Main SUPER_Calculator Instance
         *
         * Ensures only one instance of SUPER_Calculator is loaded or can be loaded.
         *
         * @static
         * @see SUPER_Calculator()
         * @return SUPER_Calculator - Main instance
         *
         *	@since		1.0.0
        */
        public static function instance() {
            if(is_null( self::$_instance)){
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        
        /**
         * SUPER_Calculator Constructor.
         *
         *	@since		1.0.0
        */
        public function __construct(){
            $this->init_hooks();
            do_action('super_calculator_loaded');
        }

        
        /**
         * Define constant if not already set
         *
         * @param  string $name
         * @param  string|bool $value
         *
         *	@since		1.0.0
        */
        private function define($name, $value){
            if(!defined($name)){
                define($name, $value);
            }
        }

        
        /**
         * What type of request is this?
         *
         * string $type ajax, frontend or admin
         * @return bool
         *
         *	@since		1.0.0
        */
        private function is_request($type){
            switch ($type){
                case 'admin' :
                    return is_admin();
                case 'ajax' :
                    return defined( 'DOING_AJAX' );
                case 'cron' :
                    return defined( 'DOING_CRON' );
                case 'frontend' :
                    return (!is_admin() || defined('DOING_AJAX')) && ! defined('DOING_CRON');
            }
        }

        
        /**
         * Hook into actions and filters
         *
         *	@since		1.0.0
        */
        private function init_hooks() {
            
            // Filters since 1.0.0

            // Actions since 1.0.0
            add_filter( 'super_shortcodes_after_form_elements_filter', array( $this, 'add_calculator_element' ), 10, 2 );

            if ( $this->is_request( 'frontend' ) ) {
                
                // Filters since 1.0.0
            	add_filter( 'super_form_styles_filter', array( $this, 'add_element_styles' ), 10, 2 );

                // Actions since 1.0.0
                
            }
            
            if ( $this->is_request( 'admin' ) ) {
                
                // Filters since 1.0.0
                add_filter( 'super_enqueue_styles', array( $this, 'add_stylesheet' ), 10, 1 );
                add_filter( 'super_enqueue_scripts', array( $this, 'add_scripts' ), 10, 1 );
                add_filter( 'super_form_styles_filter', array( $this, 'add_element_styles' ), 10, 2 );

                // Actions since 1.0.0

            }
            
            if ( $this->is_request( 'ajax' ) ) {

                // Filters since 1.0.0

                // Actions since 1.0.0

            }
            
        }

        
        /**
         * Hook into stylesheets of the form and add styles for the calculator element
         *
         *  @since      1.0.0
        */
        public static function add_element_styles( $styles, $attributes ) {
            $s = '.super-form-'.$attributes['id'].' ';
            $v = $attributes['settings'];
            $styles .= $s.'.super-calculator-canvas {';
    		$styles .= 'border: solid 1px ' . $v['theme_field_colors_border'] . ';';
    		$styles .= 'background-color: ' . $v['theme_field_colors_top'] . ';';
    		$styles .= '}';
            return $styles;
		}



        /**
         * Hook into stylesheets and add calculator stylesheet
         *
         *  @since      1.0.0
        */
        public static function add_stylesheet( $array ) {
            $suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            $assets_path    = str_replace( array( 'http:', 'https:' ), '', plugin_dir_url( __FILE__ ) ) . '/assets/';
            $frontend_path   = $assets_path . 'css/frontend/';
            $array['super-calculator'] = array(
                'src'     => $frontend_path . 'calculator' . $suffix . '.css',
                'deps'    => '',
                'version' => SUPER_VERSION,
                'media'   => 'all',
                'screen'  => array( 
                    'super-forms_page_super_create_form'
                ),
                'method'  => 'enqueue',
            );
            return $array;
        }


        /**
         * Hook into scripts and add calculator javascripts
         *
         *  @since      1.0.0
        */
        public static function add_scripts( $array ) {

			$suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            $assets_path    = str_replace( array( 'http:', 'https:' ), '', plugin_dir_url( __FILE__ ) ) . '/assets/';
            $frontend_path  = $assets_path . 'js/frontend/';
            $array['super-jquery-calculator'] = array(
                'src'     => $frontend_path . 'jquery.calculator.js',
                'deps'    => array( 'jquery', 'jquery-ui-mouse' ),
                'version' => SUPER_VERSION,
                'footer'  => false,
                'screen'  => array( 
                    'super-forms_page_super_create_form'
                ),
                'method' => 'enqueue'
            );
            $array['super-calculator'] = array(
                'src'     => $frontend_path . 'calculator' . $suffix . '.js',
                'deps'    => array( 'super-jquery-calculator' ),
                'version' => SUPER_VERSION,
                'footer'  => false,
                'screen'  => array( 
                    'super-forms_page_super_create_form'
                ),
                'method' => 'enqueue'
            );
            return $array;
        }


        /**
         * Handle the Calculator element output
         *
         *  @since      1.0.0
        */
        public static function calculator( $tag, $atts ) {
        	wp_enqueue_style( 'super-calculator', plugin_dir_url( __FILE__ ) . 'assets/css/frontend/calculator.min.css', array(), SUPER_VERSION );
            wp_enqueue_script( 'super-jquery-touch-punch', plugin_dir_url( __FILE__ ) . 'assets/js/frontend/jquery.ui.touch-punch.min.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-mouse' ), SUPER_VERSION );
            wp_enqueue_script( 'super-jquery-calculator', plugin_dir_url( __FILE__ ) . 'assets/js/frontend/jquery.calculator.js', array( 'jquery', 'jquery-ui-mouse' ), SUPER_VERSION );
			wp_enqueue_script( 'super-calculator', plugin_dir_url( __FILE__ ) . 'assets/js/frontend/calculator.min.js', array( 'super-jquery-calculator' ), SUPER_VERSION );
			$result = SUPER_Shortcodes::opening_tag( $tag, $atts );
	        $result .= SUPER_Shortcodes::opening_wrapper( $atts );
	        if( ( !isset( $atts['value'] ) ) || ( $atts['value']=='' ) ) {
	            $atts['value'] = '';
	        }else{
	            $atts['value'] = SUPER_Common::email_tags( $atts['value'] );
	        }
	        $styles = '';
	        if( !isset( $atts['height'] ) ) $atts['height'] = 100;
	        if( !isset( $atts['bg_size'] ) ) $atts['bg_size'] = 75;
	        if( !isset( $atts['background_img'] ) ) $atts['background_img'] = 0;
	        $image = wp_prepare_attachment_for_js( $atts['background_img'] );
            if( $image==null ) $image['url'] = plugin_dir_url( __FILE__ ) . 'assets/images/sign-here.png';
	        $styles .= 'height:' . $atts['height'] . 'px;';
	        $styles .= 'background-image:url(\'' . $image['url'] . '\');';
	        $styles .= 'background-size:' . $atts['bg_size'] . 'px;';
	        $result .= '<div class="super-calculator-canvas" style="' . $styles . '"></div>';
	        $result .= '<span class="super-calculator-clear"></span>';
	        $result .= '<textarea style="display:none;" class="super-shortcode-field"';
	        $result .= ' name="' . $atts['name'] . '"';
	        $result .= ' data-thickness="' . $atts['thickness'] . '"';
	        $result .= SUPER_Shortcodes::common_attributes( $atts, $tag );
	        $result .= ' />' . $atts['value'] . '</textarea>';
	        $result .= '</div>';
	        $result .= SUPER_Shortcodes::loop_conditions( $atts );
	        $result .= '</div>';
	        return $result;
        }


        /**
         * Hook into elements and add Calculator element
         * This element specifies the Calculator List by it's given ID and retrieves it's Groups
         *
         *  @since      1.0.0
        */
        public static function add_calculator_element( $array, $attributes ) {

            // Include the predefined arrays
            require( SUPER_PLUGIN_DIR . '/includes/shortcodes/predefined-arrays.php' );

	        $array['form_elements']['shortcodes']['calculator'] = array(
	            'callback' => 'SUPER_Calculator::calculator',
	            'name' => __( 'Calculator', 'super' ),
	            'icon' => 'pencil-square-o',
	            'atts' => array(
	                'general' => array(
	                    'name' => __( 'General', 'super' ),
	                    'fields' => array(
	                        'name' => SUPER_Shortcodes::name( $attributes, $default='calculator' ),
	                        'email' => SUPER_Shortcodes::email( $attributes, $default='Calculator' ),
	                        'label' => $label,
	                        'description'=>$description,
				            'tooltip' => $tooltip,
                            'validation' => array(
                                'name'=>__( 'Special Validation', 'super' ), 
                                'desc'=>__( 'How does this field need to be validated?', 'super' ), 
                                'default'=> (!isset($attributes['validation']) ? 'none' : $attributes['validation']),
                                'type'=>'select', 
                                'values'=>array(
                                    'none' => __( 'No validation needed', 'super' ),
                                    'empty' => __( 'Not empty', 'super' ), 
                                )
                            ),
	                        'error' => $error,
	                    ),
	                ),
	                'advanced' => array(
	                    'name' => __( 'Advanced', 'super' ),
	                    'fields' => array(
	                        'grouped' => $grouped,
	                        'width' => SUPER_Shortcodes::width( $attributes=null, $default=350, $min=0, $max=600, $steps=10, $name=null, $desc=null ),
	                        'height' => SUPER_Shortcodes::width( $attributes=null, $default=100, $min=0, $max=600, $steps=10, $name=null, $desc=null ),
	                        'exclude' => $exclude,
	                        'error_position' => $error_position,
	                    ),
	                ),
	                'icon' => array(
	                    'name' => __( 'Icon', 'super' ),
	                    'fields' => array(
	                        'icon_position' => $icon_position,
	                        'icon_align' => $icon_align,
	                        'icon' => SUPER_Shortcodes::icon( $attributes, 'pencil' ),
	                    ),
	                ),
	                'conditional_logic' => $conditional_logic_array
	            ),
	        );
            return $array;
        }


    }
        
endif;


/**
 * Returns the main instance of SUPER_Calculator to prevent the need to use globals.
 *
 * @return SUPER_Calculator
 */
function SUPER_Calculator() {
    return SUPER_Calculator::instance();
}


// Global for backwards compatibility.
$GLOBALS['SUPER_Calculator'] = SUPER_Calculator();