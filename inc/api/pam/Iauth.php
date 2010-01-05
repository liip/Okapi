<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Interface for authentication objects.
 */
interface api_pam_Iauth {
    /**
     * Login in with the given username and password. The authentication
     * object is responsible for handling the session state.
     *
     * @param string $user User name
     * @param string $pass Password
     * @param bool $persistent Whether to set a cookie for persistent login or not (aka "Remember me")
     * @return bool whether login succeeded or not
     */
    public function login($user, $pass, $persistent);

    /**
     * Force the login to the given user id without requiring to know
     * the password or anything
     *
     * @param int $id  User id
     * @param bool $persistent Whether to set a cookie for persistent login or not (aka "Remember me")
     * @return bool Return value of the authentication forceLogin method
     * @see api_pam_Iauth::forceLogin()
     */
    public function forceLogin($id, $persistent);

    /**
     * Log out the currently logged in user. The authentication object
     * is responsible for handling the session state.
     */
    public function logout();

    /**
     * Check if the user is currently logged in.
     * @return bool: True if the user is logged in.
     */
    public function checkAuth();

    /**
     * Return the user ID of the currently logged in user. This ID
     * is used for the permission checking.
     * @return mixed: User ID. Variable type depends on authentication
     *         class.
     */
    public function getUserId();

    /**
     * Return the user name of the currently logged in user.
     * @return string: User name
     */
    public function getUserName();

    /**
     * Gets the additional meta information about the currently logged in
     * user. Or just one value of the data if $attribute is passed
     *
     * @param string $attribute an optional attribute value
     * @return array|mixed Information key/value pair or only one value if
     * $attribute is given
     */
    public function getAuthData($attribute = null);

    /**
     * Sets a new password on an arbitrary user
     *
     * This is implemented by the auth since the auth can handle all the salting
     * and password hashing that way, upon registration just create the user with
     * an empty password and then use setPassword to alter it
     *
     * @param int $id user id to alter
     * @param string $password new user password
     * @return bool success
     */
    public function setPassword($userid, $password);
}
