<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Tida_Screenshot_API_Core {

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public static $api_token = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  protected
	 * @since   1.0.0
	 */
	protected static $api_url = '';

	public function __construct() {
		
		// initialize
		self::$api_token = get_option('tida_screenshot_api_token');

		add_filter('tida_screenshot_api_list', array($this, 'add_api_to_list'));

	}

	/**
	 * Add API to list - add_filter for tida_screenshot_api_list
	 * @param list
	 */
	public function add_api_to_list( $list )
	{
		return $list;
	}

	/**
	 * Get url value
	 */
	public static function get_api_url()
	{
		return esc_url( self::$api_url );
	}

	/**
	 * Call API
	 * @param web_api_url
	 * @param body
	 * @return final_result
	 */
	public static function send_data_to_api( $web_api_url, $body = [] )
	{
		$final_image_url = '';

		if(!empty($web_api_url))
		{
			$request = wp_remote_get( $web_api_url, array( 'timeout' => 7000 ) );
			
			if ( is_wp_error( $request ) )
			{
				//error_log( print_r( $request, true ) );
				print_r( $request );
			}
			else
			{
				$response = wp_remote_retrieve_body( $request );
				$imageData = base64_encode($response);
			
				// Format the image SRC:  data:{mime};base64,{data};
				$final_image_url = 'data:image/png;base64,'.$imageData;
			}
		}
		

		return apply_filters('api_result_data_output', $final_image_url );
	}

	
}

new Tida_Screenshot_API_Core();
?>