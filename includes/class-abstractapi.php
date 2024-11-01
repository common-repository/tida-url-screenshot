<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class AbstractAPI extends \Tida_Screenshot_API_Core {

	public function __construct() {
		
		// initialize parent constructor
		parent::__construct ();

		// initialize
		self::$api_url = 'https://screenshot.abstractapi.com/v1/';

	}

	/**
	 * Add API to list - add_filter for tida_screenshot_api_list
	 * @param list
	 */
	public function add_api_to_list( $list )
	{
		$list['abstractapi'] = 'Abstract API';
		return $list;
	}

	/**
	 * Prepare data for api
	 */
	public static function prepare_data_for_api( $web_url )
	{
		$final_url = add_query_arg([
			'api_key' => self::$api_token,
			'url' => esc_url( $web_url )
		], self::$api_url );
		
		// send data to api
		$final_result = parent::send_data_to_api( $final_url );

		return $final_result;
	}

}

new AbstractAPI();
?>