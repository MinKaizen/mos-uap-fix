<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

require_once( PLUGIN_DIR . '/includes/functions.php' );

class Debugger {

	const ROUTE = '/mos-uap-fix-debug';

	private static ?self $instance;

	public static function instance(): self {
		$instance = self::$instance ?? new self();
		self::$instance = $instance;
		return $instance;
	}

	public function init(): void {
		add_action('template_redirect', function() {
			if (current_request_uri_is(self::ROUTE)) {
				$this->main();
				die;
			}
		}, 0, 0);
	}

	public function main() {
		$settings = Settings::instance();

		echo '<pre style="font-size: 1.5em;">';

		echo '<strong>Param Name: </strong>';
		echo $settings->get_param_name();
		echo '<br>';

		echo '<strong>Param Identifier: </strong>';
		echo $settings->get_param_identifier();
		echo '<br>';

		echo '<strong>Param Value: </strong>';
		$param_value = $_GET[Settings::instance()->get_param_name()];
		if (aff_param_is_present()) {
			echo $param_value;
		} else {
			echo "(Param not present!)";
		}
		echo '<br>';

		echo '<strong>';
		echo 'Sponsor WPID (param): ';
		echo '</strong>';
		$id = get_sponsor_wpid_from_param();
		echo $id;
		echo '<br>';

		echo '<strong>';
		echo 'Sponsor WPID (cookie): ';
		echo '</strong>';
		$id_cookie = get_sponsor_wpid_from_cookie();
		echo $id_cookie;
		echo '<br>';
		echo '<br>';
		echo '</pre>';
	}

}
