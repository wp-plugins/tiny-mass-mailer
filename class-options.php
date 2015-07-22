<?php

/**
 * Master theme class
 * 
 * @package Bolts
 * @since 1.0
 */
class tinymassmailer_Options {
	
	private $sections;
	private $checkboxes;
	private $settings;
	
	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {

		
		add_action( 'admin_menu', array( &$this, 'add_pages' ) );

		
	}
	
	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public function add_pages() {
		
		$admin_page =add_menu_page( 	__( 'Tiny Mass Mailer' , 'tinymassmailer' )	, __( 'Tiny Mass Mailer' , 'tinymassmailer' )	, 'manage_options', 'tinymassmailer-options'	, array( $this, 'display_page' ) );
		$admin_page_sends = add_submenu_page( 'tinymassmailer-options', 	__('Sends' , 'tinymassmailer')				, __('Sends' , 'tinymassmailer')					, 'manage_options', 'tinymassmailer_sends'		, array( $this, 'display_sends_page' ) );
		$admin_page_urls = add_submenu_page( 'tinymassmailer-options', 	__('Urls' , 'tinymassmailer')				, __('Urls' , 'tinymassmailer')					, 'manage_options', 'tinymassmailer_urls'		, array( $this, 'display_url_page' ) );
		$display_help_page = add_submenu_page( 'tinymassmailer-options', 	__('Help' , 'tinymassmailer')				, __('Help' , 'tinymassmailer')					, 'manage_options', 'tinymassmailer_help'		, array( $this, 'display_help_page' ) );
		
		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'styles' ) );
		
		add_action( 'admin_print_scripts-' . $admin_page_sends, array( &$this, 'scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page_sends, array( &$this, 'styles' ) );


	}
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function display_page() {
		
		echo '<div class="wrap">
	<div class="icon32" id="icon-options-general"></div>
	<h2>' . __( 'tiny-mass-mailer Options' , 'tinymassmailer') . '</h2>';
	if(isset($_POST['tiny'])){
		update_option( 'tinymassmailer', $_POST['tiny'] );
		echo $this::_echo(__('Updated' , 'tinymassmailer') , 1);
	}
	$tiny = get_option('tinymassmailer');
	?>
	<form method="post">
		<p class="p-box-input">
			<strong><?php _e('Admin Mail' , 'tinymassmailer'); ?>:</strong>
			<input type="text" value="<?php echo $tiny['admin_email']; ?>" name="tiny[admin_email]">
		</p>
		<p class="p-box-input">
			<strong><?php _e('From Name' , 'tinymassmailer'); ?>:</strong>
			<input type="text" value="<?php echo $tiny['from_name']; ?>" name="tiny[from_name]">
		</p>
		<p class="p-box-input">
			<strong><?php _e('From Mail' , 'tinymassmailer'); ?>:</strong>
			<input type="text" value="<?php echo $tiny['send_from']; ?>" name="tiny[send_from]">
		</p>
		<p class="p-box-input">
			<strong><?php _e('Reply To' , 'tinymassmailer'); ?>:</strong>
			<input type="text" value="<?php echo $tiny['reply_to']; ?>" name="tiny[reply_to]">
		</p>
		<hr>
		<p>
			<strong><?php _e('SMTP Count' , 'tinymassmailer'); ?></strong><br>  <input type="number" value="<?php echo $tiny['smtp_count']; ?>" name="tiny[smtp_count]">
		</p>
		<hr/>
		<p>
		<strong>SMTP's</strong>
		<?php
		$smtp_input_pattern =<<<EEE
		<span style="display:block;margin:5px;direction:ltr;" class="tinymassmailer-span %CLASS%">
			<input type="text" class="smtp-n" name="tiny[smtp][name][]" placeholder="name" value="%N%">
			<input type="text" class="smtp-h" name="tiny[smtp][host][]" placeholder="host" value="%H%">
			<input type="text" class="smtp-u" name="tiny[smtp][user][]" placeholder="user" value="%U%">
			<input type="password" class="smtp-p" name="tiny[smtp][pass][]" placeholder="pass" value="%P%">
			<input type="text" class="smtp-po" name="tiny[smtp][port][]" placeholder="port" value="%PO%"> 
			Secure:
			<select name="tiny[smtp][secure][]">
			<option value="ssl" %SS%>SSL</option>
			<option value="tsl" %TS%>TSL</option>
			<option value="-1" %NS%>None</option>
			</select>

			<select name="tiny[smtp][enable][]">
			<option value="1" %ES%>Enable</option>
			<option value="0" %DS%>Disable</option>
			</select>
			<br>
			Limit Per Hour:<input type="number" class="smtp-limit-per-hour" name="tiny[smtp][limit][]" placeholder="limit-per-hour" value="%L%"> 

			<b>%C%</b>
			<i class="remove-me">×</i>
			<br/>
		</span>
EEE;
		for($i=0 ; $i <tinymassmailer_options('smtp_count') ; $i++){
			$ss = ($tiny['smtp']['secure'][$i] =='ssl'?'selected="selected"':'');
			$ts = ($tiny['smtp']['secure'][$i] =='tsl'?'selected="selected"':'');
			$ns = ($tiny['smtp']['secure'][$i] =='-1'?'selected="selected"':'');

			$es = ($tiny['smtp']['enable'][$i] =='1'?'selected="selected"':'');
			$ds = ($tiny['smtp']['enable'][$i] =='0'?'selected="selected"':'');
			

			$class = ($tiny['smtp']['enable'][$i] =='0'?'disable':'enable');
			echo str_replace(
				array('%CLASS%' ,'%N%' , '%H%' ,  '%U%' , '%P%' , '%PO%' , '%L%' , '%SS%' , '%TS%' ,'%NS%' , '%ES%' , '%DS%' ,'%C%'),
				array($class ,$tiny['smtp']['name'][$i] ,$tiny['smtp']['host'][$i] , $tiny['smtp']['user'][$i] , $tiny['smtp']['pass'][$i] , $tiny['smtp']['port'][$i] , $tiny['smtp']['limit'][$i] ,$ss,$ts,$ns,$es,$ds, $this::check_smtp($tiny['smtp']['name'][$i] , $tiny['smtp']['host'][$i] ,$tiny['smtp']['user'][$i] , $tiny['smtp']['pass'][$i] , $tiny['smtp']['port'][$i])),
				$smtp_input_pattern
				);
		}
		?>
		</p>
		<p>
			<input class="button-primary" type="submit" value="<?php _e('Save' , 'tinymassmailer'); ?>">
		</p>
	</form>
	<?php

	echo '</div>';
		
	}
	
	/**
	* display sends state
	* @since 0.7
	*/
	public function display_sends_page(){
		?>
		<div class="wrap">
			<h2><?php _e('Sends' , 'tinymassmailer'); ?></h2>
			<h4><a href="admin.php?page=<?php echo $_GET['page']; ?>&add_new_send=true"><?php _e('New Send' , 'tinymassmailer'); ?></a></h4>
			<?php
			if(isset($_GET['add_new_send'])){
			?>
			<div class="send-mail add_new_send">
				<!-- <h2><?php _e('Sending mail' , 'tinymassmailer'); ?></h2> -->
				<?php _e('Subject' , 'tinymassmailer'); ?>:<br/>
				<input type="text" name="tinymassmailer_subject" id="tinymassmailer_subject"> , 
				<?php _e('Start From This User ID' , 'tinymassmailer'); ?>:<input type="text" name="tinymassmailer_start_from" id="tinymassmailer_start_from">
				<br>
				<?php 
				_e('Mail Content' , 'tinymassmailer');
				echo ':<br/>';
				echo wp_editor(
				 __('Mail contect Here...' , 'tinymassmailer'),
				 'tinymassmailer_editor',
				 array('tinymce'=> 
					array(
			        'theme_advanced_buttons1' =>  "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
			        'theme_advanced_buttons2' =>  "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			        'theme_advanced_buttons3' =>  "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			        'theme_advanced_buttons4' =>  "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
			        'theme_advanced_toolbar_location' =>  "top",
			        'theme_advanced_toolbar_align' =>  "left",
			        'theme_advanced_statusbar_location' =>  "bottom",
			        'theme_advanced_resizing' =>  true),
         'textarea_rows' => 3 ,'editor_class' => 'tinymassmailer_editor') );
				?>
				<button class="button-primary" id="tinymassmailer_start_sending"><?php _e('Start Sending' , 'tinymassmailer'); ?></button>
			</div>
			<?php
			}


			//delete
			if(isset($_GET['action'] , $_GET['send']) && $_GET['action'] == 'delete'){
				$send  = (int) $_GET['send'];
				global $wpdb;
				$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
				if($wpdb->delete($tiniymassm_sends , array('id' => $send))){
					echo $this::_echo(__('Deleted' , 'tinymassmailer') , 1);
				}

			}

			//cancel
			if(isset($_GET['action'] , $_GET['send']) && $_GET['action'] == 'cancel'){
				$send  = (int) $_GET['send'];
				global $wpdb;
				$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
				if($wpdb->update($tiniymassm_sends ,array('state' => 'canceled'), array('id' => $send) , array('%s') ,array('%d') )){
					echo $this::_echo(__('Canceled' , 'tinymassmailer') , 1);
				}

			}
			//resend
			if(isset($_GET['action'] , $_GET['send']) && $_GET['action'] == 'resend'){
				$send  = (int) $_GET['send'];
				global $wpdb;
				$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
				if($wpdb->update($tiniymassm_sends ,array('state' => 'new'), array('id' => $send) , array('%s') ,array('%d') )){
					echo $this::_echo(__('Send goes to queue' , 'tinymassmailer') , 1);
				}

			}

			//show full text
			if(isset($_GET['action'] , $_GET['send']) && $_GET['action'] == 'show full text'){
				$send  = (int) $_GET['send'];
				global $wpdb;
				$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
				$row = $wpdb->get_row("select * from $tiniymassm_sends where id=$send ");
				$row->text = str_replace('<hr/><img src="'.get_bloginfo('siteurl').'/?tinymassmailer_t='.$send.'">', '', $row->text);
				?>
				<div class="sending">
					<h3 class="subject"><?php echo $row->subject; ?></h3>
					<div class="text">
						<?php
						echo stripcslashes($row->text);
						?>
					</div>
				</div>
				<?php


			}


			?>

				<?php
				$list = new tinymassmailer_data_table();
				$list->prepare_items(); 
				echo $list->display();
				?>	
			<div class="wrap-clear-both"></div>				
			</div>
		<div class="wrap-clear-both"></div>
		<?php

	}

	/**
	* @since 1.4
	*
	*/
	public function display_url_page(){
			?>
			<div class="wrap">
			<div class="display_url_page">
			<?php
				$list = new tinymassmailer_data_table_url();
				$list->prepare_items(); 
				echo $list->display();
				?>				
			</div>
			<div class="wrap-clear-both"></div>
			</div>
			<?php
	}

	static public function _echo($msg , $type=1){
		if($type==1){
			echo '
				<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
				<p><strong>'.$msg.'</strong></p></div>
							';
		}else{
			echo '
				<div class="error settings-error notice is-dismissible" id="setting-error-settings_updated"> 
				<p><strong>'.$msg.'</strong></p></div>
							';

		}
	}

	/**
	* Display help
	*@since 1
	*/
	public function display_help_page(){
		?>
		<div class="wrap">
			<h4><?php _e('Usage' , 'tinymassmailer'); ?></h4>
			<hr>
			<ol>
				<li><?php _e('First in setting page Set \'SMTP Count\' and Click \'Save\''  , 'tinymassmailer'); ?></li>
				<li><?php _e('Define SMTP\'s'  , 'tinymassmailer'); ?></li>
				<li><?php _e('Set each SMTP sends limits per hour'  , 'tinymassmailer'); ?></li>
				<li><?php _e('Use \'Check\' Button to check SMTP connection'  , 'tinymassmailer'); ?></li>
				<li><?php _e('If you have any smtp that is not correct in connection Delete it or Disable,if did not it,Tiny Mass Mailer use them as correct SMTP'  , 'tinymassmailer'); ?></li>
				<li><?php _e('Create new send'  , 'tinymassmailer'); ?></li>
				<li><?php _e('And see Sends State'  , 'tinymassmailer'); ?></li>
			</ol>
			<h4><?php _e('Notes', 'tinymassmailer'); ?></h4>
			<hr>
			<ol>
				<li><?php _e('For Using Gmail You must first  Allow less secure apps at this link '  , 'tinymassmailer'); ?>:<a href="https://www.google.com/settings/security/lesssecureapps"> Allow less secure apps</a></li>
				<li><?php _e('Tiny Mass Mailer will process just one send at time'  , 'tinymassmailer'); ?></li>
				<li><?php _e('More SMTP More send Speed'  , 'tinymassmailer'); ?></li>
				<li><?php _e('For 100% inbox use one email and set it in \'sender\' and \'reply to\' field also , by this way sending goes to be slow but emails goes to inbox completely'  , 'tinymassmailer'); ?></li>
			</ol>
			<h4><?php _e('Sample Config', 'tinymassmailer'); ?></h4>
			<hr>
			<style>
				td , th{ width:50px;text-align:center;}
			</style>
			<table>
				<tr>
					<th>Name</th>
					<th>Host</th>
					<th>User</th>
					<th>Pass</th>
					<th>Port</th>
					<th>Secure</th>
					<th>Limitition/Hour</th>
				</tr>
				<tr>
					<td>Gmail/TSL</td>
					<td>smtp.gmail.com</td>
					<td>User@gmail.com</td>
					<td>**********</td>
					<td>587</td>
					<td>TSL</td>
					<td>4</td>
				</tr>
				<tr>
					<td>Yahoo/TSL</td>
					<td>smtp.mail.yahoo.com</td>
					<td>User@yahoo.com</td>
					<td>**********</td>
					<td>587</td>
					<td>TSL</td>
					<td>4</td>
				</tr>
				<tr>
					<td>Gmail/SSL</td>
					<td>smtp.gmail.com</td>
					<td>User@gmail.com</td>
					<td>**********</td>
					<td>465</td>
					<td>SSL</td>
					<td>4</td>
				</tr>
				<tr>
					<td>Webmail  </td>
					<td>mail.domain.com</td>
					<td>User@domain.com</td>
					<td>**********</td>
					<td>587</td>
					<td>TSL</td>
					<td>50</td>
				</tr>
			</table>
		<div class="wrap-clear-both"></div>
		</div>
		<?php
	}
	
	/**
	* jQuery Tabs
	*
	* @since 1.0
	*/
	public function scripts() {
		
		wp_print_scripts( 'jquery-ui-tabs' );
		wp_enqueue_script( 'tinymassmailer-admin-js', _tinymassmailer_PATH.'/js/admin.js', array('jquery'));
		
	}
	
	/**
	* Styling for the theme options page
	*
	* @since 1.0
	*/
	public function styles() {
		
		wp_register_style( 'tinymassmailer-admin', _tinymassmailer_PATH . '/css/page-options.css' );
		wp_enqueue_style( 'tinymassmailer-admin' );
		
	}


	public static function check_smtp($name , $host , $user , $pass , $port , $secure=false  , $ajax=false){
		$name = trim($name);
		$host = trim($host);
		$user = trim($user);
		$pass = trim($pass);
		$secure = trim($secure);
		$port = (int)trim($port);

		if($host =='' || $user == '' || $pass == '' || $port == '') return '!';

		if(!$ajax)
			return '<b class="smtp-check">Check <img class="ajax-img" src="'._tinymassmailer_PATH.'/css/img/ajax-loader.gif"> <span class="smtp-result"></span></b>';

		
		$mail = new PHPMailer();  // create a new object
		$mail->IsSMTP(); // enable SMTP
		$mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true;  // authentication enabled
		$mail->SMTPSecure = $secure; // secure transfer enabled REQUIRED for GMail
		$mail->Host = $host;
		$mail->Port = $port; 
		$mail->Username = $user;  
		$mail->Password = $pass;           
		$mail->SetFrom($user, $user);
		$mail->Subject = 'SMTP Test From tinymassmailer';
		$mail->Body = 'SMTP Test From tinymassmailer';
		$mail->AddAddress($user);
		if(!$mail->smtpConnect()) {
			// return $mail->ErrorInfo; 
			return 'Error!';
		} else {
			return  'OK!';
		}


	}

	public static function send(){
		global $wpdb;
		$_tiny_in_process = '_tiny_in_process';
		//if sending is in process
		//if(file_exists(_tinymassmailer_DIR.'.in_process')) return;
		if(get_transient( $_tiny_in_process)){ return; }
		

		$tiniymassm_sends =$wpdb->prefix.'tiniymassm_sends';
		$result = $wpdb->get_results("SELECT * from $tiniymassm_sends where `state`='new' limit 1");
		//if no emails for send
		if(count($result) == 0) return;
		//

		//if this send last user id ended


		//if one hour not ended
		if( (time() - $result[0]->last_sent_time) < 3600) return;
		//


		//if no active smtp
		$smtp = tinymassmailer_options('smtp');
		$_smtp = array();
		$active_smtp = 0;
		for($i=0 ; $i < count($smtp) ; $i++){
			if($smtp['enable'][$i] == 1){
				$active_smtp++;
				$_smtp[] = 
				array
				(
				'name' 		=> $smtp['name'][$i],
				'host' 		=> $smtp['host'][$i],
				'user' 		=> $smtp['user'][$i],
				'pass' 		=> $smtp['pass'][$i],
				'port' 		=> $smtp['port'][$i],
				'secure' 	=> $smtp['secure'][$i],
				'enable' 	=> $smtp['enable'][$i],
				'limit'		=> $smtp['limit'][$i],
				);

			}
		}

		if($active_smtp == 0) return;
		//


		//send email

		//lock file
		//file_put_contents(_tinymassmailer_DIR.'.in_process' , time());
		set_transient( $_tiny_in_process, 'locked_for_10_seconds', 10 );

		$last_id = -1;
		foreach ($_smtp as $value) {
			for($i=0 ; $i <$value['limit'] ; $i++){
				//get last user id from tinymassmailer send
				$send_id = $result[0]->id;
				$last_user_id_res_t = $wpdb->get_results("SELECT * from $tiniymassm_sends where `state`='new' and id=$send_id");
				$last_id = $last_user_id_res_t[0]->last_user_id;
				$subject = $last_user_id_res_t[0]->subject;
				//get last user id from users
				$last_user_id_res 	= $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID > $last_id ORDER BY ID LIMIT 1;");
				//if the send reach end of its sending
				if(!$last_user_id_res){
					//alert admin
					//notifications
					$admin_email = tinymassmailer_options('admin_email');
					extract($value);
					$subject 	= __($subject.' Emails Sent Completely' , 'tinymassmailer') . ' | '.$result[0]->subject;
					$txt 		= __('Emails Sent Completely at: '.date('l jS F Y h:i:s A') , 'tinymassmailer');
					tinymassmailer_Options::_send($admin_email , $name , $host , $user , $pass , $port , $secure , $subject , $txt);

					//update send state
					$wpdb->update(
					$tiniymassm_sends,
					array(
						'state' 		=> 'completed',
						'end_date'		=>	current_time( 'mysql', 1 )
						),
					array(
						'id' => $result[0]->id
						),
					array('%s' , '%s'),
					array('%d')

					);

					break 1;

				}
				$user_id = $last_user_id_res->ID;
				$u_result = $wpdb->get_results("SELECT user_email from $wpdb->users where `ID`=$user_id");
				if(count($u_result)==0) { continue 1; }

				$to = $u_result[0]->user_email;
				extract($value);
				$subject 	= stripcslashes($result[0]->subject);
				$txt 		= stripcslashes($result[0]->text);
				tinymassmailer_Options::_send($to , $name , $host , $user , $pass , $port , $secure , $subject , $txt);
				$wpdb->update(
				$tiniymassm_sends,
				array(
					'last_user_id' 		=> $user_id,
					'last_sent_time'	=>	time()
					),
				array(
					'id' => $result[0]->id
					),
				array('%d' , '%s'),
				array('%d')

				);
				
			}

		}
		//delete locked file
		/*
		if(function_exists('delete')) delete(_tinymassmailer_DIR.'.in_process');
		if(function_exists('unlink')) unlink(_tinymassmailer_DIR.'.in_process');
		*/

	}

	static public function _send($to , $name , $host , $user , $pass , $port , $secure , $subject , $txt){
		$mail = new PHPMailer();  // create a new object
		$mail->IsSMTP(); // enable SMTP
		$mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true;  // authentication enabled
		if($mail->SMTPSecure != '-1'){
				$mail->SMTPSecure = $secure; // secure transfer enabled REQUIRED for GMail
		}
		$mail->Host = $host;
		$mail->Port = $port; 
		$mail->Username = $user;  
		$mail->Password = $pass;           
    	$mail->From = tinymassmailer_options('send_from');
    	$mail->FromName = tinymassmailer_options('from_name');
		$mail->SetFrom(tinymassmailer_options('send_from'), tinymassmailer_options('from_name'));
		$mail->AddReplyTo(tinymassmailer_options('reply_to'),tinymassmailer_options('from_name'));

		$mail->Subject = $subject;
		$mail->msgHTML($txt);
		$mail->AddAddress($to);
		$mail->CharSet = 'UTF-8';
		if(!$mail->send()) {
			return false;
		} else {
			return  true;
		}
	}
}

$theme_options = new tinymassmailer_Options();

function tinymassmailer_options( $option ) {
	$options = get_option( 'tinymassmailer' );
	if ( isset( $options[$option] ) )
		return $options[$option];
	else
		return false;
}

tinymassmailer_Options::send();