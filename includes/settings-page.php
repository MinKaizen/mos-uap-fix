<?php

namespace MOS_UAP_Fix;

require_once('Settings.php');

add_action( 'admin_menu', NS . 'mos_uap_fix_add_admin_menu' );
add_action( 'admin_init', NS . 'mos_uap_fix_settings_init' );

function mos_uap_fix_add_admin_menu() {
	add_options_page( 'UAP Fix', 'UAP Fix', 'manage_options', 'mos_uap_fix', NS . 'mos_uap_fix_options_page' );
}

function mos_uap_fix_settings_init() {
	register_setting( 'pluginPage', 'mos_uap_fix_settings' );

	add_settings_section(
		'mos_uap_fix_pluginPage_section',
		__( 'UAP Fix Settings', 'mos-uap-fix' ),
		NS . 'mos_uap_fix_settings_section_callback',
		'pluginPage'
	);

	add_settings_field(
		'param_name',
		__( 'Affiliate Link Parameter Name', 'mos-uap-fix' ),
		NS . 'param_name_setting_render',
		'pluginPage',
		'mos_uap_fix_pluginPage_section'
	);

	add_settings_field(
		'param_identifier',
		__( 'Affiliate Link Parameter Identifier', 'mos-uap-fix' ),
		NS . 'param_identifier_setting_render',
		'pluginPage',
		'mos_uap_fix_pluginPage_section'
	);

	add_settings_field(
		'cookie_expiration_days',
		__( 'Cookie Expiration (days)', 'mos-uap-fix' ),
		NS . 'cookie_expiration_days_setting_render',
		'pluginPage',
		'mos_uap_fix_pluginPage_section'
	);

	add_settings_field(
		'auto_set_user_as_affiliate',
		__( 'Automatically make new user affiliates', 'mos-uap-fix' ),
		NS . 'auto_set_user_as_affiliate_setting_render',
		'pluginPage',
		'mos_uap_fix_pluginPage_section'
	);
}

function param_name_setting_render() {
	$settings = Settings::instance();
	$param_name = $settings->get_param_name();
	?>
	<input type='text' name='<?php echo Settings::OPTION_NAME . "[" . Settings::PARAM_NAME . "]"; ?>' value='<?php echo $param_name; ?>' required>
	<span class="note">e.g. If set to <code>id</code>, affiliate links will become <code><?php echo home_url();?>/?id=blah</code></span>
	<?php
}

function param_identifier_setting_render() {
	$settings = Settings::instance();
	$param_identifier = $settings->get_param_identifier();
	?>
	<select name='<?php echo Settings::OPTION_NAME . "[" . Settings::PARAM_IDENTIFIER . "]"; ?>'>
		<option value="username" <?php echo ($param_identifier == 'username' ? 'selected' : ''); ?>>Username</option>
		<option value="wpid" <?php echo ($param_identifier == 'wpid' ? 'selected' : ''); ?>>WPID</option>
		<option value="email" <?php echo ($param_identifier == 'email' ? 'selected' : ''); ?>>Email</option>
	</select>
	<span class="note">What should be used in the affiliate link to identify an affiliate?</span>
	<?php
}

function cookie_expiration_days_setting_render() {
	$settings = Settings::instance();
	$cookie_expiration_days = $settings->get_cookie_expiration_days();
	?>
	<input type='number' required step="1" min="0" name='<?php echo Settings::OPTION_NAME . "[" . Settings::COOKIE_EXPIRATION_DAYS . "]"; ?>' value='<?php echo $cookie_expiration_days; ?>'>
	<span class="note">(Changes only apply to new cookies)</span>
	<?php
}

function auto_set_user_as_affiliate_setting_render(  ) {
	$settings = Settings::instance();
	$auto_set_user_as_affiliate = $settings->get_auto_set_user_as_affiliate();
	?>
	<input type='checkbox' name='<?php echo Settings::OPTION_NAME . "[" . Settings::AUTO_SET_USER_AS_AFFILIATE . "]"; ?>' <?php checked( $auto_set_user_as_affiliate, 1 ); ?> value='1'>
	<span class="note">Only enable this if UAP setting <code>Automatically Affiliate</code> is off or not working</span>
	<?php
}


function mos_uap_fix_settings_section_callback() {}

function mos_uap_fix_options_page() {
	?>
	<style>
		.note {
			font-style: italic;
		}
	</style>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
	</form>
	<?php
}
