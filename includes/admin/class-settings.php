<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Tida_URL_Screenshot_Settings {

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct() {
		//$this->file = $file;
		//$this->dir = dirname( $this->file );
		$this->base = 'tida_screenshot_';

		// Initialise settings
		add_action( 'admin_init', array( &$this, 'init' ) );

		// Register plugin settings
		add_action( 'admin_init' , array( &$this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( &$this, 'add_menu_item' ) );

		// admin footer
		add_action('admin_head', array(&$this, 'admin_header_scripts'));

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( TIDA_URL_SCREENSHOT_PLUGIN_DIR . 'tida-url-screenshot-tool.php' ) , array( &$this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item() {
		$page = add_options_page( __( 'Tida URL Screenshot Settings', 'tida_url_screenshot' ) , __( 'Tida URL Screenshot Settings', 'tida_url_screenshot' ) , 'manage_options' , 'tida-url-screenshot-settings' ,  array( &$this, 'settings_page' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link[] = '<a href="'.esc_url( add_query_arg( array( 'page' => 'ipgeo-settings' ) , admin_url( '/options-general.php' ) ) ).'">' . __( 'Settings', 'ipgeo' ) . '</a>';
		$settings_link = array_merge( $settings_link, $links );
  		return $settings_link;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {

		$settings['general'] = array(
			'title'       => __( 'General', 'tida_url_screenshot' ),
			'description' => __( 'This section is the appearance settings.', 'tida_url_screenshot' ),
			'fields'				=> array(
				array(
					'id' 			=> 'input_class',
					'label'			=> __( 'Input Class' , 'tida_url_screenshot' ),
					'description'	=> __( 'You can enter name of input class for custom style.', 'tida_url_screenshot' ),
					'type'			=> 'text',
					'default'		=> '',
					'length'		=> 20,
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class',
					'label'			=> __( 'Button Class' , 'tida_url_screenshot' ),
					'description'	=> __( 'You can enter name of button class for custom style.', 'tida_url_screenshot' ),
					'type'			=> 'text',
					'default'		=> '',
					'length'		=> 20,
					'placeholder'	=> ''
				),
			)
		);

		$settings['api'] = array(
			'title'       => __( 'API', 'tida_url_screenshot' ),
			'description' => __( 'This section is the settings of API service.', 'tida_url_screenshot' ),
			'fields'				=> array(
				array(
					'id'          => 'api_service',
					'label'       => __( 'API Service', 'tida_url_screenshot' ),
					'description' => __( 'Please select the service for taking the screenshot', 'tida_url_screenshot' ),
					'type'        => 'select',
					'options'     => apply_filters('tida_screenshot_api_list', $list )
				),
				array(
					'id' 			=> 'api_token',
					'label'			=> __( 'API Token' , 'ipgo' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'length'		=> 70,
					'placeholder'	=> ''
				),
			)
		);

		$settings = apply_filters( 'tida_url_screenshot_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab.
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = sanitize_text_field( $_POST['tab'] );
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = sanitize_text_field( $_GET['tab'] );
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section !== $section ) {
					continue;
				}

				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), 'tida_url_screenshot_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field.
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting( 'tida_url_screenshot_settings', $option_name, $validation );

					// Add field to page.
					add_settings_field(
						$field['id'],
						$field['label'],
						array( &$this, 'display_field' ),
						'tida_url_screenshot_settings',
						$section,
						array(
							'field'  => $field,
							'prefix' => $this->base,
						)
					);
				}

				if ( ! $current_section ) {
					break;
				}
			}
		}
	}

	/**
	 * Settings section description field.
	 *
	 * @param array $section Array of section ids.
	 * @return void
	 */
	public function settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo apply_filters('settings_section_output', $html);
	}

	/**
	 * Generate HTML for displaying fields.
	 *
	 * @param  array   $data Data array.
	 * @param  object  $post Post object.
	 * @param  boolean $echo  Whether to echo the field HTML or return it.
	 * @return string
	 */
	public function display_field( $data = array(), $post = null, $echo = true ) {

		// Get field info.
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name.
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data.
		$data = '';
		if ( $post ) {

			// Get saved field data.
			$option_name .= $field['id'];
			$option       = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field.
			if ( isset( $option ) ) {
				$data = $option;
			}
		} else {

			// Get saved option.
			$option_name .= $field['id'];
			$option       = get_option( $option_name );

			// Get data to display in field.
			if ( isset( $option ) ) {
				$data = $option;
			}
		}

		// Show default data if no option saved and default is supplied.
		if ( false === $data && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( false === $data ) {
			$data = '';
		}

		$html = '';

		switch ( $field['type'] ) {

			case 'text':
			case 'url':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" size="'.esc_attr( $field['length'] ).'" value="' . esc_attr( $data ) . '" />' . "\n";
				break;

			case 'hidden':
				$min = '';
				if ( isset( $field['min'] ) ) {
					$min = ' min="' . esc_attr( $field['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['max'] ) ) {
					$max = ' max="' . esc_attr( $field['max'] ) . '"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
				break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' === $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
				break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k === $data ) {
						$selected = true;
					}
					if($k == "mapbox")
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '"  disabled="disabled">' . $v . '</option>';
					else
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

		}

		switch ( $field['type'] ) { // for description

			case 'text':
			case 'select':
				$html .= '<p class="description">' . $field['description'] . '</p>';
				break;

			default:
				if ( ! $post ) {
					$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
				}

				$html .= '<span class="description">' . $field['description'] . '</span>' . "\n";

				if ( ! $post ) {
					$html .= '</label>' . "\n";
				}
				break;
		}

		if ( ! $echo ) {
			return apply_filters('display_field_output', $html);
		}

		echo apply_filters('display_field_output', $html);

	}

	/**
	 * Validate form field
	 *
	 * @param  string $data Submitted value.
	 * @param  string $type Type of field to validate.
	 * @return string       Validated value
	 */
	public function validate_field( $data = '', $type = 'text' ) {

		switch ( $type ) {
			case 'text':
				$data = esc_attr( $data );
				break;
			case 'url':
				$data = esc_url( $data );
				break;
			case 'email':
				$data = is_email( $data );
				break;
		}

		return $data;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page() {

		// Build page HTML.
		$html = '<div class="wrap" id="tida_url_screenshot_settings">' . "\n";
			$html .= '<h2>' . __( 'Tida URL Screenshot Settings', 'tida_url_screenshot' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= sanitize_text_field( $_GET['tab'] );
			}

			// Show page tabs.
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class.
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 === $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == sanitize_text_field( $_GET['tab'] ) ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link.
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab.
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

				$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields.
				ob_start();
				settings_fields( 'tida_url_screenshot_settings' );
				do_settings_sections( 'tida_url_screenshot_settings' );
				$html .= ob_get_clean();

				$html     .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings', 'tida_url_screenshot' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo apply_filters('settings_page_output', $html);
	}

	public function admin_header_scripts()
	{
		global $pagenow;

		//Check if current admin page is Option Tree settings
		if ( $pagenow == 'options-general.php' && $_GET['page'] == 'tida-url-screenshot-settings' ) {
		?>
		<script>
		jQuery(document).ready(function(){
			// event for api token
			api_token_field_toggle();
			jQuery("select[name=tida_screenshot_api_service]").on('change', function(){ api_token_field_toggle(); });
		});

		function api_token_field_toggle()
		{
			var api_selector = jQuery("select[name=tida_screenshot_api_service]").val();
			if(api_selector=="ip-api" || api_selector=="ipwhois")
				jQuery("input[name=tida_screenshot_api_token]").parent().parent().slideUp();
			else
				jQuery("input[name=tida_screenshot_api_token]").parent().parent().slideDown();
		}
		</script>
		<?php
		}
	}

}

new Tida_URL_Screenshot_Settings();
?>