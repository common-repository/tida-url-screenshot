<?php
/*
Plugin Name: Tida URL Screenshot
Description: This plugin is adding the tool on your website that take a screenshot from URL.
Version: 1.0
Author: Tidaweb
Author URI: https://tidaweb.com
Textdomain: tida_url_screenshot
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('Tida_URL_Screenshot'))
{
	class Tida_URL_Screenshot
	{
		/**
		 * Construct the plugin object
		 */
    	public function __construct()
    	{
            define( 'TIDA_URL_SCREENSHOT_VERSION', '1.1' );
            define( 'TIDA_URL_SCREENSHOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Initialize Settings
			add_action( 'wp_enqueue_scripts', 					array(&$this, 'header_enqueue_scripts'));
			
			// tida_screenshot shortcode
			add_shortcode( 'tida_screenshot',					array(&$this, 'tida_screenshot_shortcode'));

            // add ajax for form
            add_action( 'wp_ajax_get_tida_screenshot', 			array(&$this, 'ajax_get_tida_screenshot'));
			add_action( 'wp_ajax_nopriv_get_tida_screenshot', 	array(&$this, 'ajax_get_tida_screenshot'));

            // initialize classes
            self::initialize_classes();

			do_action('tida_url_screenshot_plugin_hooks');
			
		} // END public function __construct()

        /**
         * initialize classes of tida url screenshot
         */
        public static function initialize_classes()
        {
			// include settings page
            require_once('includes/admin/class-settings.php');

			// include api services
			require_once('includes/class-API.php');
			require_once('includes/class-abstractapi.php');
        }

		public function header_enqueue_scripts()
		{
			global $post;
			if(has_shortcode($post->post_content, 'tida_screenshot'))
			{
				wp_enqueue_style('tida-screenshot', plugins_url( '/assets/css/tida-screenshot.css', __FILE__ ) );
				wp_register_script('tida-screenshot', plugins_url( '/assets/js/tida-screenshot.js', __FILE__ ), array('jquery') );

				wp_localize_script( 'tida-screenshot', 'tida_screenshot_params', [
					'ajax_url' => admin_url('admin-ajax.php'),
					'please_text' => __('Please Wait', 'tida_url_screenshot')
				]);

				wp_enqueue_script('tida-screenshot');
			}
			
		}
        
		/**
		 * IP Geo Shortcode: show IP Geo Location form and result
		 */
        public function tida_screenshot_shortcode()
        {
			ob_start();
			$tida_screenshot_input_class = get_option('tida_screenshot_input_class');
			$tida_screenshot_button_class = get_option('tida_screenshot_button_class');
            ?>
            <form class="tida_screenshot_form" method="post" action="">
				<?php wp_nonce_field('tida_url_screenshot_nonce', 'tida_url_screenshot_nonce'); ?>
                <input type="text" <?php if(!empty($tida_screenshot_input_class)) echo 'class="'.esc_attr( $tida_screenshot_input_class ).'"'; ?> name="url" placeholder="<?php echo __('Enter URL...', 'tida_url_screenshot'); ?>" />
                <input type="submit" <?php if(!empty($tida_screenshot_button_class)) echo 'class="'.esc_attr( $tida_screenshot_button_class ).'"'; ?> name="check" value="<?php echo __('Take Screenshot', 'tida_url_screenshot'); ?>" />
            </form>
			<div class="screenshot_msg"></div>
			<div class="screenshot_result"><img src="" /></div>
			<?php

            $output = ob_get_contents();
            ob_end_clean();
            return apply_filters('tida_screenshot_shortcode_filter', $output);
		}


		
		/**
		 * Ajax handler for getting the screenshot from api
		 */
		public function ajax_get_tida_screenshot()
		{
			// get screenshot from api
			$result = [];
			$url = sanitize_url( $_POST['url'] );
			if(!is_null($url))
			{
				$api_service = get_option('tida_screenshot_api_service');
				switch($api_service)
				{
					case "abstractapi":
						$result = [ 'status' => true , 'src' => AbstractAPI::prepare_data_for_api( $url ), 'msg' => '' ];
						break;
					default:
						$result = [ 'status' => false , 'src' => 'NO URL' ];
						break;
				}
			}
			
			echo wp_json_encode( $result );
			wp_die();
		}
		
		/**
		* Activate the plugin
		*/
		public static function activate()
		{
			// Do nothing
		} // END public static function activate

		/**
		* Deactivate the plugin
		*/
		public static function deactivate()
		{
			// Do nothing
		} // END public static function deactivate	
	
	} // END class Tida_URL_Screenshot
} // END if(!class_exists('Tida_URL_Screenshot'))

if(class_exists('Tida_URL_Screenshot'))
{
	// instantiate the plugin class
	new Tida_URL_Screenshot();

    register_activation_hook( __FILE__, array( 'Tida_URL_Screenshot', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'Tida_URL_Screenshot', 'deactivate' ) );
}



