<?php
/*
Plugin Name: Custom Post Donations
Plugin URI: http://labs.hahncreativegroup.com/wordpress-plugins/custom-post-donations/
Description: This WordPress plugin will allow you to create unique customized PayPal donation widgets on WordPress posts or pages and accept donations. Creates custom PayPal donation widgets.
Author: HahnCreativeGroup
Version: 1.6
Author URI: http://labs.HahnCreativeGroup.com/
*/

global $cpDonations_table;
global $cpDonations_plugin_db_version;
global $wpdb;
$cpDonations_table = $wpdb->prefix . 'cp_donations';
$cpDonations_plugin_db_version = '1.2';

register_activation_hook( __FILE__,  'cpDonations_install' );

function cpDonations_install() {
  global $wpdb;
  global $cpDonations_table;
  global $hcg_presspage_plugin_db_version;

  if ( $wpdb->get_var( "show tables like '$cpDonations_table'" ) != $cpDonations_table ) {
			
	$sql = "CREATE TABLE $cpDonations_table (".
		"Id INT NOT NULL AUTO_INCREMENT, ".
		"name VARCHAR( 30 ) NOT NULL, ".
		"slug VARCHAR( 30 ) NOT NULL, ".
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

// Create Admin Panel
function add_cpDonations_menu()
{
	add_menu_page(__('CP Donations','menu-cpDonations'), __('CP Donations','menu-cpDonations'), 'manage_options', 'cpDonations-admin', 'showCpDonationsMenu' );
	
	// Add a submenu to the custom top-level menu:
	add_submenu_page('cpDonations-admin', __('CP Donations >> Add Page','menu-cpDonations'), __('Add Donation','menu-cpDonations'), 'manage_options', 'add-cpDonation', 'add_cpDonation');
}

add_action( 'admin_menu', 'add_cpDonations_menu' );

function showCpDonationsMenu()
{
	include("admin/overview.php");
}

function add_cpDonation()
{
	include("admin/add-cpDonation.php");
}

function add_jquery_cpDonation() {
	wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'add_jquery_cpDonation');

function createCPDonationForm($cpDonationName) {
	/*
	// Donation types:
	// 1) Standard - one editable donation amount field
	// 2) Fixed + additional - one fixed donation amount with an additional editable donation amount field
	// 3) Per Item - Fixed donation amount per item witn an additional editable donation amount field
	*/
	
	global $wpdb;
	global $cpDonations_table;
	
	$cpDonation = $wpdb->get_row( "SELECT * FROM $cpDonations_table WHERE slug = '$cpDonationName'" );
	
	if($cpDonation != null) {
	
	$businessName = get_option("cpDonations_Business_Name");
	$defaultDonation = $cpDonation->defaultdonation;
	$donationType = $cpDonation->donationtype;
	$maxItems = ($cpDonation->maxitems == null) ? 1 : $cpDonation->maxitems;
	$options = "";
	for($i=1;$i<=$maxItems;$i++) {
		$options .= "<option>".$i."</option>";	
	}
	$quantity = "<select name='quantity' id='quantity'>".$options."</select>";
	
	$customForm = "<p class='donate_amount'><label for='amount'>Your Donation Amount:</label><br /><input type='text' name='amount' id='amount' value='".$defaultDonation."' /></p>";
	
	switch($donationType) {
		case 2:
			$customForm = "<p class='donate_amount'><label for='amount'>Fixed Donation Amount:</label> <span id='fixed-amount'>".$defaultDonation."</span><br />
			<input type='hidden' name='amount' id='amount' value='".$defaultDonation."' />\n
			<label for='amount2'>Additional Donation:</label><br /><input type='text' name='amount2' id='amount2' /></p>";
			break;
		case 3:
			$customForm = "<p class='donate_amount'><label for='amount'>Price per item:</label> <span id='fixed-amount'>".$defaultDonation."</span><br />
			<label for='quantity'>Number of items:</label> ".$quantity."<br />
			<label for='amount2'>Additional Donation:</label> <input type='text' name='amount2' id='amount2' /><input type='hidden' name='amount' id='amount' value='".$defaultDonation."' /></p>";
			break;
		default:
			break;
	}
	
	$form = "<div><form id='cpDonation' action='https://www.paypal.com/cgi-bin/webscr' method='post'>".
		"<input type='hidden' id='cmd' name='cmd' value='_donations'>".
		$customForm.
		"<p>Your total amount is : <span id='total_amt'>".$defaultDonation."</span> <small>(Currency: USD)</small></p>".
		"<input type='hidden' name='item_name' value='".$cpDonation->name."'>".
		"<input type='hidden' name='business' value='".$businessName."'>".
		"<input type='hidden' name='lc' value='US'>".
		"<input type='hidden' name='no_note' value='1'>".
		"<input type='hidden' name='no_shipping' value='1'>".
		"<input type='hidden' name='rm' value='1'>".
		"<input type='hidden' name='currency_code' value='USD'>".
		"<input type='hidden' name='bn' value='PP-DonationsBF:btn_donateCC_LG.gif:NonHosted'>".
		"<p class='submit'><input type='image' src='https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif' border='0' name='submit' alt=''>".
		"<img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'></p>".
		"</form></div>";
		
		$js = "\n<script type='text/javascript'>".
			"jQuery(document).ready(function(){".
					"var defaultDonation = jQuery('#total_amt').html();".
					"jQuery('#amount').keydown(function(e){if(!((e.keyCode > 47 && e.keyCode < 59) || ( e.keyCode >= 96 && e.keyCode <= 105 ||  e.keyCode == 110 || e.keyCode == 190) || e.keyCode == 8)){e.preventDefault();}".
												"});".
				"//Type 1
				jQuery('#amount').keyup(function(){												   
				  var donation = jQuery('#amount').val();
				  if(!isNaN(donation)){
					  var total = donation;
					  jQuery('#amount').val(total);
					  jQuery('#total_amt').html(total);
				  }else{
					  jQuery('#total_amt').html(defaultDonation);
				  }		  
				  
			});".
				
				"//Type 2
				jQuery('#amount2').keyup(function(){ 	  
				  var donation = parseFloat(jQuery('#fixed-amount').html());
				  var quantity = (parseFloat(jQuery('option:selected').val()) > 1) ? parseFloat(jQuery('option:selected').val()) : 1;
				  var additionalDonation = parseFloat(jQuery('#amount2').val());
				  if(!isNaN(additionalDonation)){
					  var total = additionalDonation+(donation*quantity);
					  jQuery('#amount').val(total);
					  jQuery('#total_amt').html(total);
				  }else{
					  jQuery('#total_amt').html(defaultDonation);
				  }	
			});".
			"
			//Type 3
			jQuery('#quantity').change(function(){ 
				  jQuery('#amount2').val('');						
				  var quantity = parseFloat(jQuery(this).val());
				  if(!isNaN(quantity)){
					  var total = quantity*parseFloat(jQuery('#fixed-amount').html());
					  jQuery('#amount').val(total);
					  jQuery('#total_amt').html(total);
				  }else{
					  jQuery('#total_amt').html(defaultDonation);
				  }	
			});
		});\n</script>";
		
		if(is_single() || is_page()) {
			return $form.$js;
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
	return createCPDonationForm($atts[id]);
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
?>