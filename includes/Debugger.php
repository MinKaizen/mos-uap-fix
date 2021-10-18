<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

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
		echo '</pre>';
	}

}
