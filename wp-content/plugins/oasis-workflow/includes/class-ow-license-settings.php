<?php
/*
 * Settings class for Add-on License settings
*
* @copyright   Copyright (c) 2016, Nugget Solutions, Inc
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       2.0
*
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * OW_License_Settings Class
 *
 * License Settings for Add-ons
 *
 * @since 2.0
 */
class OW_License_Settings {

	/**
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		?>
		<form id="wf_settings_form" method="post" action="options.php">
    	<?php
    	// adds nonce and option_page fields for the settings page
    	settings_fields('ow-settings-license');
    	?>
			<div id="workflow-general-setting">
				<div id="license-setting">

					<?php
					// action to add license settings for add-ons
					do_action( 'owf_add_license_settings' );
					?>
					<div class="select-info full-width">
						<input type="submit" class="button button-primary button-large" name="oasiswf_license_activate" value="<?php _e('Save'); ?>"/>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php wp_nonce_field( 'owf_license_nonce', 'owf_license_nonce' ); ?>
		</form>
	<?php
	}
}

$ow_license_settings = new OW_License_Settings();
?>