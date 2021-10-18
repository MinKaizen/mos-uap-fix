<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

use \WP_User;

use function \get_user_by;
use function \get_option;

define( __NAMESPACE__ . '\NO_ID', 0 );
define( __NAMESPACE__ . '\AFF_PARAM_NAME', 'id' );
define( __NAMESPACE__ . '\COOKIE_NAME', 'mos_sponsor_wpid' );
define( __NAMESPACE__ . '\COOKIE_EXPIRATION_DAYS', 360 );


function aff_param_is_present(): bool {
	return !empty($_GET[AFF_PARAM_NAME]);
}

function get_username_from_param(): string {
	$username = aff_param_is_present() ? (string) $_GET[AFF_PARAM_NAME] : '';
	return $username;
}

function get_id_by_username(string $username): int {
	$user = get_user_by('login', $username);
	if ($user instanceof WP_User) {
		$id = $user->ID;
	} else {
		$id = NO_ID; // No matching user found
	}
	return $id;
}

function get_affid_by_id( int $id ): int {
	$user = get_user_by( 'ID', $id );

	if (!($user instanceof WP_User)) {
		$affid = NO_ID;
		return $affid;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'uap_affiliates';
	$query = "SELECT id FROM $table WHERE uid=$id LIMIT 1";
	$result = $wpdb->get_var($query);

	$affid = is_string($result) ? (int) $result : NO_ID;

	return $affid;
}

function get_domain_name(): string {
	$domain_name = parse_url( home_url(), \PHP_URL_HOST );
	$domain_name = is_string($domain_name) ? $domain_name : '';
	return $domain_name;
}

function set_sponsor_cookie( int $id ): void {
	$name = COOKIE_NAME;
	$value = (string) $id;
	$expiration = time() + COOKIE_EXPIRATION_DAYS * 24 * 60 * 60;
	$path = '/'; // available on entire domain
	$domain = get_domain_name();
	$secure = false;
	$httponly = true;

	// Sets the cookie, but requires refresh
	setcookie( $name, $value, $expiration, $path, $domain, $secure, $httponly );
	// Also set the cookie manually for this session
	$_COOKIE[COOKIE_NAME] = $value;
}

function get_sponsor_id_from_cookie(): int {
	if (empty($_COOKIE[COOKIE_NAME])) {
		$id = NO_ID;
		return $id;
	}

	$id = (int) $_COOKIE[COOKIE_NAME];
	return $id;
}

function set_sponsor_relationship( int $user_id, int $sponsor_id ): void {
	$sponsor_affid = get_affid_by_id( $sponsor_id );

	if ( $sponsor_affid == NO_ID ) {
		return;
	}

	global $wpdb;

	// Insert into uap_affiliate_referral_users_relations table
	$table = $wpdb->prefix . 'uap_affiliate_referral_users_relations';
	$data = [
		'affiliate_id' => $sponsor_affid,
		'referral_wp_uid' => $user_id,
	];
	$formats = [
		'affiliate_id' => '%d',
		'referral_wp_uid' => '%d',
	];
	$wpdb->insert( $table, $data, $formats );

	// Insert into uap_mlm_relations table
	$user_affid = get_affid_by_id( $user_id );

	if ( $sponsor_affid == NO_ID ) {
		return;
	}

	$table = $wpdb->prefix . 'uap_mlm_relations';
	$data = [
		'affiliate_id' => $user_affid,
		'parent_affiliate_id' => $sponsor_affid,
	];
	$formats = [
		'affiliate_id' => '%d',
		'parent_affiliate_id' => '%d',
	];

	$wpdb->insert( $table, $data, $formats );
}

function set_user_as_affiliate( int $user_id ): void {
	$user = get_user_by( 'ID', $user_id );

	if ( !($user instanceof WP_User) ) {
		// User doesn't exist!
		return;
	}

	global $wpdb;

	$table = $wpdb->prefix . 'uap_affiliates';
	$data = [
		'uid' => $user_id,
		'rank_id' => 0,
		'status' => 2, // Verified
	];
	$formats = [
		'uid' => '%d',
		'rank_id' => '%d',
		'status' => '%d',
	];
	$wpdb->insert( $table, $data, $formats );
}

function is_plugin_active(string $plugin_slug): bool {
	$full_plugin_name = "$plugin_slug/$plugin_slug.php";
	$active_plugins = get_option( 'active_plugins' );
	return in_array($full_plugin_name, $active_plugins);
}

/**
 * @return string - The current request url, including http[s] and any query string parameters
 */
function current_url(): string {
	if (empty($_SERVER)) {
		return '';
	}

	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
	$full_url = "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	return $full_url;
}

/**
 * @param bool $include_params - whether to include query string parameters
 * @return string - The current request uri, with leading slash, but without traiing slash
 */
function current_request_uri(bool $include_params = false): string {
	$url = parse_url(current_url());
	$request_uri = $url['path'] ?? '';

	if ($include_params && !empty($url['query'])) {
		// Include query string parameters
		$query = $url['query'];
		$request_uri = "$request_uri?$query";
	}

	// Strip trailing slash unless request uri is root (/)
	if ($request_uri !== '/') {
		$request_uri = rtrim($request_uri, '/');
	}

	return $request_uri;
}

/**
 * @param string $uri - the requst uri to compare to. Can include leading and trailing slashes, but no query parameters
 * @return bool - whether or not the current request uri matches the given string
 */
function current_request_uri_is(string $uri): bool {
	$current_uri = current_request_uri();

	// Add leading slash
	if (substr($uri, 0, 1) !== '/') {
		$uri = '/' . $uri;
	}

	// Strip trailing slash
	if ($uri !== '/') {
		$uri = rtrim($uri, '/');
	}

	return $current_uri === $uri;
}
