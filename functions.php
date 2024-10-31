<?php
/**

* @package Tiktok Pixel

*/
/*
 * Plugin Name: NestScale TikTok Pixel & TikTok Ads
 * Plugin URI: https://woocommerce.nestscale.com/
 * Description: Install as many TikTok pixels as you want in one click. Automatic event triggers & precise data tracking with no technical skills needed.
 * Version: 1.0.10
 * Author: NestScale
 * Author URI: https://nestscale.com/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.0
 * Requires PHP: 5.4
 * WC requires at least: 2.0
 * WC tested up to: 5.5.2
 * Text Domain: wpns-tiktok-pixel
*/

define( 'WPNS_TT_GO_TO_APP_URL', 'https://woocommerce.nestscale.com/woocommerce/main' );
define( 'WPNS_TT_RETURN_URL', 'https://woocommerce.nestscale.com/woocommerce/finalize' );
define( 'WPNS_TT_CALLBACK_URL', 'https://woocommerce.nestscale.com/woocommerce/auth' );


function wpns_tt_prefix_append_plugin_meta_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
    if ( strpos( $plugin_file_name, basename(__FILE__) ) ) {

        $links_array[] = '<a href="'.admin_url( '?page=ns-tiktok-pixel' ).'">Settings</a>';
    }

    return $links_array;
}

add_filter( 'plugin_row_meta', 'wpns_tt_prefix_append_plugin_meta_links', 10, 4 );
/**
 * Enqueue admin script
*/
function wpns_tt_enqueue_plugin_scripts() {

    $current_screen = get_current_screen();
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'ns-tiktok-pixel' ){

        wp_enqueue_script( 'request-access', plugin_dir_url( __FILE__ ) . 'asset/js/request_access.js', '', '', true );

    }

}
add_action( 'admin_enqueue_scripts', 'wpns_tt_enqueue_plugin_scripts' );
/**
 * Register normal style and script
 */

/**
 * Register a custom menu page.
 */
function wpnd_tt_register_my_custom_menu_page(){
    add_menu_page(
        __( 'TikTok Pixel', 'ns-tiktok-pixel' ),
        'TikTok pixel',
        'manage_options',
        'ns-tiktok-pixel',
        'wpns_tt_custom_menu_page',
        plugin_dir_url( __FILE__ ).'asset/img/logo.png',
        26
    );
}
add_action( 'admin_menu', 'wpnd_tt_register_my_custom_menu_page' );

/**
 * Display a custom menu page
 */
function wpns_tt_custom_menu_page() {
    if ( ! get_option( 'wpns_tt_allow_nestads_api' ) ) {
        add_option( 'wpns_tt_allow_nestads_api', false);
    }
    require_once(  __DIR__ . '/inc/started-page.php' );
}
/**
 * allow/deny nestads access action
*/
add_action( 'wp_ajax_wpns_tt_woo_allow_nestads' , 'wpns_tt_woo_allow_nestads' );
function wpns_tt_woo_allow_nestads(){
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
        wp_send_json_error( 'Error' );
        die();
    } else {
        $user_info = wp_get_current_user();
        $user_id = $user_info->data->ID;
        $name = $user_info->data->user_nicename;
        $email = $user_info->data->user_email;
        
        $redirect_url = WPNS_TT_CALLBACK_URL;

        $store_url = get_permalink( wc_get_page_id( 'shop' ) );
        $new_password    = wp_generate_password( 24, false );
        if ( ! get_option( 'wpns_tt_pwd' ) ){
            add_option( 'wpns_tt_pwd', base64_encode($new_password) );
        }else{
            update_option( 'wpns_tt_pwd', base64_encode($new_password) );
        }

        $body = [
            'store_url'         => $store_url,
            'email'             => $email,
            'user_id'           => $user_id,
            'name'              => $name,
            'key'               => $new_password
        ];

        $redirect_url = $redirect_url.'?'.http_build_query( $body );
        wp_send_json_success( array(
            'status' => 'success',
            'redirect_url' => $redirect_url
        ) );
        die();
    }
}

add_action( 'wp_ajax_wpns_tt_woo_go_to_app' , 'wpns_tt_woo_go_to_app' );
function wpns_tt_woo_go_to_app(){
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
        wp_send_json_error( 'Error' );
        die();
    } else {
        $redirect_url = WPNS_TT_GO_TO_APP_URL;
        wp_send_json_success( array(
            'status' => 'success',
            'redirect_url' => $redirect_url
        ) );
    }
}


add_action( 'rest_api_init', function () {
    register_rest_route( 'nestscale/v1', '/authentication/(?P<id>\d+)/data', array(
        'methods' => 'POST',
        'callback' => 'wpns_tt_authentication',
        'permission_callback' =>  '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
    register_rest_route( 'nestscale/v1', '/pixels/(?P<id>\d+)/data', array(
        'methods' => 'POST',
        'callback' => 'wpns_tt_save_pixels',
        'permission_callback' =>  '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
} );
/**authenticaion and save pixels*/
function wpns_tt_authentication( $request ){

    $id = $request['id'];
    $user_data = get_userdata( $id );
    if ( $user_data === false ){
        return new WP_Error( __( 'Wrong id!', 'ns-tiktok-pixel' ), array( 'status' => 500 ) );
    } else {
        $ns_consumer_key = $request['ns_consumer_key'];
        
        $key = $request['key'];
        $key = base64_encode($key);
        if ( $key && $key == get_option( 'wpns_tt_pwd' ) ){
            if ( $ns_consumer_key ){
                if ( !get_option( 'wpns_tt_consumer_key' ) ){
                    add_option( 'wpns_tt_consumer_key', $ns_consumer_key );
                    return new WP_REST_Response( true, 200 );
                } else {
                    update_option( 'wpns_tt_consumer_key', $ns_consumer_key );
                    return new WP_REST_Response( 'Update ck.', 200 );
                }
            } else {
                return new WP_Error( __( 'Wrong ck key!', 'ns-tiktok-pixel' ), array( 'status' => 500 ) );
            }
            
        } else {
            return new WP_Error( __( 'Wrong key!', 'ns-tiktok-pixel' ), array( 'status' => 500 ) );
        }
    }
}
function wpns_tt_save_pixels( $request ){

    $ns_ck = get_option( 'wpns_tt_consumer_key' );
    $ns_post_ck = $request['ns_consumer_key'];
    if ( $ns_post_ck == $ns_ck ){
        $pixels = substr( $request['pixels'], 1, -1 );
        $pixels = explode( ',', $pixels );
        if ( ! get_option( 'wpns_tt_pixel_codes')){
            add_option( 'wpns_tt_pixel_codes', $pixels );
        } else {
            delete_option( 'wpns_tt_pixel_codes');
            add_option( 'wpns_tt_pixel_codes', $pixels );
        }
        return new WP_REST_Response( true, 200 );
    }else{
        return new WP_Error( __( 'Wrong key!', 'ns-tiktok-pixel' ), array( 'status' => 500 ) );
    }
}

add_action( 'wp_footer', 'wpns_tt_tiktok_pixel_script_footer' );
function wpns_tt_tiktok_pixel_script_footer(){
    $code_arr = get_option( 'wpns_tt_pixel_codes' );
    if ( $code_arr && !empty( $code_arr ) ){
        $code_list = array();
        foreach ( $code_arr as $c ){
            $code_list[] = substr( sanitize_text_field( $c ), 1, -1 );
        }
        $code_list = json_encode( $code_list );
        ?>
        <!-- Pixels zone -->
        <script>
            let codes = <?php echo esc_html( $code_list );?>;
            if ( codes.length > 0 ){
                ! function( w, d, t ) {
                    w.TiktokAnalyticsObject = t;
                    var ttq = w[t] = w[t] || [];
                    ttq.methods = ["page", "track", "identify", "instances", "debug", "on", "off", "once", "ready", "alias", "group", "enableCookie", "disableCookie"], ttq.setAndDefer = function( t, e ) { t[e] = function() { t.push( [e].concat( Array.prototype.slice.call( arguments, 0 ) ) ) } };
                    for ( var i = 0; i < ttq.methods.length; i++ ) ttq.setAndDefer( ttq, ttq.methods[i] );
                    ttq.instance = function( t ) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer( e, ttq.methods[n] ); return e }, ttq.load = function( e, n ) {
                        var i = "https://analytics.tiktok.com/i18n/pixel/events.js";
                        ttq._i = ttq._i || {}, ttq._i[e] = [], ttq._i[e]._u = i, ttq._t = ttq._t || {}, ttq._t[e] = +new Date, ttq._o = ttq._o || {}, ttq._o[e] = n || {};
                        var o = document.createElement( "script" );
                        o.type = "text/javascript", o.async = !0, o.src = i + "?sdkid=" + e + "&lib=" + t;
                        var a = document.getElementsByTagName( "script" )[0];
                        a.parentNode.insertBefore( o, a );
                    };
                    <?php if ( is_product() ):?>
                    for ( let i=0; i< codes.length; i++ ){
                        ttq.load( codes[i] );
                        ttq.instance( codes[i] ).track( 'ViewContent' );
                    }
                    ttq.page();
                    jQuery( document ).on( 'click','button[name="add-to-cart"]',function( e ){
                       
                       for ( let i=0; i< codes.length; i++ ){
                            ttq.instance( codes[i] ).track( 'AddToCart' );
                        }
                    } );
                    <?php else: ?>
                    for ( let i=0; i< codes.length; i++ ){
                        ttq.load( codes[i] );
                    }
                    ttq.page();
                    <?php endif; ?>
                    
                    jQuery( 'body' ).on( 'added_to_cart', function( e, fragments, cart_hash, this_button ) {
                        for ( let i=0; i < codes.length; i++ ){
                            ttq.instance( codes[i] ).track('AddToCart');
                        }
                    } );
                    jQuery( 'body' ).on( 'init_checkout', function(){
                        for ( let i=0; i < codes.length; i++ ){
                            
                            ttq.instance( codes[i] ).track( 'InitiateCheckout' );
                        }
                    });
                    jQuery( 'body' ).on( 'adding_to_cart', function( this_button, data ) {
                        for ( let i = 0; i < codes.length; i++ ){
                            ttq.instance( codes[i] ).track( 'AddToCart' );
                        }
                    } );
                    // ttq.load('YOUR PIXEL ID WILL BE LOCATED HERE');
                    // ttq.page();
                }( window, document, 'ttq' );
            }
        </script>
        <?php
    }
}

add_action( 'admin_enqueue_scripts', 'admin_style_tiktok_pixel' );
function admin_style_tiktok_pixel(){
    ?>
    <style>
        #adminmenu #toplevel_page_ns-tiktok-pixel .wp-menu-image img{
            width: 18px;
        }
        #adminmenu .current#toplevel_page_ns-tiktok-pixel .wp-menu-image img{
            opacity: 1;
        }
    </style>
    <?php
}
