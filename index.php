<?php
/**
 * @package   wp-betteredit
 * @author    Stefan Böttcher
 *
 * @wordpress-plugin
 * Plugin Name: Better Edit
 * Description: makes editing posts a little easier
 * Version:     0.0.1
 * Author:      Stefan Böttcher
 * Author URI:  http://wp-hotline.com
 */

// If this file is called directly, abort.
if (!defined("WPINC")) {
	die;
}

add_action('admin_footer', 'betteredit_admin_footer');
add_action( 'wp_ajax_editpost', 'betteredit_save_post' );
add_action( 'admin_enqueue_scripts', 'betteredit_admin_enqueue' );

function betteredit_admin_footer( $hook ) {

    echo '<script>';
    echo 'jQuery(document).ready(function($) {

			//$("#post-preview.preview.button").unbind("onkeyup,click");
			$("a#post-preview.preview.button").attr("href","#").prop("target","").removeAttr("target");

			$("a#post-preview.preview.button").after("<span class=button id=post-preview-new>'.__('Preview Changes').'</span>");
			$("a#post-preview.preview.button").remove();

      $("body").append("<div id=better-edit-adminpreview><a id=better-edit-adminpreviewcancel>x</a><iframe src=\'\' id=betteriframe>iframe</iframe></div>");
      $("#better-edit-adminpreview").css("left",$(window).width());

			$( "body" ).on( "click", "#publish,#post-preview-new", function() {
      //$("#publish,#post-preview").click(function() {


        if( $("#original_post_status").val() == "publish" ) {
        var overidedata = {
      		"action": "better_edit_save_post",
      		"whatever": ""
      	};

        $(this).prop("disabled",true);
        //$("body").css("position","fixed").animate({ left: - $(window).width() }, 500);
        $("#better-edit-adminpreview").css("width", $("#better-edit-adminpreview").outerWidth() - $("#adminmenu:visible").outerWidth() );
				$("#better-edit-adminpreview iframe").remove();
        $("#better-edit-adminpreview").animate({ left: parseInt( $("#adminmenu:visible").outerWidth() ) }, 500);
      	jQuery.post( "'.admin_url( 'admin-ajax.php' ).'", $("#post").serialize() + "&security='.wp_create_nonce( "wp-betteredit" ).'", function(response) {

          $("#better-edit-adminpreview").append("<iframe src="+response+" id=betteriframe>iframe</iframe>");

      	});

        return false;

        }

      });

      $("#better-edit-adminpreviewcancel").click(function() {
        $("#better-edit-adminpreview").stop().animate({ left: $(window).width() }, 200);
        $("#publish,#post-preview-new").prop("disabled",false);
        $("#better-edit-adminpreview iframe").remove();
				$("#better-edit-adminpreview").css("width", $(window).width() );
        return false;
      });

      $(window).resize(function() {
        $("#better-edit-adminpreviewcancel").trigger("click");
      });

			$(document).keyup(function(e){

			    if(e.keyCode === 27) $("#better-edit-adminpreviewcancel").trigger("click");

			});

      });';
    echo '</script>';

    echo '
    <style>
    #better-edit-adminpreview { background: #23282d url('.plugins_url( 'ripple.svg', __FILE__ ).') center center no-repeat; position: fixed; left: 0; top: 0; height: 100%; width: 100%; z-index: 9999; }
    #betteriframe { width: 100%; height: 100%; }
    #better-edit-adminpreviewcancel { position: absolute; left: 50px; top: 100px; font-size: 5em; color: #0085ba; text-shadow: 0 2px 20px rgba(0,0,0,0.5); cursor: pointer; }
    #better-edit-adminpreviewcancel:hover { color: #fff; }
    </style>
    ';
}

function betteredit_save_post() {

	check_ajax_referer( 'wp-betteredit', 'security' );
  if(current_user_can('edit_posts')) {

		$_POST = sanitize_post( $_POST, 'db' );
    $_POST["ID"] = intval( $_POST["post_ID"] );
    $_POST["post_content"] = sanitize_post_field( 'post_content', $_POST["content"], $_POST["ID"], 'db' );
		$_POST["post_excerpt"] = sanitize_post_field( 'post_excerpt', $_POST["excerpt"], $_POST["ID"], 'db' );
    $post_id = wp_update_post( $_POST, true );

		//WP SEO (YOAST) FIX
		if(function_exists('wpseo_auto_load')) {
			$WPSEO_Metabox_NEW = new WPSEO_Metabox;
			$WPSEO_Metabox_NEW->save_postdata( $_POST["ID"] );
		}
    $url = get_permalink( $post_id );
  	if($post_id) { echo $url; }
	}

	wp_die();
}

function betteredit_admin_enqueue($hook) {
  if( 'post.php' != $hook ) {	return; }
  //wp_localize_script( 'ajax-script', 'better_edit_objects', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
}
 ?>
