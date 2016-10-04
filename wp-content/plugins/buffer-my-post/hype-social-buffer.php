<?php
#     /*
#     Plugin Name: HYPE Social - Buffer
#     Plugin URI: http://www.hypestudio.org/
#     Description: Get more social traffic by easily connecting your WordPress to Buffer, and automatically sharing posts & pages to Twitter, Facebook, LinkedIn, etc.
# 
#     Author: hypestudio,dejanmarkovic,nytogroup,freemius
#     Version: 16.4.1
#     Author URI: http://www.hypestudio.org/
#     */

// Create a helper function for easy SDK access.

function hsb_fs() {
	global $hsb_fs;

	if ( ! isset( $hsb_fs ) ) {
		// Include Freemius SDK.
		require_once dirname(__FILE__) . '/freemius/start.php';

		$hsb_fs = fs_dynamic_init( array(
			'id'                => '133',
			'slug'              => 'buffer-my-post',
			'public_key'        => 'pk_d8db8d28d363893018e772b3b3a67',
			'is_premium'        => false,
			'has_addons'        => false,
			'has_paid_plans'    => false,
			'menu'              => array(
				'slug'       => 'HYPESocialBuffer',
				'account'    => false /*,

				'support'    => false,
				'contact'    => false,
				*/
			),
		) );
	}

	return $hsb_fs;
}

// Init Freemius.
hsb_fs();

// Customize msg
function hsb_custom_connect_message(
	$message,
	$user_first_name,
	$plugin_title,
	$user_login,
	$site_link,
	$freemius_link
) {
	return sprintf(
		__fs( 'hey-x' ) . '<br>' .
		__( 'In order to enjoy all our features and functionality, %s needs to connect your user, %s at %s, to %s', 'freemius' ),
		$user_first_name,
		'<b>' . $plugin_title . '</b>',
		'<b>' . $user_login . '</b>',
		$site_link,
		$freemius_link
	);
}

hsb_fs()->add_filter('connect_message', 'hsb_custom_connect_message', 10, 6);

// Handle uninstall
function hsb_fs_uninstall_cleanup() {
	delete_option( 'hsb_settings' );
	delete_option( 'hsb_opt_admin_url' );
	delete_option( 'hsb_opt_last_update' );
	delete_option( 'hsb_opt_omit_cats' );
	delete_option( 'hsb_opt_omit_custom_cats' );
	delete_option( 'hsb_opt_omit_cust_cats' );
	delete_option( 'hsb_opt_max_age_limit' );
	delete_option( 'hsb_opt_age_limit' );
	delete_option( 'hsb_opt_excluded_post' );
	delete_option( 'hsb_opt_post_type' );
	delete_option( 'hsb_opt_no_of_post' );
	delete_option( 'hsb_opt_posted_posts' );
	delete_option( 'hsb_opt_add_text' );
	delete_option( 'hsb_opt_add_text_at' );
	delete_option( 'hsb_opt_include_link' );
	delete_option( 'hsb_opt_interval' );
	delete_option( 'hsb_settings' );
	delete_option( 'hsb_enable_log' );
	delete_option( 'hsb_disable_buffer' );
	delete_option( 'hsb_opt_access_token' );
	delete_option( 'hsb_opt_post_format' );
	delete_option( 'hsb_opt_acnt_id' );
}

hsb_fs()->add_action('after_uninstall', 'hsb_fs_uninstall_cleanup');


define( 'hsb_opt_1_HOUR', 60 * 60 );
define( 'hsb_opt_2_HOURS', 2 * hsb_opt_1_HOUR );
define( 'hsb_opt_4_HOURS', 4 * hsb_opt_1_HOUR );
define( 'hsb_opt_8_HOURS', 8 * hsb_opt_1_HOUR );
define( 'hsb_opt_6_HOURS', 6 * hsb_opt_1_HOUR );
define( 'hsb_opt_12_HOURS', 12 * hsb_opt_1_HOUR );
define( 'hsb_opt_24_HOURS', 24 * hsb_opt_1_HOUR );
define( 'hsb_opt_48_HOURS', 48 * hsb_opt_1_HOUR );
define( 'hsb_opt_72_HOURS', 72 * hsb_opt_1_HOUR );
define( 'hsb_opt_168_HOURS', 168 * hsb_opt_1_HOUR );
define( 'hsb_opt_INTERVAL', 4 );
define( 'hsb_opt_AGE_LIMIT', 30 ); // 120 days
define( 'hsb_opt_MAX_AGE_LIMIT', 60 ); // 120 days
define( 'hsb_opt_OMIT_CATS', "" );
define( 'hsb_opt_OMIT_CUSTOM_CATS', "" );
define( 'hsb_opt_HASHTAGS', "" );
define( 'hsb_opt_no_of_post', "1" );
define( 'hsb_opt_post_type', "post" );
define( 'hsb_opt_number_repeats', 10 );  //set this as option in settings?
define( 'hsb_opt_purchase_code', '' );
define( 'hsb_opt_envato_user_name', '' );

require_once( 'hsb-core.php' );
require_once( 'hsb-admin.php' );
