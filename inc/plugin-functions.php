<?php

// Basic login

/*
     * fun:mk_login_callback
     * desc : ajax callback for do login
     *
     * @params
     *  action:mk_login (this is ajax action)
     * user_login : (string) | required | username | email
     * user_password : (string) | required
     * remember : (int) | optional
     *
     */
add_action('wp_ajax_mk_login', 'mk_login_callback');
add_action('wp_ajax_nopriv_mk_login', 'mk_login_callback');
if (!function_exists('mk_login_callback')) {
    function mk_login_callback()
    {
        try {
            // Validate Post method
            if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
                throw new Exception(__('Action must be post'));
            }
            // receive post request
            $formdata = $_POST;
            // variable define
            $errors = array();
            // When form data empty
            if (empty($formdata)) {
                $errors[] = __("Form data is empty");
            }
            // when user_login
            if (!isset($formdata['user_login']) || (isset($formdata['user_login']) && $formdata['user_login'] == "")) {
                $errors[] = __("User login is required");
            }
            // when user_password
            if (!isset($formdata['user_password']) || (isset($formdata['user_password']) && $formdata['user_password'] == "")) {
                $errors[] = __("User password is required");
            }

            // validate error
            if (!empty($errors)) {
                throw new Exception(__("Error while processing"));
            }

            // set credentials
            $creds = array(
                'user_login' => $formdata['user_login'],
                'user_password' => $formdata['user_password'],
            );

            // when remember
            if (isset($formdata['remember']) && $formdata['remember'] != "") {
                $creds['remember'] = true;
            }

            // process login
            $user = mk_login($creds);
            // when wp error
            if (is_wp_error($user)) {

                // by default wp send html string in some cases but we need error text only
                // then check when password html
                // Object to array
                $temp_wp_error = mk_objectToArray($user);
                //check isset keys
                if (isset($temp_wp_error['errors']) && !empty($temp_wp_error['errors'])) {
                    if (isset($temp_wp_error['errors']['incorrect_password']) && !empty($temp_wp_error['errors']['incorrect_password'])) {
                        $errors[] = __("Incorrect password");
                    } else {
                        $errors[] = __($user->get_error_message());
                    }
                }
                // Throw error
                throw new Exception("Error while processing");
            } else {
                // object to array
                $single_user_data = mk_objectToArray($user);
                // parse user data for result
                $single_user_data = mk_parse_api_res($single_user_data, true);
            }

            // Parse json response
            mk_jsonThrow(
                array(
                    'succ' => true,
                    'msg' => __("User login successfully"),
                    'data' => $single_user_data,
                    "errors" => $errors
                ),
                true
            );
        } catch (Exception $exception) {
            // Parse error response
            mk_jsonThrow(
                $errors = array(
                    "succ" => false,
                    "msg" => $exception->getMessage(),
                    'data' => array(),
                    "errors" => $errors
                ),
                true
            );
        }

    }
}


// new user registration

/*
     * func : mk_wp_insert_user_callback
     * desc: Ajax callback for create new user in system

     * user_email : required | (string) The user email address.
     * user_pass : required | (string) The plain-text user password.
     * user_repeat_pass : required | (string) The plain-text user password.
     * first_name :required | (string) The user's first name.
     * last_name : optional | (string) The user's last name.
     * role : optional | (string) The user's role by default Subscriber.
     * ID : (int) User ID. If supplied, the user will be updated.
     *
     * Dummy array data
     *
     * $formdata['user_email']   = "newuser@gmail.com";
     * $formdata['user_pass'] = "admin@123";
     * $formdata['user_repeat_pass'] = "admin@123";
     *  $formdata['first_name'] = "New user ";
     */

add_action('wp_ajax_mk_register_user', 'mk_wp_insert_user_callback');
add_action('wp_ajax_nopriv_mk_register_user', 'mk_wp_insert_user_callback');

if (!function_exists('mk_wp_insert_user_callback')) {
    function mk_wp_insert_user_callback()
    {
        try {

            // Validate Post method
            if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
                throw new Exception(__('Action must be post'));
            }
            // receive post request
            $formdata = $_POST;
            // Variable define
            $errors = array();
            $single_user_data = array();

            // validate
            if (empty($formdata)) {
                $errors[] = __("Form data is empty");
            }

            // Validate
            if (!isset($formdata['first_name']) || (isset($formdata['first_name']) && $formdata['first_name'] == "")) {
                $errors[] = __("User's name is required ");
            }

            // when user_email
            if (!isset($formdata['user_email']) || (isset($formdata['user_email']) && $formdata['user_email'] == "")) {
                $errors[] = __("User's email is required");
            }
            // when first_name
            if (isset($formdata['first_name']) && $formdata['first_name'] != "") {
                if (preg_match("/^[a-zA-Z \s]+$/", $formdata['first_name']) != 1) {
                    $errors[] = __('Only alphabets, space and underscore allowed in  name');
                }
            }
            // when last_name
            if (isset($formdata['last_name']) && $formdata['last_name'] != "") {
                if (preg_match("/^[a-zA-Z \s]+$/", $formdata['last_name']) != 1) {
                    $errors[] = __("Only alphabets, space and underscore allowed in  name");
                }
            }

            // when password
            if (!isset($formdata['user_pass']) || (isset($formdata['user_pass']) && $formdata['user_pass'] == "")) {
                $errors[] = __("Password is required");
            }
            // when user_repeat_pass
            if (!isset($formdata['user_repeat_pass']) || (isset($formdata['user_repeat_pass']) && $formdata['user_repeat_pass'] == "")) {
                $errors[] = __("Repeat password is required");
            }

            // when user_pass
            if (isset($formdata['user_pass']) && $formdata['user_repeat_pass'] != "") {
                if ($formdata['user_pass'] != $formdata['user_repeat_pass']) {
                    $errors[] = __("Both passwords must be same");
                }
            }


            // validate error
            if (!empty($errors)) {
                throw new Exception(__("Error while processing"));
            }

            // pre fill data for more details
            // set username as user login default
            $formdata['user_login'] = $formdata['user_email'];
            // set nickname
            $formdata['nickname'] = $formdata['first_name'];
            // set last_name to nickname
            if (isset($formdata['last_name']) && $formdata['last_name'] != "") {
                $formdata['nickname'] = $formdata['nickname'] . " " . $formdata['last_name'];
            }

            // process insert user
            $user_id = wp_insert_user($formdata);
            // On success.
            if (!is_wp_error($user_id)) {
                // get user data
                $userFilter = array('value' => $user_id);
                $single_user_data = mk_get_user_by($userFilter);

                // When user data is found
                if (!empty($single_user_data)) {
                    $single_user_data = mk_objectToArray($single_user_data);
                    $single_user_data = mk_parse_api_res($single_user_data, true);
                }

                // check if new user then send mail
                if (!isset($formdata['ID']) || (isset($formdata['ID']) && $formdata['ID'] == "")) {
                    // send new user notification
                    wp_send_new_user_notifications($user_id, 'user');
                }


                // process for login
                $is_login = mk_set_current_user($single_user_data['ID'], $single_user_data['user_login']);

                if ($is_login) {
                    // Parse json response
                    // Parse json response
                    mk_jsonThrow(
                        array(
                            'succ' => true,
                            'msg' => "New user created || updated successfully",
                            'data' => $single_user_data,
                            "errors" => $errors
                        ),
                        true
                    );
                } else {
                    $errors[] = __("Error while processing");
                    throw new Exception(__("Error while processing"));
                }
            } else {
                // get wp error
                if (is_wp_error($user_id)) {
                    // throw error
                    $errors[] = $user_id->get_error_message();
                    throw new  Exception(__("Error while processing"));
                }
            }
        } catch (Exception $exception) {
            // Parse error response
            mk_jsonThrow(
                $errors = array(
                    "succ" => false,
                    "msg" => $exception->getMessage(),
                    'data' => array(),
                    "errors" => $errors
                ),
                true
            );
        }
    }
}


// google login action
add_action('wp_ajax_mk_do_login_by_google', 'mk_do_login_by_google_callback');
add_action('wp_ajax_nopriv_mk_do_login_by_google', 'mk_do_login_by_google_callback');

if (!function_exists('mk_do_login_by_google_callback')) {
    function mk_do_login_by_google_callback()
    {
        try {

            // Validate Post method
            if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
                throw new Exception(__('Action must be post'));
            }
            // receive post request
            $formdata = $_POST;
            // Variable define
            $userData = array();
            $errors = array();
            // validate form data
            if (empty($formdata)) {
                $errors[] = __("Form data is empty");
            }
            // validate
            // when name
            if (!isset($formdata['name']) || (isset($formdata['name']) && $formdata['name'] == "")) {
                $errors[] = __("Name is required");
            }

            // when id
            if (!isset($formdata['id']) || (isset($formdata['id']) && $formdata['id'] == "")) {
                $errors[] = __("Google user login is required");
            }

            // accessToken
            if (!isset($formdata['accessToken']) || (isset($formdata['accessToken']) && $formdata['accessToken'] == "")) {
                $errors[] = __("Access token is required");
            }

            // validate error
            if (!empty($errors)) {
                // throw error
                throw new Exception(__("Error while processing"));
            }


            // curl
            $res = wp_remote_get("https://oauth2.googleapis.com/tokeninfo?id_token={$formdata['accessToken']}");
            // get data response
            $body = wp_remote_retrieve_body($res);
            // json decode
            $res = json_decode($body, true);


            // validate
            if (!isset($res['sub'])) {
                // assign error
                $errors[] = __("Invalid token");
                throw new Exception(__("Error while processing"));
            }


            // if valid token
            /*
             *  stdClass Object
             {
             "iss": "accounts.google.com",
             "azp": "869010987460-ajfpuep0l841gfj871ef8sfgob5hv65t.apps.googleusercontent.com",
             "aud": "869010987460-ajfpuep0l841gfj871ef8sfgob5hv65t.apps.googleusercontent.com",
             "sub": "107263763471016189104", (id)
             "email": "kumaranup594@gmail.com(opens in new tab)",
             "email_verified": "true",
             "at_hash": "dCZIq3vOTRp2FmLXO3GlpQ",
             "name": "Anup kumar",
             "picture": "https://lh3.googleusercontent.com/a-/AOh14GgsYaOG_6RgbAFxD7DONY0xmwpePJbooDrC0Km4xQ=s96-c",
             "given_name": "Anup",
             "family_name": "kumar",
             "locale": "en",
             "iat": "1587269635",
             "exp": "1587273235",
             "jti": "9dbca43bd463e6b105eb4245ef73b1d66ec8d0ab",
             "alg": "RS256",
             "kid": "f9d97b4cae90bcd76aeb20026f6b770cac221783",
             "typ": "JWT"
             }
            */

            if ($res['sub'] == $formdata['id'] && $res['name'] == $formdata['name'] &&
                $res['email'] == $formdata['email']) {

                // checking user is registered or not
                // here I m not sending activate status - because deactive user will
                // will be activated after this.

                // set user filter
                $user_filter = array(
                    'key' => 'email',
                    'value' => $formdata['email']
                );

                // check id old user
                $userData = mk_get_user_by($user_filter);

                // if old user
                if (!empty($userData)) {
                    $userData = mk_objectToArray($userData);
                    $userData = mk_parse_api_res($userData, true);
                    // process for login
                    $is_login = mk_set_current_user($userData['ID'], $userData['user_login']);
                    // if login true
                    if ($is_login) {
                        // Parse json response
                        mk_jsonThrow(
                            array(
                                'succ' => true,
                                'msg' => "User login successfully",
                                'data' => $userData,
                            ),
                            true
                        );
                        wp_die();
                    } else {
                        $errors[] = __("Error while processing");
                        throw new Exception(__("Error while processing"));
                    }
                } else {

                    // process for register and do login
                    $user_id = wp_create_user($formdata['email'], wp_generate_password(), $formdata['email']);
                    // On success.
                    if (!is_wp_error($user_id)) {
                        // get user data
                        $userFilter = array('value' => $user_id);
                        $userData = mk_get_user_by($userFilter);
                        // When user data is found
                        $userData = mk_objectToArray($userData);
                        $userData = mk_parse_api_res($userData, true);
                        // check if new user then send mail
                        if (!isset($formdata['ID']) || (isset($formdata['ID']) && $formdata['ID'] == "")) {
                            // send new user notification
                            wp_send_new_user_notifications($user_id, 'user');
                        }

                        // Parse json response
                        mk_jsonThrow(
                            array(
                                'succ' => true,
                                'msg' => "User login successfully",
                                'data' => $userData,
                                "errors" => $errors
                            ),
                            true
                        );
                        wp_die();
                    } else {
                        // get wp error
                        if (is_wp_error($user_id)) {
                            // throw error
                            $errors[] = $user_id->get_error_message();
                            throw new  Exception("Error while processing");
                        }
                    }
                }

            }

        } catch (Exception $exception) {
            // Parse error response
            mk_jsonThrow(
                $errors = array(
                    "succ" => false,
                    "msg" => $exception->getMessage(),
                    'data' => array(),
                    'errors' => $errors
                ),
                true
            );
        }
    }
}


/*
     * func: nexgi_do_login_by_facebook_callback
     * desc:  Ajax action for do login by google social login
     */

// facebook login action
add_action('wp_ajax_mk_do_login_by_facebook', 'mk_do_login_by_facebook_callback');
add_action('wp_ajax_nopriv_mk_do_login_by_facebook', 'mk_do_login_by_facebook_callback');
if (!function_exists('mk_do_login_by_facebook_callback')) {
    function mk_do_login_by_facebook_callback()
    {
        try {

            // Validate Post method
            if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
                throw new Exception(__('Action must be post'));
            }

            // receive post request
            $formdata = $_POST;
            // Variable define
            $userData = array();
            $errors = array();
            // validate form data
            if (empty($formdata)) {
                $errors[] = __("Form data is empty");
            }
            // validate
            // when name
            if (!isset($formdata['name']) || (isset($formdata['name']) && $formdata['name'] == "")) {
                $errors[] = __("Name is required");
            }

            // when id
            if (!isset($formdata['id']) || (isset($formdata['id']) && $formdata['id'] == "")) {
                $errors[] = __("Facebook id is required");
            }

            // accessToken
            if (!isset($formdata['accessToken']) || (isset($formdata['accessToken']) && $formdata['accessToken'] == "")) {
                $errors[] = __("Access token is required");
            }

            // validate error
            if (!empty($errors)) {
                // throw error
                throw new Exception(__("Error while processing"));
            }

            // curl
            $res = wp_remote_get("https://graph.facebook.com/me?fields=id,name,email&access_token={$formdata['accessToken']}");
            // get data response
            $body = wp_remote_retrieve_body($res);
            // json decode
            $res = json_decode($body, true);
            // validate
            if (!isset($res['id'])) {
                // assign error
                $errors[] = __("Invalid token");
                throw new Exception(__("Error while processing"));
            }


            // if valid token
            /*
             * stdClass Object
              (
              [id] => 2991083347651340
              [name] => manoj Kumar
              [email] => manojit1611@gmail.com (opens in new tab)
              )
             */

            if ($res['id'] == $formdata['id'] && $res['name'] == $formdata['name'] &&
                $res['email'] == $formdata['email']) {

                // checking user is registered or not
                // here I m not sending activate status - because deactive user will
                // will be activated after this.

                // set user filter
                $user_filter = array(
                    'key' => 'email',
                    'value' => $formdata['email']
                );

                // check id old user
                $userData = mk_get_user_by($user_filter);
                // if old user
                if (!empty($userData)) {
                    $userData = mk_objectToArray($userData);
                    $userData = mk_parse_api_res($userData, true);
                    // process for login
                    $is_login = mk_set_current_user($userData['ID'], $userData['user_login']);
                    // if login true
                    if ($is_login) {
                        // Parse json response
                        mk_jsonThrow(
                            array(
                                'succ' => true,
                                'msg' => "User login successfully",
                                'data' => $userData,
                            ),
                            true
                        );
                    } else {
                        $errors[] = __("Error while processing");
                        throw new Exception(__("Error while processing"));
                    }
                } else {
                    // process for register and do login
                    $user_id = wp_create_user($formdata['email'], wp_generate_password(), $formdata['email']);
                    // On success.
                    if (!is_wp_error($user_id)) {
                        // get user data
                        $userFilter = array('value' => $user_id);
                        $userData = mk_get_user_by($userFilter);
                        // When user data is found
                        $userData = mk_objectToArray($userData);
                        $userData = mk_parse_api_res($userData, true);
                        // check if new user then send mail
                        if (!isset($formdata['ID']) || (isset($formdata['ID']) && $formdata['ID'] == "")) {
                            // send new user notification
                            wp_send_new_user_notifications($user_id, 'user');
                        }

                        // Parse json response
                        mk_jsonThrow(
                            array(
                                'succ' => true,
                                'msg' => "User login successfully",
                                'data' => $userData,
                                "errors" => $errors
                            ),
                            true
                        );
                    } else {
                        // get wp error
                        if (is_wp_error($user_id)) {
                            // throw error
                            $errors[] = $user_id->get_error_message();
                            throw new  Exception("Error while processing");
                        }
                    }
                }

            }

        } catch (Exception $exception) {
            // Parse error response
            mk_jsonThrow(
                $errors = array(
                    "succ" => false,
                    "msg" => $exception->getMessage(),
                    'data' => array(),
                    'errors' => $errors
                ),
                true
            );
        }
    }
}


/*
     * fun: mk_get_user_by
     * desc: get single user
     *
     * You can pass filter like
     * Default key is : id
     * key : (string) (Required) The field to retrieve the user with. id | ID | slug | email | login.
     * value : (int|string) (Required) A value for $field. A user ID, slug, email address, or login name.
     */
if (!function_exists('mk_get_user_by')) {
    function mk_get_user_by($filters = array())
    {
        // Validate $field else default is id
        $field = 'ID';
        $value = "";
        // Check and set filters

        // When key
        if (isset($filters['key']) && $filters['key'] != "") {
            $field = $filters['key'];
        }

        // When key
        if (isset($filters['value']) && $filters['value'] != "") {
            $value = $filters['value'];
        } else {
            return array();
        }

        $single_user_data = get_user_by($field, $value);
        return $single_user_data;
    }
}


/*
 * fun:mk_objectToArray
 * Desc: object to array convert
 * take object  and return array
 */
if (!function_exists('mk_objectToArray')) {
    function mk_objectToArray($object)
    {
        $arrayData = json_decode(json_encode($object), true);
        return $arrayData;
    }
}

/*
     * fun:mk_parse_api_res
     * desc: This will parse the format for result return
     * Will take array and ten filter that then return
     */
if (!function_exists('mk_parse_api_res')) {
    function mk_parse_api_res($data = array(), $isrow = false)
    {
        // Validate
        if (empty($data)) {
            return array();
        }
        $final_data = array();
        if (!$isrow) {
            foreach ($data as $index => $row) {
                // Check key and fill in final data
                if (isset($row['data']) && !empty($row['data'])) {
                    unset($row['data']['user_activation_key']);
                    unset($row['data']['user_pass']);
                    $final_data[] = $row['data'];
                }
            }
        } else {
            // Check key and fill in final data
            if (isset($data['data']) && !empty($data['data'])) {
                unset($data['data']['user_activation_key']);
                unset($data['data']['user_pass']);
                $final_data = $data['data'];
            }
        }

        // Return final data
        return $final_data;

    }
}


/*
     * func:mk_set_current_user
     * desc: this will help to login | set current user
     */

if (!function_exists('mk_set_current_user')) {
    function mk_set_current_user($user_id = 0, $user_login = '')
    {
        // validate id
        if (!$user_id) {
            return false;
        }

        // validate user login if blank
        if ($user_login == "") {
            return false;
        }


        /*
         * first delete session then process
         */
        wp_destroy_current_session();

        // set current user as logged
        wp_set_current_user($user_id, $user_login);


        // set auth cookies
        wp_set_auth_cookie($user_id);


        // fire action for do login by current user login
//        do_action('wp_login', $user_login);


        // return true
        return true;
    }
}
if (!function_exists('mk_jsonThrow')) {
    function mk_jsonThrow($error = array(), $die = false)
    {
        echo json_encode($error);
        if ($die) {
            wp_die();
        }
    }
}


/*
     * func:mk_login
     * desc: will do login and return user array on success else error array
     */
if (!function_exists('mk_login')) {
    function mk_login($creds = array())
    {
        // process for sign on
        $user = wp_signon($creds);
        // return result
        return $user;
    }
}


