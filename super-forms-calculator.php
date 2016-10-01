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
 * Version:     1.1.2
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
        public $version = '1.1.2';

        
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
            add_filter( 'super_shortcodes_after_form_elements_filter', array( $this, 'add_calculator_element' ), 10, 2 );
            
            // Filters since 1.0.8
            add_filter( 'super_common_attributes_filter', array( $this, 'add_element_attribute' ), 10, 2 );

            // Actions since 1.0.0



            if ( $this->is_request( 'frontend' ) ) {
                
                // Filters since 1.0.0
                add_filter( 'super_form_styles_filter', array( $this, 'add_element_styles' ), 10, 2 );
                add_filter( 'super_common_js_dynamic_functions_filter', array( $this, 'add_dynamic_function' ), 110, 2 );

                // Actions since 1.0.0
                $settings = get_option( 'super_settings' );
                if( isset( $settings['enable_ajax'] ) ) {
                    if( $settings['enable_ajax']=='1' ) {
                        add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts_before_ajax' ) );
                    }
                }

            }
            
            if ( $this->is_request( 'admin' ) ) {
                
                // Filters since 1.0.0
                add_filter( 'super_enqueue_styles', array( $this, 'add_stylesheet' ), 10, 1 );
                add_filter( 'super_enqueue_scripts', array( $this, 'add_scripts' ), 10, 1 );
                add_filter( 'super_form_styles_filter', array( $this, 'add_element_styles' ), 10, 2 );
                add_filter( 'super_common_js_dynamic_functions_filter', array( $this, 'add_dynamic_function' ), 110, 2 );

                // Filters since 1.0.8
                add_filter( 'super_shortcodes_after_form_elements_filter', array( $this, 'add_date_field_settings' ), 10, 2 );
                
                // Actions since 1.0.0

            }
            
            if ( $this->is_request( 'ajax' ) ) {

                // Filters since 1.0.0

                // Actions since 1.0.0

            }
            
        }


        /**
         * Hook into common attributes and add return_age attribute for date element
         *
         *  @since      1.0.8
        */
        public static function add_element_attribute( $result, $element ) {
            if( $element['tag']=='date' ) {
                $atts = $element['atts'];
                if( !isset( $atts['return_age'] ) ) $atts['return_age'] = '';
                $result .= ' data-return_age="' . $atts['return_age'] . '"';
            }
            return $result;
        }


        /**
         * Hook into settings and add Text field settings
         *
         *  @since      1.0.8
        */
        public static function add_date_field_settings( $array, $attributes ) {
            
            // Now add the age settings field
            $fields_array = $array['form_elements']['shortcodes']['date']['atts']['general']['fields'];
            $res = array_slice($fields_array, 0, 8, true);
            $setting['return_age'] = array(
                'desc' => __( 'Return age based on selected date to use with calculations', 'super-forms' ), 
                'default'=> ( !isset( $attributes['return_age'] ) ? '' : $attributes['return_age'] ),
                'type' => 'checkbox', 
                'filter'=>true,
                'values' => array(
                    'true' => __( 'Return age for calculation fields', 'super-forms' ),
                )
            );
            $res = $res + $setting + array_slice($fields_array, 1, count($fields_array) - 1, true);

            $array['form_elements']['shortcodes']['date']['atts']['general']['fields'] = $res;
            return $array;
        }


        /**
         * Enqueue scripts before ajax call is made
         *
         *  @since      1.0.0
        */
        public static function load_frontend_scripts_before_ajax() {
            wp_enqueue_style( 'super-calculator', plugin_dir_url( __FILE__ ) . 'assets/css/frontend/calculator.min.css', array(), SUPER_Calculator()->version );
            wp_enqueue_script( 'super-calculator', plugin_dir_url( __FILE__ ) . 'assets/js/frontend/calculator.min.js', array( 'jquery', 'super-common' ), SUPER_Calculator()->version );
        }


        /**
         * Hook into stylesheets of the form and add styles for the calculator element
         *
         *  @since      1.0.0
        */
        public static function add_dynamic_function( $functions ) {
            
            $functions['after_initializing_forms_hook'][] = array(
                'name' => 'init_calculator'
            );
            $functions['before_validating_form_hook'][] = array(
                'name' => 'init_calculator'
            );
            $functions['after_dropdown_change_hook'][] = array(
                'name' => 'init_calculator'
            );
            $functions['after_field_change_blur_hook'][] = array(
                'name' => 'init_calculator'
            );
            $functions['after_radio_change_hook'][] = array(
                'name' => 'init_calculator'
            );
            $functions['after_checkbox_change_hook'][] = array(
                'name' => 'init_calculator'
            );

            // @since 1.1.2
            $functions['after_form_data_collected_hook'][] = array(
                'name' => 'init_calculator_update_data_value'
            );

            return $functions;
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
                'version' => SUPER_Calculator()->version,
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
            $array['super-calculator'] = array(
                'src'     => $frontend_path . 'calculator.min.js',
                'deps'    => array( 'jquery' ),
                'version' => SUPER_Calculator()->version,
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
        public static function calculator( $tag, $atts, $inner, $shortcodes=null, $settings=null ) {
        	wp_enqueue_style( 'super-calculator', plugin_dir_url( __FILE__ ) . 'assets/css/frontend/calculator.min.css', array(), SUPER_Calculator()->version );
			wp_enqueue_script( 'super-calculator', plugin_dir_url( __FILE__ ) . 'assets/js/frontend/calculator.min.js', array( 'jquery' ), SUPER_Calculator()->version );
            $class = ''; 
            if( !isset( $atts['margin'] ) ) $atts['margin'] = '';
            if($atts['margin']!=''){
                $class = 'super-remove-margin'; 
            }
            $result = SUPER_Shortcodes::opening_tag( $tag, $atts, $class );
	        $result .= SUPER_Shortcodes::opening_wrapper( $atts, $inner, $shortcodes, $settings );
            if( !isset( $atts['decimals'] ) ) $atts['decimals'] = 2;
            if( !isset( $atts['thousand_separator'] ) ) $atts['thousand_separator'] = ',';
            if( !isset( $atts['decimal_separator'] ) ) $atts['decimal_separator'] = '.';
            if( !isset( $atts['math'] ) ) $atts['math'] = '';
            if( !isset( $atts['amount_label'] ) ) $atts['amount_label'] = '';
            $result .= '<div class="super-calculator-wrapper" data-decimals="' . $atts['decimals'] . '" data-thousand-separator="' . $atts['thousand_separator'] . '" data-decimal-separator="' . $atts['decimal_separator'] . '" data-super-math="' . $atts['math'] . '">';
            $result .= '<span class="super-calculator-label">' . $atts['amount_label'] . '</span>';

            $style = '';
            if( !isset( $atts['amount_width'] ) ) $atts['amount_width'] = 0;
            if( $atts['amount_width']!=0 ) {
                $style = 'width:' . $atts['amount_width'] . 'px;';
            }
            if( !empty( $style ) ) {
                $style = ' style="' . $style . '"';
            }
            $result .= '<span' . $style . ' class="super-calculator-currency-wrapper">';
            $result .= '<span class="super-calculator-currency">' . $atts['currency'] . '</span>';
            $result .= '<span class="super-calculator-amount">' . number_format( 0, $atts['decimals'], $atts['decimal_separator'], '' ) . '</span>';
            
            // @since v1.0.2
            if( !isset( $atts['format'] ) ) $atts['format'] = '';
            $result .= '<span class="super-calculator-format">' . $atts['format'] . '</span>';
            
            $result .= '</span>';
            $result .= '</div>';
	        $result .= '<input type="hidden" class="super-shortcode-field"';
	        $result .= ' name="' . $atts['name'] . '"';

            // @since v1.1.2
            if( (isset($atts['email_float'])) && ($atts['email_float']=='true') ) {
                $result .= ' data-email-float="true"';
            }

	        $result .= SUPER_Shortcodes::common_attributes( $atts, $tag );
	        $result .= ' />';
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
	            'name' => __( 'Calculator', 'super-forms' ),
	            'icon' => 'calculator',
	            'atts' => array(
	                'general' => array(
	                    'name' => __( 'General', 'super-forms' ),
	                    'fields' => array(
                            'name' => SUPER_Shortcodes::name( $attributes, $default='subtotal' ),
                            'math' => array(
                                'name'=>__( 'Calculation', 'super-forms' ), 
                                'desc'=>__( 'You can use tags to retrieve field values e.g: ({field1}+{field2})*7.5', 'super-forms' ),
                                'default'=> ( !isset( $attributes['math'] ) ? '' : $attributes['math'] ),
                                'placeholder'=>'({field1}+{field2})*7.5',
                                'required'=>true
                            ),
                            'amount_label' => array(
                                'name'=>__( 'Amount Label', 'super-forms' ), 
                                'desc'=>__( 'Set a label for the amount e.g: Subtotal or Total', 'super-forms' ),
                                'default'=> ( !isset( $attributes['amount_label'] ) ? '' : $attributes['amount_label'] ),
                                'placeholder'=>'',
                            ),
                            'format' => array(
                                'default'=> ( !isset( $attributes['format'] ) ? '' : $attributes['format'] ),
                                'name' => __( 'Amount format (example: %)', 'super-forms' ), 
                                'desc' => __( 'Set a format e.g: %, EUR, USD etc.', 'super-forms' )
                            ),
                            'currency' => array(
                                'name'=>__( 'Currency', 'super-forms' ), 
                                'desc'=>__( 'Set the currency of or leave empty for no currency e.g: $ or €', 'super-forms' ),
                                'default'=> ( !isset( $attributes['currency'] ) ? '$' : $attributes['currency'] ),
                                'placeholder'=>'$',
                            ),
                            'decimals' => array(
                                'name'=>__( 'Length of decimal', 'super-forms' ), 
                                'desc'=>__( 'Choose a length for your decimals (default = 2)', 'super-forms' ), 
                                'default'=> (!isset($attributes['decimals']) ? '2' : $attributes['decimals']),
                                'type'=>'select', 
                                'values'=>array(
                                    '0' => __( '0 decimals', 'super-forms' ),
                                    '1' => __( '1 decimal', 'super-forms' ),
                                    '2' => __( '2 decimals', 'super-forms' ),
                                    '3' => __( '3 decimals', 'super-forms' ),
                                    '4' => __( '4 decimals', 'super-forms' ),
                                    '5' => __( '5 decimals', 'super-forms' ),
                                    '6' => __( '6 decimals', 'super-forms' ),
                                    '7' => __( '7 decimals', 'super-forms' ),
                                )
                            ),
                            'decimal_separator' => array(
                                'name'=>__( 'Decimal separator', 'super-forms' ), 
                                'desc'=>__( 'Choose your decimal separator (comma or dot)', 'super-forms' ), 
                                'default'=> (!isset($attributes['decimal_separator']) ? '.' : $attributes['decimal_separator']),
                                'type'=>'select', 
                                'values'=>array(
                                    '.' => __( '. (dot)', 'super-forms' ),
                                    ',' => __( ', (comma)', 'super-forms' ), 
                                )
                            ),
                            'thousand_separator' => array(
                                'name'=>__( 'Thousand separator', 'super-forms' ), 
                                'desc'=>__( 'Choose your thousand separator (empty, comma or dot)', 'super-forms' ), 
                                'default'=> (!isset($attributes['thousand_separator']) ? ',' : $attributes['thousand_separator']),
                                'type'=>'select', 
                                'values'=>array(
                                    '' => __( 'None (empty)', 'super-forms' ),
                                    '.' => __( '. (dot)', 'super-forms' ),
                                    ',' => __( ', (comma)', 'super-forms' ), 
                                )
                            ),
                            'email_float' => array(
                                'desc' => __( 'This will remove the thousand separater from the number', 'super-forms' ), 
                                'default'=> ( !isset( $attributes['email_value'] ) ? '' : $attributes['email_value'] ),
                                'type' => 'checkbox', 
                                'filter'=>true,
                                'values' => array(
                                    'true' => __( 'Send/Save the value as a float format', 'super-forms' ),
                                )
                            ),                            
	                        'email' => SUPER_Shortcodes::email( $attributes, $default='Subtotal:' ),
	                        'label' => $label,
	                        'description'=>$description,
				            'tooltip' => $tooltip,
                            'validation' => array(
                                'name'=>__( 'Special Validation', 'super-forms' ), 
                                'desc'=>__( 'How does this field need to be validated?', 'super-forms' ), 
                                'default'=> (!isset($attributes['validation']) ? 'none' : $attributes['validation']),
                                'type'=>'select', 
                                'values'=>array(
                                    'none' => __( 'No validation needed', 'super-forms' ),
                                    'empty' => __( 'Not empty', 'super-forms' ), 
                                )
                            ),
	                        'error' => $error,
	                    ),
	                ),
	                'advanced' => array(
	                    'name' => __( 'Advanced', 'super-forms' ),
	                    'fields' => array(
	                        'grouped' => $grouped,
                            'align' => array(
                                'name'=> __('Alignment', 'super-forms' ),
                                'default'=> ( !isset( $attributes['align']) ? 'left' : $attributes['align']),
                                'type'=>'select', 
                                'values'=>array(
                                    'left' => 'Align Left', 
                                    'center' => 'Align Center', 
                                    'right' => 'Align Right', 
                                ),
                            ),
                            'amount_width' => array(
                                'type' => 'slider', 
                                'default'=> (!isset($attributes['amount_width']) ? 0 : $attributes['amount_width']),
                                'min' => 0, 
                                'max' => 600, 
                                'steps' => 10, 
                                'name' => __( 'Amount wrapper width in pixels', 'super-forms' ), 
                                'desc' => __( 'Set to 0 to use default CSS width.', 'super-forms' )
                            ),
                            'wrapper_width' => $wrapper_width,
                            'margin' => array(
                                'name'=>__( 'Remove margin', 'super-forms' ),
                                'default'=> (!isset($attributes['margin']) ? '' : $attributes['margin']),
                                'type'=>'select',
                                'values'=>array(
                                    ''=>'No',
                                    'no_margin'=>'Yes',
                                )
                            ),
	                        'exclude' => $exclude,
	                        'error_position' => $error_position,
	                    ),
	                ),
	                'icon' => array(
	                    'name' => __( 'Icon', 'super-forms' ),
	                    'fields' => array(
	                        'icon_position' => $icon_position,
	                        'icon_align' => $icon_align,
	                        'icon' => SUPER_Shortcodes::icon( $attributes, 'calculator' ),
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