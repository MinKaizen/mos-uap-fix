<?php declare(strict_types=1);

namespace MOS_UAP_Fix;

use \WP_User;

use function \get_user_by;

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
