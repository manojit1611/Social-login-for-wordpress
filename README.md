# Social-login-for-wordpress

This plugin will help to integrate basic login | registration | google login | facebook login 


Note: This will auto manage the scrips for login based in user login or not and credentials setup in admin.
you have to just add html code given below.
   
## Social Login Integration
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



## Basic Login 
Steps: Just call your ajax from front end based on given params
<pre>
     * @params
     *  action : mk_login (this is ajax action)
     * user_login : (string) | required | username | email
     * user_password : (string) | required
     * remember : (int) | optional
</pre>


## Basic Registration 
Steps: Just call your ajax from front end based on given params
<pre>
     * action : mk_register_user (this is ajax action)
     * user_email : required | (string) The user email address.
     * user_pass : required | (string) The plain-text user password.
     * user_repeat_pass : required | (string) The plain-text user password.
     * first_name :required | (string) The user's first name.
     * last_name : optional | (string) The user's last name.
     * role : optional | (string) The user's role by default Subscriber.
</pre> 


