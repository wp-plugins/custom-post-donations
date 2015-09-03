<?php
/*
Plugin Name: Custom Post Donations
Plugin URI: http://labs.hahncreativegroup.com/wordpress-paypal-plugin/
Description: This WordPress plugin will allow you to create unique customized PayPal donation widgets on WordPress posts or pages and accept donations. Creates custom PayPal donation widgets.
Author: HahnCreativeGroup
Version: 4.1.1
Author URI: http://labs.hahncreativegroup.com/wordpress-paypal-plugin/
*/

global $cpDonations_table;
global $cpDonations_plugin_db_version;
global $wpdb;
$cpDonations_table = $wpdb->prefix . 'cp_donations';
$cpDonations_plugin_db_version = '1.3';

register_activation_hook( __FILE__,  'cpDonations_install' );

function cpDonations_install() {
  global $wpdb;
  global $cpDonations_table;
  global $hcg_presspage_plugin_db_version;

  if ( $wpdb->get_var( "show tables like '$cpDonations_table'" ) != $cpDonations_table ) {
			
	$sql = "CREATE TABLE $cpDonations_table (".
		"Id INT NOT NULL AUTO_INCREMENT, ".
		"name VARCHAR( 100 ) NOT NULL, ".
		"slug VARCHAR( 100 ) NOT NULL, ".
		"description TEXT, ".
		"donationtype INT NOT NULL, ".
		"maxitems INT NOT NULL, ".
		"defaultdonation DECIMAL(7,2),".
		"PRIMARY KEY Id (Id) ".
		")";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	add_option( "cpDonations_plugin_db_version", $cpDonations_plugin_db_version );	
  }
  add_option("cpDonations_Business_Name", "Enter Email Address");
}

function cpDonations_options() {
	if (!get_option('cpDonations_returnUrl')) {
		add_option('cpDonations_returnUrl', "");	
	}
	if (!get_option('cpDonations_buttonStyle')) {
		add_option('cpDonations_buttonStyle', "default");	
	}
	if (!get_option('cpDonations_restrictToPagePost')) {
		add_option('cpDonations_restrictToPagePost', "true");	
	}
}
add_action('plugins_loaded', 'cpDonations_options');

//Add Admin Styles
function cpDonation_admin_style() {
	wp_register_style('cpDonation-admin-style', WP_PLUGIN_URL.'/custom-post-donations/admin/styles/style.css');
	wp_enqueue_style('cpDonation-admin-style');
}

// Create Admin Panel
function add_cpDonations_menu()
{
	add_menu_page(__('CP Donations','menu-cpDonations'), __('CP Donations','menu-cpDonations'), 'manage_options', 'cpDonations-admin', 'showCpDonationsMenu' );
	
	// Add a submenu to the custom top-level menu:
	add_submenu_page('cpDonations-admin', __('CP Donations >> Add Page','menu-cpDonations'), __('Add Donation','menu-cpDonations'), 'manage_options', 'add-cpDonation', 'add_cpDonation');
	add_submenu_page('cpDonations-admin', __('CP Donations >> Add Page','menu-cpDonations'), __('Edit Donation','menu-cpDonations'), 'manage_options', 'edit-cpDonation', 'edit_cpDonation');
}

add_action( 'admin_menu', 'add_cpDonations_menu' );
add_action( 'admin_menu', 'cpDonation_admin_style' );

function showCpDonationsMenu()
{
	include("admin/overview.php");
}

function add_cpDonation()
{
	include("admin/add-cpDonation.php");
}

function edit_cpDonation()
{
	include("admin/edit-cpDonation.php");
}

function add_jquery_cpDonation() {
	wp_register_script('cp-donations', WP_PLUGIN_URL.'/custom-post-donations/scripts/jquery.cpDonations.js', array('jquery'));
	wp_enqueue_script('jquery');
	wp_enqueue_script('cp-donations');
}
add_action('wp_enqueue_scripts', 'add_jquery_cpDonation');

function createCPDonationForm($cpDonationName, $id) {
	/*
	// Donation types:
	// 1) Standard - one editable donation amount field
	// 2) Fixed + additional - one fixed donation amount with an additional editable donation amount field
	// 3) Per Item - Fixed donation amount per item witn an additional editable donation amount field
	*/	
	global $wpdb;
	global $cpDonations_table;
	
	if ($id != "-1") {
		$cpDonation = $wpdb->get_row( "SELECT * FROM $cpDonations_table WHERE Id = '$id'" );
	}
	else {
		$cpDonation = $wpdb->get_row( "SELECT * FROM $cpDonations_table WHERE slug = '$cpDonationName'" );
	}
	
	if($cpDonation != null) {
	
	$buttonStyle = "https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif";
	
	switch(get_option('cpDonations_buttonStyle')) {
		case "small":
			$buttonStyle = "<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' style='width: 74px;' border='0' name='submit' class='paypalSubmit' alt=''>";
			break;
		case "withCC":
			$buttonStyle = "<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif' style='width: 147px;' border='0' name='submit' class='paypalSubmit' alt=''>";
			break;
		default:
			$buttonStyle = "<input type='image' src='https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif' style='width: 92px;' border='0' name='submit' class='paypalSubmit' alt=''>";
			break;
	}
	
	$businessName = get_option("cpDonations_Business_Name");
	$returnURLMarkup = (get_option("cpDonations_returnUrl") == "") ? "" : "<input type='hidden' name='return' value='".get_option("cpDonations_returnUrl")."' />"; 
	$defaultDonation = $cpDonation->defaultdonation;
	$donationType = $cpDonation->donationtype;
	$maxItems = ($cpDonation->maxitems == null) ? 1 : $cpDonation->maxitems;
	$options = "";
	for($i=1;$i<=$maxItems;$i++) {
		$options .= "<option>".$i."</option>";	
	}
	$quantity = "<select name='quantity' class='quantity'>".$options."</select>";
	
	$customForm = "<p class='donate_amount'><label class='cp-donation' for='amount'>Your Donation Amount:</label><br /><input type='text' name='amount' class='amount' value='".$defaultDonation."' /></p>";
	
	switch($donationType) {
		case 2:
			$customForm = "<p class='donate_amount'><label class='cp-donation' class='cp-donation' for='amount'>Fixed Donation Amount:</label> <span class='fixed-amount'>".$defaultDonation."</span><br />
			<input type='hidden' name='amount' class='amount' value='".$defaultDonation."' />\n
			<label class='cp-donation' for='amount2'>Additional Donation:</label><br /><input type='text' name='amount2' class='amount2' /></p>";
			break;
		case 3:
			$customForm = "<p class='donate_amount'><label class='cp-donation' for='amount'>Price per item:</label> <span class='fixed-amount'>".$defaultDonation."</span><br />
			<label class='cp-donation' for='quantity'>Number of items:</label> ".$quantity."<br />
			<label class='cp-donation' for='amount2'>Additional Donation:</label> <input type='text' name='amount2' class='amount2' /><input type='hidden' name='amount' class='amount' value='".$defaultDonation."' /></p>";
			break;
		default:
			break;
	}
	
	$form = "<style>input {width: 100%;}</style><!-- Custom Post Donations - http://labs.hahncreativegroup.com/wordpress-paypal-plugin/ --><div><form class='cpDonation' action='https://www.paypal.com/cgi-bin/webscr' method='post'>".
		"<input type='hidden' class='cmd' name='cmd' value='_donations'>".
		$customForm.
		"<p>Your total amount is : <span class='total_amt'>".$defaultDonation."</span> <small>(Currency: USD)</small></p>".
		"<input type='hidden' name='item_name' value='".$cpDonation->name."'>".
		$returnURLMarkup.
		"<input type='hidden' name='business' value='".$businessName."'>".
		"<input type='hidden' name='lc' value='US'>".
		"<input type='hidden' name='no_note' value='1'>".
		"<input type='hidden' name='no_shipping' value='1'>".
		"<input type='hidden' name='rm' value='1'>".
		"<input type='hidden' name='currency_code' value='USD'>".
		"<input type='hidden' name='bn' value='PP-DonationsBF:btn_donateCC_LG.gif:NonHosted'>".
		"<p class='submit'>".$buttonStyle."".
		"<img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'></p>".
		"</form></div><!-- Custom Post Donations -->";		
						
		$restrict = get_option('cpDonations_restrictToPagePost');
		if(($restrict == "true") && (is_single() || is_page())) {
			return $form;	
		}
		else if($restrict == "false") {
			return $form;
		}
		else {
			return "Read article for donation information.";
		}
	}
	else {
		return "";	
	}	
}

function cpDonation_Handler($atts) {
	$atts = shortcode_atts( array( 'id' => '-1', 'key' => '-1'), $atts );
	return createCPDonationForm($atts['id'], $atts['key']);
}
add_shortcode('cpDonation', 'cpDonation_Handler');

// Taken from Google XML Sitemaps from Arne Brachhold
function add_cpDonations_plugin_links($links, $file) {	
	if ( $file == plugin_basename(__FILE__) ) {			
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AZLPGKSCJBPKS">' . __('Donate', 'cpDonations') . '</a>';			
	}
	return $links;
}
	
//Add the extra links on the plugin page
add_filter('plugin_row_meta', 'add_cpDonations_plugin_links', 10, 2);

add_action( 'init', 'cpd_code_button' );
function cpd_code_button() {
    add_filter( "mce_external_plugins", "cpd_code_add_button" );
    add_filter( 'mce_buttons', 'cpd_code_register_button' );
}
function cpd_code_add_button( $plugin_array ) {
    $plugin_array['cpdbutton'] = $dir = plugins_url( 'scripts/shortcode.js', __FILE__ );
    return $plugin_array;
}
function cpd_code_register_button( $buttons ) {
    array_push( $buttons, 'cpdselector' );
    return $buttons;
}

//Donation Widget
class CP_Donation_Widget extends WP_Widget {
	//register widget
	function __construct() {
		parent::__construct(
			'cp_donation_widget',
			__('CP Donations Widget', 'custom-post-donations-pro'),
			array('description' => __('Simple Donation Widget', 'custom-post-donations-pro'), )
		);
	}
	
	//front-end
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( !empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		if ( empty( $instance['default_donation'] ) ||  empty( $instance['item_name'] )) {
			$instance['default_donation'] = '5.00';
			$instance['item_name'] = 'Donation';
		}
		
			$buttonStyle = "<input type='image' src='https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif' style='width: 92px;' border='0' name='submit' class='paypalSubmit' alt='' onclick='return false;'>";
	
			switch(get_option('cpDonations_buttonStyle')) {
				case "small":
					$buttonStyle = "<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif' style='width: 74px;' border='0' name='submit' class='paypalSubmit' alt='' onclick='return false;'>";
					break;
				case "withCC":
					$buttonStyle = "<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif' style='width: 147px;' border='0' name='submit' class='paypalSubmit' alt='' onclick='return false;'>";
					break;
				default:
					$buttonStyle = "<input type='image' src='https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif' style='width: 92px;' border='0' name='submit' class='paypalSubmit' alt='' onclick='return false;'>";
					break;
			}
	
			echo "<div><form class='cpDonation' action='https://www.paypal.com/cgi-bin/webscr' method='post'>".
					"<input type='hidden' class='cmd' name='cmd' value='_donations'>".
					"<p class='donate_amount'><label class='cp-donation' for='amount'>Your Donation Amount:</label><input type='text' class='cp-donation amount' name='amount' value='".$instance['default_donation']."' /></p>\n".
					"<p>Your total amount is : <span class='total_amt'>".$instance['default_donation']."</span> <small>(Currency: USD)</small></p>".
					"<input type='hidden' name='cp_quantity' class='cp_quantity' value='0' />".
					"<input type='hidden' name='item_name' class='item_name' value='".$instance['item_name']."'>".
					"<input type='hidden' name='business' value='".get_option("cpDonations_Business_Name")."'>".
					"<input type='hidden' name='no_note' value='1'>".
					"<input type='hidden' name='no_shipping' value='0'>".
					"<input type='hidden' name='rm' value='1'>".
					"<input type='hidden' name='currency_code' value='USD'>".
					"<input type='hidden' name='bn' value='PP-DonationsBF:btn_donateCC_LG.gif:NonHosted'>".
					"<p class='submit'>".$buttonStyle."".
					"<img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'></p>".
				"</form></div>";
		
		
		echo $args['after_widget'];
	}
	
	//back-end
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		else {
			$title = __( 'Donation', 'custom-post-donations-pro' );
		}
		if ( isset( $instance['default_donation'] ) ) {
			$default_donation = $instance['default_donation'];
		}
		else {
			$default_donation = __( '5.00', 'custom-post-donations-pro' );
		}
		if ( isset( $instance['item_name'] ) ) {
			$item_name = $instance['item_name'];
		}
		else {
			$item_name = __( 'Donation', 'custom-post-donations-pro' );
		}
		?>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		<label for="<?php echo $this->get_field_id( 'default_donation' ); ?>"><?php _e( 'Default Donation Amount:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'default_donation' ); ?>" name="<?php echo $this->get_field_name( 'default_donation' ); ?>" type="text" value="<?php echo esc_attr( $default_donation ); ?>">
		<label for="<?php echo $this->get_field_id( 'item_name' ); ?>"><?php _e( 'Donation name/reason:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'item_name' ); ?>" name="<?php echo $this->get_field_name( 'item_name' ); ?>" type="text" value="<?php echo esc_attr( $item_name ); ?>">
		<?php
	}
	
	//sanitize form values when updated
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['default_donation'] = ( !empty( $new_instance['default_donation'] ) ) ? strip_tags( $new_instance['default_donation'] ) : '';
		$instance['item_name'] = ( !empty( $new_instance['item_name'] ) ) ? strip_tags( $new_instance['item_name'] ) : '';
		
		return $instance;
	}
}
function register_cp_donation_widget() {
	register_widget( 'CP_Donation_Widget' );
}
add_action( 'widgets_init', 'register_cp_donation_widget' );
?>