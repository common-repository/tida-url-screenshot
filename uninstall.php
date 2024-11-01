<?php
/*
*
* Uninstall Tida URL Screenshot Plugin
*
*/

if(!defined('WP_UNINSTALL_PLUGIN'))
	exit();

// delete general options
delete_option('tida_screenshot_input_class');
delete_option('tida_screenshot_button_class');

// delete api options
delete_option('tida_screenshot_api_service');
delete_option('tida_screenshot_api_token');

?>