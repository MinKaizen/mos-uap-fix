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
require_once( PLUGIN_DIR . '/includes/Plugin.php' );

$plugin = Plugin::instance();
$plugin->init();

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
