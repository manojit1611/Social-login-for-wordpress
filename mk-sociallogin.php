<?php
/*
Plugin Name: Wordpress Social Login
description: Social user login for wordpress
This plugin will help you to do login and register by facebook and google
Version: 1.0
Author: Manoj Kumar
*/
if (!defined('MK_SOCIAL_OPTIONS_SETTING_PAGE')) {
    define('MK_SOCIAL_OPTIONS_SETTING_PAGE', admin_url('admin.php?page=mk-social-login-settings'));
}
require_once 'inc/plugin-functions.php';

// set header scripts
function mk_socialLogin_header_assets()
{
    include_once 'inc/login-header-scripts.php';
}

add_action('wp_head', 'mk_socialLogin_header_assets');


// set footer scrips
function mk_socialLogin_footer_assets()
{
// manage footer scripts files
    include_once 'inc/login-scripts.php';
}

add_action('wp_footer', 'mk_socialLogin_footer_assets');


/**
 * top level menu
 */
function mk_socialLogin_admin_menu_callback()
{
    // add top level menu page
    add_menu_page(
        'Social Login Settings',
        'Social Login Settings',
        'manage_options',
        'mk-social-login-settings',
        'mk_social_login_settings_callback'
    );
}

/**
 * register our wporg_options_page to the admin_menu action hook
 */
add_action('admin_menu', 'mk_socialLogin_admin_menu_callback');

/**
 * top level menu:
 * callback functions
 */
function mk_social_login_settings_callback()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    // validate post and data
    if (!empty($_POST)) {
        // validate if mk_save_social_login_setting
        if (isset($_POST['mk_save_social_login_setting']) && $_POST['mk_save_social_login_setting']) {
            // Process for facebook option
            if (isset($_POST['mk_fb_app_id']) && !empty($_POST['mk_fb_app_id'])) {
                $fb_app_id = get_option('mk_fb_app_id');
                if (!$fb_app_id) {
                    add_option('mk_fb_app_id', $_POST['mk_fb_app_id']);
                } else {
                    update_option('mk_fb_app_id', $_POST['mk_fb_app_id']);
                }
            } else {
                delete_option('mk_fb_app_id');
            }

            // process for google option

            if (isset($_POST['mk_google_client_id']) && !empty($_POST['mk_google_client_id'])) {
                $mk_google_client_id = get_option('mk_google_client_id');
                if (!$mk_google_client_id) {
                    add_option('mk_google_client_id', $_POST['mk_google_client_id']);
                } else {
                    update_option('mk_google_client_id', $_POST['mk_google_client_id']);
                }
            } else {
                delete_option('mk_google_client_id');
            }

            // set msg

            add_settings_error('mk_social_login_admin_message', 'mk_social_login_admin_message', __('Settings Saved'), 'updated');
            settings_errors('mk_social_login_admin_message');

        }
    }


    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="<?php echo MK_SOCIAL_OPTIONS_SETTING_PAGE ?>" method="post">

            <div class="row">
                <div class="col-md-12">
                    <input type="hidden" name="mk_save_social_login_setting" value="1"/>
                    <label>
                        FACEBOOK APP ID :
                    </label>
                    <input value="<?php echo get_option('mk_fb_app_id')?>" type="text" class="form-control" name="mk_fb_app_id">
                </div>

                <div class="col-md-12">
                    <label>
                        GOOGLE CLIENT ID :
                    </label>
                    <input type="text" value="<?php echo get_option('mk_google_client_id')?>" class="form-control" name=mk_google_client_id>
                </div>
            </div>

            <?php
            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}
