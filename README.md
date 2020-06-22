# Social-login-for-wordpress

This is a basic plugin to manage facebook and google social login integration in wordpress.

Note: This will auto manage the scrips for login based in user login or not and credentials setup in admin.
you have to just add html code given below.
   
Steps:
1. Go to in wp-admin -> Social Login Settings and add your facebook app id and google client id 
2. Below is html code sample synced with events and function just copy and paste in front end code
```html
<div class="row">
    <div class="col-md-12">
        <button type="button" onclick="mk_social_fb_login()" class="fb_login btn btn-facebook btn-block">
            <i class="fa fa-facebook"></i> <?php echo __('Login with Facebook') ?>
        </button>
    </div>

    <div class="col-md-12">
        <button class="btn btn-google btn-block mk_social_google_login_btn">
            <i class="fa fa-google"></i> <?php echo __('Login with Google') ?>
        </button>
    </div>
</div>
```




