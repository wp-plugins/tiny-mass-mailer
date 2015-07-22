<?php
/*
Plugin Name: tiny-mass-mailer
Plugin URI : http://wp-master.ir
Author: wp-master.ir
Author URI: http://wp-master.ir
Description: tiny-mass-mailer
Version: 1.7
Text Domain: tinymassmailer
*/

/*
* No script kiddies please!
*/
defined('ABSPATH') or die("اللهم صل علی محمد و آل محمد و عجل فرجهم");

/*
* Defines
*/
define('_tinymassmailer_DIR' 				, dirname( __FILE__ ).DIRECTORY_SEPARATOR);
define('_tinymassmailer_PATH' 				, plugin_dir_url( __FILE__ ));

if(!function_exists('PHPMailerAutoload')){
	require_once _tinymassmailer_DIR.'inc'.DIRECTORY_SEPARATOR.'PHPMailer'.DIRECTORY_SEPARATOR.'PHPMailerAutoload.php';
}


if(is_admin())
	require_once _tinymassmailer_DIR.'inc'.DIRECTORY_SEPARATOR.'listTable.php';

/*
* load plugin language
*/
add_action( 'plugins_loaded', '_tinymassmailer_lang');
function _tinymassmailer_lang()
{
	load_plugin_textdomain( 'tinymassmailer', false, dirname( plugin_basename( __FILE__ ) ).DIRECTORY_SEPARATOR);
}
__('tiny-mass-mailer' , 'tinymassmailer');
__('tiny-mass-mailer' , 'tinymassmailer');


/**
 * Load Plugin Style And script that are need in front area
 */
function tinymassmailer_user_styles_scripts($hook)
{
		if(!is_admin()){
	        wp_register_script('tinymassmailer' . 'user_JS'		, _tinymassmailer_PATH . 'js/script.js', array('jquery'), 1.0, false);
	        wp_register_style('tinymassmailer' . 'user_CSS'		, _tinymassmailer_PATH.'css/style.css', array(), 1.0, 'all');
			/**
			 * Localizes Scripts
			 */
			$translation_array = array(
				'text' => __( 'Localizing was successful', 'tinymassmailer' ),
				'ajaxurl' => admin_url('admin-ajax.php')
			);
			wp_localize_script( 'tinymassmailer' . 'user_JS', 'tinymassmailer', $translation_array );
			wp_enqueue_script('tinymassmailer' . 'user_JS');
	        wp_enqueue_style('tinymassmailer' . 'user_CSS');


		}elseif($hook == 'settings_page_tinymassmailer-options'){
	        wp_register_script('tinymassmailer' . 'admin_JS'		, _tinymassmailer_PATH . 'js/admin.js', array('jquery'), 1.0, false);
	        wp_register_style('tinymassmailer' . 'admin_CSS'		, _tinymassmailer_PATH.'css/admin.css', array(), 1.0, 'all');
	        wp_enqueue_script('tinymassmailer' . 'admin_JS');
	        wp_enqueue_style('tinymassmailer' . 'admin_CSS');
		}

}

add_action('wp_enqueue_scripts', 'tinymassmailer_user_styles_scripts');
add_action('admin_enqueue_scripts', 'tinymassmailer_user_styles_scripts');

/**
 * On plugin activation
 */
 function tinymassmailer_activate() {
		global $wpdb;
	    //create tables
	     $tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
	     $sql[] = "CREATE TABLE IF NOT EXISTS `$tiniymassm_sends` (
	      `id` 				bigint(20) NOT NULL AUTO_INCREMENT,
	      `last_user_id` 	varchar(200) NOT NULL,
	      `last_sent_time` 	varchar(200) NOT NULL,
	      `start_date` 		varchar(200) NOT NULL,
	      `end_date` 		varchar(200)  	NULL,
	      `smtp` 			varchar(200) 	NULL,
	      `text` 			text NOT NULL,
	      `subject` 		varchar(200) NOT NULL,
	      `state` ENUM( 'completed' , 'new' ,'canceled' ),
	      `opens` 			varchar(200) 	NULL DEFAULT '0',
	      `clicks` 			varchar(200) 	NULL DEFAULT '0',
	      PRIMARY KEY (`id`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		
		$tiniymassm_urls =$wpdb->prefix.'tiniymassm_urls';
	     $sql[] = "CREATE TABLE IF NOT EXISTS `$tiniymassm_urls` (
	      `id` 				bigint(20) NOT NULL AUTO_INCREMENT,
	      `url` 			varchar(200) NOT NULL,
	      `hits` 			varchar(200) NOT NULL,
	      PRIMARY KEY (`id`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	     foreach($sql as $s){
	        dbDelta($s);
	        $wpdb->query($s);
	     }
}
register_activation_hook( __FILE__, 'tinymassmailer_activate' );


/**
 * On plugin deactivation
 */
 function tinymassmailer_deactivate() {

    // deactivation code here...
}
register_deactivation_hook( __FILE__, 'tinymassmailer_deactivate' );


/**
* plugin shortcode
*/
function tinymassmailer_shortcode(){
	$html = '<div id="tiny-mass-mailer">';
	$html .= 'tinymassmailer shortcode contents here';
	$html .= '</div> <!-- end of #tiny-mass-mailer --> ';
	return $html;
}
add_shortcode('tinymassmailer_shortcode' , 'tinymassmailer_shortcode');

/**
 * Admin panel menu
 */
require_once 'class-options.php';

/**
* Ajax request handling
*/
add_action( 'wp_ajax_tiniymassmailer_smtpcheck', 'tiniymassmailer_smtpcheck' );
// add_action( 'wp_ajax_nopriv_tiniymassmailer_smtpcheck', 'tiniymassmailer_smtpcheck' );
function tiniymassmailer_smtpcheck(){
	$msg = tinymassmailer_Options::check_smtp($_POST['name'] , $_POST['host'] , $_POST['user'] , $_POST['pass'] , $_POST['port'] , $_POST['secure'] , true);
	echo json_encode(array('msg' => $msg));
	die();
}

add_action( 'wp_ajax_tiniymassmailer_start_sending', 'tiniymassmailer_start_sending' );
// add_action( 'wp_ajax_nopriv_tiniymassmailer_start_sending', 'tiniymassmailer_start_sending' );
function tiniymassmailer_start_sending(){
	$msg = '';
	// sending mail	
	global $wpdb;
	$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';

	$last_user_id = 0;
	if(isset($_POST['tinymassmailer_start_from'])){
		$last_user_id = (int)$_POST['tinymassmailer_start_from'];
	}

	$r = $wpdb->insert(
		$tiniymassm_sends,
		array(
			'last_user_id' 	=> $last_user_id,
			'start_date'	=>	current_time( 'mysql', '1' ),
			'text' 			=> 	$_POST['text'] 		, 
			'subject' 		=> 	$_POST['subject'] 		, 
			'state' 		=> 'new' 		, 

			),
		array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			)
		);

	 //add trackings to text
	 $tracked_text = tinymassmailer_make_tracking($_POST['text'] , $wpdb->insert_id);
	$wpdb->update($tiniymassm_sends,
		array('text' => $tracked_text),
		array('id' => $wpdb->insert_id),
		array('%s'),
		array('%d')
		);

	if($r){
		$msg = __('Send goes to queue' , 'tinymassmailer');
	}else{
		$msg = __('Problem to Sending to queue' , 'tinymassmailer');
	}
	echo json_encode(array('msg' => $msg));
	die();
}


/**
* add trackinges methods to text
* @since 1.2
*/
function tinymassmailer_make_tracking($txt , $send_id){
	//add clicks tracking
	if(!function_exists('file_get_html')){
		require_once _tinymassmailer_DIR.'inc'.DIRECTORY_SEPARATOR.'simple_html_dom.php';
	}

	// Create DOM from string
	$html = str_get_html($txt);
	$url = get_bloginfo('siteurl').'/?tinymassmailer_c='.$send_id.'&tmm_url=';
	foreach($html->find('a') as $key => $v){
		$html->find('a' , $key)->href =  $url.str_replace('"' , '' ,$html->find('a' , $key)->href);
	}

	$txt = $html;

  	//add opens tracking
	$txt = $txt . '<hr/><img src="'.get_bloginfo('siteurl').'/?tinymassmailer_t='.$send_id.'">';


	return $txt;
}

$_tinymassmailer_make_tracking_transient = '_tinymassmailer_make_tracking_run_check_'.$_SERVER['REMOTE_ADDR'];
function _tinymassmailer_make_tracking(){
	if(isset($_GET['tinymassmailer_t'])){
		$sImage = _tinymassmailer_PATH.'css/img/live.jpg';
		if(get_transient( $_tinymassmailer_make_tracking_transient )) {		header("Content-Type: image/jpeg");	echo file_get_contents($sImage);die();	};
		set_transient( $_tinymassmailer_make_tracking_transient, 'locked_for_10_seconds', 10 );
		
		//update opens emails
		global $wpdb;
		$send_id =(int)($_GET['tinymassmailer_t']);
		$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
		$row = $wpdb->get_row("select * from $tiniymassm_sends where id=$send_id");
		if(!$row) return;

		$new_opens = (int)$row->opens;
		$new_opens += 1;
		$wpdb->update($tiniymassm_sends,
			array('opens' => $new_opens),
			array('id' => $send_id),
			array('%d'),
			array('%d')
			);			

		header("Content-Type: image/jpeg");
		echo file_get_contents($sImage);
		die();	
	}

	if(isset($_GET['tinymassmailer_c'])){
		$url = trim($_GET['tmm_url']);
		if(get_transient( $_tinymassmailer_make_tracking_transient )) { header('location:'.$url); die(); };
		global $wpdb;
		$send_id =(int)($_GET['tinymassmailer_c']);
		$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
		$row = $wpdb->get_row("select * from $tiniymassm_sends where id=$send_id");
		if(!$row) return;

		$new_clicks = (int)$row->clicks;
		$new_clicks += 1;
		$wpdb->update($tiniymassm_sends,
			array('clicks' => $new_clicks),
			array('id' => $send_id),
			array('%d'),
			array('%d')
			);
			
		//add url hits to DB

		$tiniymassm_urls =$wpdb->prefix.'tiniymassm_urls';
		$url_res = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tiniymassm_urls where `url` = '%s'" , array($url)));
		if($url_res){
			$wpdb->update(
				$tiniymassm_urls,
				array(
					'hits' => $url_res->hits+1
				),
				array('id' => $url_res->id),
				array('%d'),
				array('%d')
			);
		}else{
			$wpdb->insert(
				$tiniymassm_urls,
				array(
					'url' 		=> $url,
					'hits'		=>	1 , 
				),
				array('%s' , '%d' , '%d')
			);
		}
		
		header('location:'.$url);
		die();

	}

}
add_action('init' , '_tinymassmailer_make_tracking' , 1);

