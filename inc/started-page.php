
<style>
    .container{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 90vh;
        /*background-color: #F2ECEC;*/
        width: 100%;
        margin-top:0px;
    }
    .nestscale-logo{
/*            margin-bottom: 80px;*/
        padding-block: 45px;
        display: flex;
        background-color: #000;
        border-radius: 12px 12px 0 0;
    }
    .nestscale-logo img{
        height:60px;
        width: auto;
        margin-inline: auto;
    }
    .nestads-started{
        padding:28px;
        border-radius: 0 0 12px 12px;
        background-color: #fff;
        display: flex;
        flex-direction: column;
    }
    .title{
        font-size: 24px;
        line-height: 1.5;
        font-weight: 500;
        color:#000;
        margin:0;
    }
    .title span{
        color:#6D42E9;
        font-size: inherit;
        line-height: inherit;
        font-weight: 600;
    }
    .nestads-policy p{
        font-size: 16px;
        line-height: 1.5;
        font-weight: 400;
    }
    .policy-list{
        margin:15px;
        list-style: inside;
    }
    .policy{
        padding-block: 5px;
        font-size: 16px;
        line-height: 1.5;
    }
    .nestads-footer{
        margin-top:15px;
        display: flex;
        justify-content: center;
    }
    .btn{
        padding: 8px 30px;
        border-radius: 6px;
        font-size: 16px;
        line-height: 22px;
        font-weight: 600;
/*            margin-right:10px;*/
        text-decoration: none;
    }
    .btn-deny{
        border: 1px solid #000;
        background-color: inherit;
        color:#000;
    }
    .btn-allow{
        /*border:none;*/
        background-color: #000;
        color:#fff;
        border: 1px solid #000;
    }
    .btn-connect{
        background-color: #000;
        color: #fff;
        margin-inline: auto;
    }
    .nestads-footer p{
        margin-top: 22px;
        font-size: 14px;
        line-height: 22px;
        font-weight: 400;
        color: #000;
    }
    .switch-account{
        color:#6D42E9;
        font-weight: 500;
        text-decoration: none;
    }
</style>
<section class="container" >
    <div class="content">
        <div class="nestscale-logo">
            <img src="<?php esc_html_e( plugin_dir_url( __DIR__ ) ); ?>asset/img/Frame427318667.png" alt="Nestscale">
        </div>
        <div class="nestads-started">
            
            <h3 class="title">
                <?php if ( get_option( 'wpns_tt_consumer_key' ) ) : ?>
                    Manage TikTok pixels on your WooCommerce store
                <?php else : ?>
                    Connect NestScale to WooCommerce to get started
                <?php endif; ?>
            </h3>
            <div class="nestads-policy">
                <?php if ( get_option( 'wpns_tt_consumer_key' ) ) : ?>
                    <p>Easily manage all your TikTok Pixels and install new pixel in one-click.</p>
                <?php else : ?>
                    <p>TikTok Pixels by NestScale is ready to use! Connect to install TikTok Pixel on your WooCommerce store and start measuring your ad performance with automatically installed pixel events.</p>
                <?php endif; ?>
                
                
            </div>
            <div class="nestads-footer">
                <div class="btn-wrapper">
                    <!-- <a href="javascript:void(0)" class="btn btn-deny nestads-btn" data-access="deny">Deny</a>
                    <a href="javascript:void(0)" class="btn btn-allow nestads-btn" data-access="allow">Allow</a> -->
                <?php if ( get_option( 'wpns_tt_consumer_key' ) ): ?>
                    <a href="javascript:void(0)" class="btn btn-allow nestads-btn" id="go-to-app">Go to App</a>
                <?php else : ?>
                    <a href="javascript:void(0)" class="btn btn-connect" id="btn-connect">Connect</a>
                <?php endif; ?>
                </div>
                <!-- <p>You are logged in as Egan NestScale. <a href="#" class="switch-account">Not this account?</a></p> -->
            </div>
        </div>
    </div>
</section>
<script>
    let admin_url = '<?php esc_html_e( admin_url( 'admin-ajax.php' ) ); ?>',
    nonce = "<?php esc_html_e( wp_create_nonce( 'ajax-nonce' ) ); ?>";
    //index_url = `<?php //=admin_url('index.php')?>//`;
</script>
