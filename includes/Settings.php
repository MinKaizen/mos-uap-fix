<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

use function \get_option;

class Settings {
	const OPTION_NAME = 'mos_uap_fix_settings';
	const PARAM_NAME = 'param_name';
	const PARAM_IDENTIFIER = 'param_identifier';
	const COOKIE_NAME = 'cookie_name';
	const COOKIE_EXPIRATION_DAYS = 'cookie_expiration_days';
	const AUTO_SET_USER_AS_AFFILIATE = 'auto_set_user_as_affiliate';
	const DEFAULTS = [
		'param_name' => 'id',
		'param_identifier' => 'username',
		'cookie_name' => 'mos_sponsor_wpid',
		'cookie_expiration_days' => 360,
		'auto_set_user_as_affiliate' => false,
	];

	private static ?self $instance;
	private array $options;

	public function __construct() {
		$options = get_option( self::OPTION_NAME );
		$this->options = is_array($options) ? $options : [];
	}

	public static function instance(): self {
		$instance = self::$instance ?? new self();
		self::$instance = $instance;
		return $instance;
	}

	public function init(): void {
		include('settings-page.php');
	}

	public function get_param_name(): string {
		$param_name = $this->options[self::PARAM_NAME] ?? self::DEFAULTS[self::PARAM_NAME];
		return (string) $param_name;
	}

	public function get_param_identifier(): string {
		$param_identifier = $this->options[self::PARAM_IDENTIFIER] ?? self::DEFAULTS[self::PARAM_IDENTIFIER];
		return (string) $param_identifier;
	}

	public function get_cookie_name(): string {
		$cookie_name = $this->options[self::COOKIE_NAME] ?? self::DEFAULTS[self::COOKIE_NAME];
		return (string) $cookie_name;
	}

	public function get_cookie_expiration_days(): int {
		$cookie_expiration_days = $this->options[self::COOKIE_EXPIRATION_DAYS] ?? self::DEFAULTS[self::COOKIE_EXPIRATION_DAYS];
		return (int) $cookie_expiration_days;
	}

	public function get_auto_set_user_as_affiliate(): bool {
		$auto_set_user_as_affiliate = $this->options[self::AUTO_SET_USER_AS_AFFILIATE] ?? self::DEFAULTS[self::AUTO_SET_USER_AS_AFFILIATE];
		return (bool) $auto_set_user_as_affiliate;
	}

	public function get_encryption(): bool {
		return true;
	}
}
