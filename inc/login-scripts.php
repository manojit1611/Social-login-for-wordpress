<style>
    .mk_preloader {
        position: fixed;
        right: 5px;
        top: 150px;
        color: currentColor;
        padding: 14px;
        z-index: 9999;
        min-width: 10%;
        max-width: 25%;
        font-size: 17px;
        border-radius: 4px;
        height: fit-content;
        background-color: #ffffff;
    }
</style>
<script src="https://apis.google.com/js/platform.js?onload=startApp" async defer></script>
<script>
    /*
    * jquery conflict
    */
    $ = jQuery.noConflict();

    function mk_preload_on(msg, msg_type_cls) {
        // mk_preload_off();
        msg = typeof msg != "undefined" ? msg : "Processing ...";
        msg_type_cls = typeof msg_type_cls != "undefined" ? msg_type_cls : "p_info";
        if (msg_type_cls == "p_success") {
            msg = "<span class='fa fa-check-circle'></span> " + msg;
        } else if (msg_type_cls == "p_danger") {
            msg = "<span class='fa fa-exclamation-circle'></span> " + msg;
        } else {
            msg = "<span class='fa fa-refresh gly-spin'></span> " + msg;
        }
        var gap = 20;
        var total = 0;
        $(".mk_preloader").each(function () {
            gap = gap + $(this).height() + 28 + 10;
            console.log("$(this).height()", total);
            console.log("gap", gap);
        });
        var $div = $("<div>", {class: "mk_preloader " + msg_type_cls})
            .html(msg + ' <span class="fa fa-close pull-right"></span></div>')
            .click(function () {
                $(this).remove();
            });
        $("body").append($div);
        $div.css({bottom: gap + "px"});
        $div.addClass("animate__animated animate__backInUp");
    }

    function mk_preload_off() {
        $(".mk_preloader").remove();
    }
</script>

<?php
// facebook login code
$mk_fb_app_id = get_option('mk_fb_app_id');
if ($mk_fb_app_id && !is_user_logged_in()) {
    include_once 'mk-fb-code.php';
}

// google login code
$mk_google_client_id = get_option('mk_google_client_id');
if ($mk_google_client_id && !is_user_logged_in()) {
    include_once 'mk-google-code.php';
}