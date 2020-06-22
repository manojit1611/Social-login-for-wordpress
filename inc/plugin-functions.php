<?php

// google login action
add_action('wp_ajax_mk_do_login_by_google', 'mk_do_login_by_google_callback');
add_action('wp_ajax_nopriv_mk_do_login_by_google', 'mk_do_login_by_google_callback');

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
/*
     * func: nexgi_do_login_by_facebook_callback
     * desc:  Ajax action for do login by google social login
     */
add_action('wp_ajax_mk_do_login_by_facebook', 'mk_do_login_by_facebook_callback');
add_action('wp_ajax_nopriv_mk_do_login_by_facebook', 'mk_do_login_by_facebook_callback');
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

/*
     * fun: mk_get_user_by
     * desc: get single user
     *
     * You can pass filter like
     * Default key is : id
     * key : (string) (Required) The field to retrieve the user with. id | ID | slug | email | login.
     * value : (int|string) (Required) A value for $field. A user ID, slug, email address, or login name.
     */
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


/*
     * func:mk_set_current_user
     * desc: this will help to login | set current user
     */

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

if (!function_exists('mk_jsonThrow')) {
    function mk_jsonThrow($error = array(), $die = false)
    {
        echo json_encode($error);
        if ($die) {
            wp_die();
        }
    }
}


