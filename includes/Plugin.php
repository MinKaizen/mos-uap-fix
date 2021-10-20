<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

require_once( PLUGIN_DIR . '/includes/functions.php' );
require_once( PLUGIN_DIR . '/includes/Debugger.php' );
require_once( PLUGIN_DIR . '/includes/Settings.php' );
require_once( PLUGIN_DIR . '/includes/Logger.php' );

class Plugin {

	private static ?self $instance;
	private Logger $logger;
	private array $init_errors = ['Did not run pre init checks from Plugin class'];

	public static function instance(): self {
		$instance = self::$instance ?? new self();
		self::$instance = $instance;
		return $instance;
	}

	public function init(): void {
		$this->pre_init_check();

		if (!$this->ok_to_init()) {
			$this->abort_init();
			return;
		}

		$this->logger = new Logger('mos-uap-fix', 'mos-uap-fix.log');
		$this->register_hooks();
		Settings::instance()->init();
		Debugger::instance()->init();
	}

	private function pre_init_check(): void {
		// Reset init errors
		$this->init_errors = [];

		$checks = [
			[
				'assertion' => 'UAP plugin is installed and active',
				'success' => is_plugin_active( 'indeed-affiliate-pro' ),
				'error_message' => 'UAP plugin is not active',
			],
		];

		foreach ( $checks as $check ) {
			if ( !$check['success'] ) {
				$this->init_errors[] = $check['error_message'];
			}
		}
	}

	private function ok_to_init(): bool {
		return empty($this->init_errors);
	}

	private function abort_init(): void {
		add_action('admin_notices', function() {
			$plugin_name = PLUGIN_NAME;
			$errors_as_string = implode(', ', $this->init_errors);
			echo '<div class="notice notice-error">';
			echo "Plugin $plugin_name failed to init: $errors_as_string";
			echo '</div>';
		});
	}

	private function register_hooks(): void {
		// Generate and replace cookie if aff param is present
		add_action( 'init', function() {
			if ( !aff_param_is_present() ) {
				// No param is set. Do nothing
				return;
			}

			$sponsor_id = get_sponsor_wpid_from_param();
			if ( $sponsor_id === NO_ID ) {
				// User doesn't exist. Do nothing
				return;
			} else {
				set_sponsor_cookie( $sponsor_id );
			}
		}, 10, 0 );

		// On user register, create sponsor relationship
		add_action( 'user_register', function( $user_id ) {
			// Set user as affiliate
			if (Settings::instance()->get_auto_set_user_as_affiliate()) {
				set_user_as_affiliate( $user_id );
				$affiliated_by_this = true;
			}

			// Create sponsor relationship
			if (!uap_cookie_is_present()) {
				$sponsor_id = get_sponsor_wpid_from_cookie();
				if ( $sponsor_id !== NO_ID ) {
					set_sponsor_relationship( $user_id, $sponsor_id );
					$sponsor_set_by_this = true;
				}
			}

			// Generate log
			$affid = get_affid_by_id( $user_id );
			$sponsor = get_sponsor($user_id);
			$log_data = [
				'user_id' => $user_id,
				'affiliated' => !empty($affid),
				'user_affid' => $affid,
				'affiliated_by' => !empty($affiliated_by_this) ? 'mos-uap-fix' : 'uap',
				'sponsor_set' => !empty($sponsor),
				'sponsor_id' => $sponsor->ID ?? 0,
				'sponsor_set_by' => !empty($sponsor_set_by_this) ? 'mos-uap-fix' : 'uap',
			];
			$log_message = json_encode($log_data);
			$this->logger->log($log_message);
		}, 999, 1 );
	}

}
