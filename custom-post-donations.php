<?php
/*
Plugin Name: Custom Post Donations
Plugin URI: http://labs.hahncreativegroup.com/wordpress-paypal-plugin/
Description: This WordPress plugin will allow you to create unique customized PayPal donation widgets on WordPress posts or pages and accept donations. Creates custom PayPal donation widgets.
Author: HahnCreativeGroup
Version: 3.9
Author URI: http://labs.hahncreativegroup.com/wordpress-plugins/custom-post-donations/
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
			$buttonStyle = "https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif";
			break;
		case "withCC":
			$buttonStyle = "https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif";
			break;
		default:
			$buttonStyle = "https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif";
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
	
	$form = "<!-- Custom Post Donations 3.8 - http://labs.hahncreativegroup.com/wordpress-paypal-plugin/ --><div><form class='cpDonation' action='https://www.paypal.com/cgi-bin/webscr' method='post'>".
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
		"<p class='submit'><input type='image' src='".$buttonStyle."' border='0' name='submit' class='paypalSubmit' alt=''>".
		"<img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'></p>".
		"</form></div><!-- Custom Post Donations 3.8 -->";		
						
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
?>