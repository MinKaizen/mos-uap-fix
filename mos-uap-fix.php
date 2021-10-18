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

// Github Update constans
define( NS . 'GITHUB_USER', 'MinKaizen' );
define( NS . 'GITHUB_REPO', 'mos-uap-fix' );

require_once( PLUGIN_DIR . '/includes/Plugin.php' );
require_once( PLUGIN_DIR . '/includes/Updater.php' );

$plugin = Plugin::instance();
$plugin->init();

$updater = new Updater( __FILE__ );
$updater->set_username( GITHUB_USER );
$updater->set_repository( GITHUB_REPO );
$updater->initialize();
