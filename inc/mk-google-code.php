<script>

    function googlesignOut() {
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.signOut().then(function () {
            //console.log('User signed out.');
        });
    }

    /*
     * bind anchor class to trigger logout
     */
    $('.woocommerce-MyAccount-navigation-link--customer-logout,#wp-admin-bar-logout').click(function () {
        googlesignOut();
    });

    var googleUser = {};
    var startApp = function () {
        gapi.load('auth2', function () {
            // Retrieve the singleton for the GoogleAuth library and set up the client.
            auth2 = gapi.auth2.init({
                client_id: '<?php echo get_option('mk_google_client_id')?>',
                // Request scopes in addition to 'profile' and 'email'
                //scope: 'additional_scope'
            });
//            attachSignin(document.getElementById('mk_social_google_login_btn'));
            attachSignin(document.getElementsByClassName('mk_social_google_login_btn')[0]);
            attachSignin(document.getElementsByClassName('mk_social_google_login_btn')[1]);
        });
    };

    function attachSignin(element) {
        // //console.log(element.id);
        auth2.attachClickHandler(element, {},
            function (googleUser) {

                {
                    var profile = googleUser.getBasicProfile();
                    var id_token = googleUser.getAuthResponse().id_token;

                    // //console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
                    // //console.log('Name: ' + profile.getName());
                    // //console.log('Image URL: ' + profile.getImageUrl());
                    // //console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
                    // //console.log('Google Token: ' + id_token);

                    let formData = {};
                    formData = Object.assign(formData, {id: profile.getId()});
                    formData = Object.assign(formData, {email: profile.getEmail()});
                    formData = Object.assign(formData, {accessToken: id_token});
                    formData = Object.assign(formData, {name: profile.getName()});
                    formData = Object.assign(formData, {action: 'mk_do_login_by_google'});

                    <?php
                    if (!is_user_logged_in()) {
                    ?>
                    $.ajax({
                        type: "POST",
                        url: ajax_url,
                        data: formData,
                        dataType: "json",
                        beforeSend: function () {
                            mk_preload_on();
                        },
                        success: function (result) {
                            mk_preload_off();
                            if (result.succ) {
                                mk_preload_on(result.public_msg, "p_success");
                                window.location.reload();
                            } else {
                                mk_preload_on(result.public_msg, "p_danger");
                            }
                        }
                    });
                    <?php
                    }
                    ?>
                }
            }, function (error) {
                mk_preload_on(JSON.stringify(error, undefined, 2), "p_danger");
            });
    }
</script>