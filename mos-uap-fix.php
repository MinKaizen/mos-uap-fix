<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

use function \add_action;
use function \update_user_meta;

/**
 * Main Plugin File
 *
 * @link              https://github.com/MinKaizen/mos-uap-fix
 * @since             1.0.0
 * @package           MOS_UAP_Fix
 *
 * @wordpress-plugin
 * Plugin Name:       MOS UAP Fix
 * Plugin URI:        https://github.com/MinKaizen/mos-uap-fix
 * Description:       Hacky fix for UAP plugin (Indeed Ultimate Affiliate Pro)
 * Version:           1.0.0
 * Author:            MinKaizen
 * Author URI:        https://github.com/MinKaizen/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mos-uap-fix
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin constants
define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
define( NS . 'PLUGIN_NAME', 'mos-uap-fix' );
define( NS . 'PLUGIN_VERSION', '1.0.0' );
define( NS . 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( NS . 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( NS . 'PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once( PLUGIN_DIR . '/includes/functions.php' );

// Generate and replace cookie if aff param is present
add_action( 'init', function() {
	if ( !aff_param_is_present() ) {
		// No param is set. Do nothing
		return;
	}

	$sponsor_username = get_username_from_param();
	$sponsor_id = get_id_by_username( $sponsor_username );
	if ( $sponsor_id === NO_ID ) {
		// User doesn't exist. Do nothing
		return;
	} else {
		set_sponsor_cookie( $sponsor_id );
	}
}, 10, 0 );

// On user register, create sponsor relationship
add_action( 'user_register', function( $user_id ) {
	$sponsor_id = get_sponsor_id_from_cookie();
	if ( $sponsor_id !== NO_ID ) {
		set_sponsor_relationship( $user_id, $sponsor_id );
	}
}, 10, 1 );

// On user register, set user as affiliate
add_action( 'user_register', function( $user_id ) {
	set_user_as_affiliate( $user_id );
}, 10, 1 );


add_action( 'init', function() {
	main();
	die;
}, 999, 0);

function main(): void {
	echo '<pre style="font-size: 1.5em;">';

	echo '<strong>';
	echo 'Sponsor username (param) ';
	echo '</strong>';
	$username = get_username_from_param();
	if (aff_param_is_present()) {
		echo $username;
	} else {
		echo "(Param not present!)";
	}
	echo '<br>';

	echo '<strong>';
	echo 'Sponsor id: ';
	echo '</strong>';
	$id = get_id_by_username($username);
	echo $id;
	echo '<br>';

	echo '<strong>';
	echo 'Sponsor id (cookie): ';
	echo '</strong>';
	$id_cookie = get_sponsor_id_from_cookie();
	echo $id_cookie;
	echo '<br>';
	echo '<br>';

	echo 'Show cookies';
	echo '<br>';
	var_dump( $_COOKIE );
	echo '<br>';

	echo '</pre>';
}
