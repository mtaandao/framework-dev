<?php
/**
 * Deprecated pluggable functions from past Mtaandao versions. You shouldn't use these
 * functions and look for the alternatives instead. The functions will be removed in a
 * later version.
 *
 * Deprecated warnings are also thrown if one of these functions is being defined by a plugin.
 *
 * @package Mtaandao
 * @subpackage Deprecated
 * @see pluggable.php
 */

/*
 * Deprecated functions come here to die.
 */

if ( !function_exists('set_current_user') ) :
/**
 * Changes the current user by ID or name.
 *
 * Set $id to null and specify a name if you do not know a user's ID.
 *
 * @since 2.0.1
 * @deprecated 3.0.0 Use mn_set_current_user()
 * @see mn_set_current_user()
 *
 * @param int|null $id User ID.
 * @param string $name Optional. The user's username
 * @return MN_User returns mn_set_current_user()
 */
function set_current_user($id, $name = '') {
	_deprecated_function( __FUNCTION__, '3.0.0', 'mn_set_current_user()' );
	return mn_set_current_user($id, $name);
}
endif;

if ( !function_exists('get_currentuserinfo') ) :
/**
 * Populate global variables with information about the currently logged in user.
 *
 * @since 0.71
 * @deprecated 4.5.0 Use mn_get_current_user()
 * @see mn_get_current_user()
 *
 * @return bool|MN_User False on XMLRPC Request and invalid auth cookie, MN_User instance otherwise.
 */
function get_currentuserinfo() {
	_deprecated_function( __FUNCTION__, '4.5.0', 'mn_get_current_user()' );

	return _mn_get_current_user();
}
endif;

if ( !function_exists('get_userdatabylogin') ) :
/**
 * Retrieve user info by login name.
 *
 * @since 0.71
 * @deprecated 3.3.0 Use get_user_by()
 * @see get_user_by()
 *
 * @param string $user_login User's username
 * @return bool|object False on failure, User DB row object
 */
function get_userdatabylogin($user_login) {
	_deprecated_function( __FUNCTION__, '3.3.0', "get_user_by('login')" );
	return get_user_by('login', $user_login);
}
endif;

if ( !function_exists('get_user_by_email') ) :
/**
 * Retrieve user info by email.
 *
 * @since 2.5.0
 * @deprecated 3.3.0 Use get_user_by()
 * @see get_user_by()
 *
 * @param string $email User's email address
 * @return bool|object False on failure, User DB row object
 */
function get_user_by_email($email) {
	_deprecated_function( __FUNCTION__, '3.3.0', "get_user_by('email')" );
	return get_user_by('email', $email);
}
endif;

if ( !function_exists('mn_setcookie') ) :
/**
 * Sets a cookie for a user who just logged in. This function is deprecated.
 *
 * @since 1.5.0
 * @deprecated 2.5.0 Use mn_set_auth_cookie()
 * @see mn_set_auth_cookie()
 *
 * @param string $username The user's username
 * @param string $password Optional. The user's password
 * @param bool $already_md5 Optional. Whether the password has already been through MD5
 * @param string $home Optional. Will be used instead of COOKIEPATH if set
 * @param string $siteurl Optional. Will be used instead of SITECOOKIEPATH if set
 * @param bool $remember Optional. Remember that the user is logged in
 */
function mn_setcookie($username, $password = '', $already_md5 = false, $home = '', $siteurl = '', $remember = false) {
	_deprecated_function( __FUNCTION__, '2.5.0', 'mn_set_auth_cookie()' );
	$user = get_user_by('login', $username);
	mn_set_auth_cookie($user->ID, $remember);
}
else :
	_deprecated_function( 'mn_setcookie', '2.5.0', 'mn_set_auth_cookie()' );
endif;

if ( !function_exists('mn_clearcookie') ) :
/**
 * Clears the authentication cookie, logging the user out. This function is deprecated.
 *
 * @since 1.5.0
 * @deprecated 2.5.0 Use mn_clear_auth_cookie()
 * @see mn_clear_auth_cookie()
 */
function mn_clearcookie() {
	_deprecated_function( __FUNCTION__, '2.5.0', 'mn_clear_auth_cookie()' );
	mn_clear_auth_cookie();
}
else :
	_deprecated_function( 'mn_clearcookie', '2.5.0', 'mn_clear_auth_cookie()' );
endif;

if ( !function_exists('mn_get_cookie_login') ):
/**
 * Gets the user cookie login. This function is deprecated.
 *
 * This function is deprecated and should no longer be extended as it won't be
 * used anywhere in Mtaandao. Also, plugins shouldn't use it either.
 *
 * @since 2.0.3
 * @deprecated 2.5.0
 *
 * @return bool Always returns false
 */
function mn_get_cookie_login() {
	_deprecated_function( __FUNCTION__, '2.5.0' );
	return false;
}
else :
	_deprecated_function( 'mn_get_cookie_login', '2.5.0' );
endif;

if ( !function_exists('mn_login') ) :
/**
 * Checks a users login information and logs them in if it checks out. This function is deprecated.
 *
 * Use the global $error to get the reason why the login failed. If the username
 * is blank, no error will be set, so assume blank username on that case.
 *
 * Plugins extending this function should also provide the global $error and set
 * what the error is, so that those checking the global for why there was a
 * failure can utilize it later.
 *
 * @since 1.2.2
 * @deprecated 2.5.0 Use mn_signon()
 * @see mn_signon()
 *
 * @global string $error Error when false is returned
 *
 * @param string $username   User's username
 * @param string $password   User's password
 * @param string $deprecated Not used
 * @return bool False on login failure, true on successful check
 */
function mn_login($username, $password, $deprecated = '') {
	_deprecated_function( __FUNCTION__, '2.5.0', 'mn_signon()' );
	global $error;

	$user = mn_authenticate($username, $password);

	if ( ! is_mn_error($user) )
		return true;

	$error = $user->get_error_message();
	return false;
}
else :
	_deprecated_function( 'mn_login', '2.5.0', 'mn_signon()' );
endif;

/**
 * Mtaandao AtomPub API implementation.
 *
 * Originally stored in mn-app.php, and later res/class-mn-atom-server.php.
 * It is kept here in case a plugin directly referred to the class.
 *
 * @since 2.2.0
 * @deprecated 3.5.0
 *
 * @link https://mtaandao.co.ke/plugins/atom-publishing-protocol/
 */
if ( ! class_exists( 'mn_atom_server', false ) ) {
	class mn_atom_server {
		public function __call( $name, $arguments ) {
			_deprecated_function( __CLASS__ . '::' . $name, '3.5.0', 'the Atom Publishing Protocol plugin' );
		}

		public static function __callStatic( $name, $arguments ) {
			_deprecated_function( __CLASS__ . '::' . $name, '3.5.0', 'the Atom Publishing Protocol plugin' );
		}
	}
}
