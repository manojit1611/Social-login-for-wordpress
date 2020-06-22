<script>
    function fb_logout() {
        //console.log("Call facebook logout");
        FB.logout(function (response) {
            //https://developers.facebook.com/docs/reference/javascript/FB.logout/
            // document.getElementById("fb_logout").style.display = "none";
            // document.getElementById("mk_social_fb_login").style.display = "block";
            document.getElementsByClassName('mk_social_fb_login')[0].style.display = "none";
            document.getElementsByClassName('mk_social_fb_login')[0].style.display = "block";
        });
    }

    function mk_social_fb_login() {
        //console.log("Call facebook login");
        if (location.protocol !== 'https:') {
            mk_preload_on("Facebook login only works on https", "p_danger");
        } else {
            FB.login(statusChangeCallback, {scope: 'public_profile,email'});
        }

    }

    function checkLoginState() {
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });
    }

    window.fbAsyncInit = function () {
        FB.init({
            appId: '<?php echo get_option('mk_fb_app_id')?>',
            cookie: true,
            xfbml: true,
            version: 'v7.0'
        });
        FB.getLoginStatus(function (response) {
            if (response.status == "connected") {
                fb_logout();
            }
        });

        FB.AppEvents.logPageView();
    };
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function statusChangeCallback(res) {
        if (res.status != "connected") {
            fb_logout(); // to switch the btns
            mk_preload_on("Sorry, some error occurred please try again.");
            return;
        }
        // document.getElementById("fb_logout").style.display = "block";
        // document.getElementById("mk_social_fb_login").style.display = "none";
        document.getElementsByClassName('mk_social_fb_login')[0].style.display = "block";
        document.getElementsByClassName('mk_social_fb_login')[0].style.display = "none";
        FB.api(
            '/me',
            'GET',
            {"fields": "id,name,email"},
            function (response) {
                mk_preload_on();
                //console.log("object", Object.assign(response, {accessToken: res.authResponse.accessToken}));
                let formData = Object.assign(response, {accessToken: res.authResponse.accessToken});
                formData = Object.assign(formData, {action: 'mk_do_login_by_facebook'})

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
                //console.log("res", response);
                //console.log("accessToken", res.authResponse.accessToken);
            }
        );

    }
</script>