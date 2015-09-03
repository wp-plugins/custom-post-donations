<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;
global $cpDonations_table;

if(isset($_POST['cpDonationBusiness']) && $_POST['cpDonationBusiness'] != "Enter Email Address") {
	if(check_admin_referer('cp_donation','cp_donation')) {
		$restrict = isset($_POST['cpDonationsRestrictToPagePost']) ? 'true' : 'false';
		update_option("cpDonations_Business_Name", $_POST['cpDonationBusiness']);
		update_option("cpDonations_returnUrl", $_POST['cpDonationReturnURL']);
		update_option("cpDonations_buttonStyle", $_POST['cpDonationButtonStyle']);
		update_option("cpDonations_restrictToPagePost", $restrict);
		?>  
	  <div class="updated"><p><strong><?php _e('Options have been updated.' ); ?></strong></p></div>  
	  <?php
	}
}
else if (get_option("cpDonations_Business_Name") == "") {
	?>  
    <div class="updated"><p><strong><?php _e('Please enter your PayPal email address.' ); ?></strong></p></div>  
    <?php	
}

if(isset($_POST['cpDonationId'])) {	
	if(check_admin_referer('cp_donation','cp_donation')) {
	  $wpdb->query( "DELETE FROM $cpDonations_table WHERE Id = '".$_POST['cpDonationId']."'" );
		  
	  ?>  
	  <div class="updated"><p><strong><?php _e('CP Donation Form has been deleted.' ); ?></strong></p></div>  
	  <?php
	}
}
$cpDonationButtonStyle = get_option("cpDonations_buttonStyle");
$cpDonationWidgets = $wpdb->get_results( "SELECT * FROM $cpDonations_table" );
?>
<div class='wrap cp-donations'>
	<h2>Custom Post Donation Widgets</h2>
    <p style="float: left;">This is a listing of all CP Donation Forms.</p>
    <p style="float: right; font-weight: bold; font-style: italic;"><a href="http://labs.hahncreativegroup.com/wordpress-paypal-plugin/?src=cpd">Try Custom Post Donations Premium</a></p>
    <table class="widefat post fixed">
    	<thead>
        <tr>
        	<th>CP Donation Name</th>
            <th>CP Donation Short Code</th>
            <th>Description</th>
            <th></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>CP Donation Name</th>
            <th>CP Donation Short Code</th>
            <th>Description</th>
            <th></th>
        </tr>
        </tfoot>
        <tbody>
        	<?php foreach($cpDonationWidgets as $widget) { ?>				
            <tr>
            	<td><?php _e($widget->name); ?></td>
                <td><input type="text" size="25" value="[cpDonation key='<?php _e($widget->Id); ?>']" /></td>
                <td><?php _e($widget->description); ?></td>
                <td class="major-publishing-actions right">
                <form name="delete_page_<?php _e($widget->Id); ?>" method ="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                	<input type="hidden" name="cpDonationId" value="<?php _e($widget->Id); ?>" />
                    <?php wp_nonce_field('cp_donation','cp_donation'); ?>
                    <input type="submit" name="Submit" class="button-primary" value="Delete Donation Widget" />
                </form>
                </td>
            </tr>
			<?php } ?>
        </tbody>
     </table>
     <h2>Custom Post Donation Settings</h2>
     <form name="cpDonation_Settings" method ="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
     <?php wp_nonce_field('cp_donation','cp_donation'); ?>      
     <table class="widefat post fixed">
    	<thead>
        <tr>
        	<th>Attribute</th>
            <th>Value</th>
            <th>Description</th>            
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>Attribute</th>
            <th>Value</th>
            <th>Description</th>
        </tr>
        </tfoot>
        <tbody>        				
            <tr>                
                <td>CP Donation Business Name</td>
                <td><input type="text" name="cpDonationBusiness" size="37" value="<?php _e(get_option("cpDonations_Business_Name")); ?>" /></td>
                <td>Enter the email address associated with the PayPal account donation will be made to.</td>                
            </tr>
            <tr>
                <td><?php _e('CP Donation Global Return URL', 'custom-post-donations'); ?></td>
                <td><input type="text" name="cpDonationReturnURL" size="37" value="<?php _e(get_option("cpDonations_returnUrl")); ?>" /></td>
                <td><?php _e('Enter the default thank you page URL associated with all CP Donation forms.', 'custom-post-donations'); ?></td>                
            </tr>
			<tr>
                <td><?php _e('Restrict to Pages/Posts', 'custom-post-donations-pro'); ?></td>
                <td><input type="checkbox" name="cpDonationsRestrictToPagePost" value="true" <?php if (get_option("cpDonations_restrictToPagePost") == "true") { echo "checked"; } ?>/></td>
                <td><?php _e('Uncheck if you would like donation forms to show on archive pages as well as pages and posts.', 'custom-post-donations-pro'); ?></td>                
            </tr>
			<tr>
                <td><?php _e('CP Donation Global Button Type', 'custom-post-donations'); ?></td>
                <td><ul>
					<li><input type="radio" name="cpDonationButtonStyle" value="default" <?php if ($cpDonationButtonStyle == "default") { echo "checked"; } ?>> <img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal - The safer, easier way to pay online!"></li>
					<li><input type="radio" name="cpDonationButtonStyle" value="small" <?php if ($cpDonationButtonStyle == "small") { echo "checked"; } ?>> <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" alt="PayPal - The safer, easier way to pay online!"></li>
					<li><input type="radio" name="cpDonationButtonStyle" value="withCC" <?php if ($cpDonationButtonStyle == "withCC") { echo "checked"; } ?>> <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" alt="PayPal - The safer, easier way to pay online!"></td></li>
					</ul>
                <td><?php _e('Select which button will show for all CP Donation forms.', 'custom-post-donations'); ?></td>                
            </tr>
            <tr>
            	<td class="major-publishing-actions"><input type="submit" name="Submit" class="button-primary" value="Save Donation Settings" /></td>
                <td></td>
				<td></td>
            </tr>			
        </tbody>
     </table>
     </form>
     <br />
     <table class="widefat post fixed">
    	<thead>
        <tr>
        	<th>Please Consider Supporting this Plugin by Donating</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th></th>
        </tr>
        </tfoot>
        <tbody>        				
            <tr>
            <td><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=YEAT8SE2TXE3S" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" alt="PayPal - The safer, easier way to pay online!"></a><img alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></td>            
            </tr>
            </tbody>
            </table>
     <p><a href="http://labs.hahncreativegroup.com/wordpress-paypal-plugin/?src=cpd" target="_blank"><img src="http://labs.hahncreativegroup.com/wp-content/uploads/2015/09/CustomPostDonationsPro-Banner.gif" width="400" height="60" alt="Custom Post Donations Pro" /></a></p>
     <h2><a href="http://labs.hahncreativegroup.com/wordpress-paypal-plugin/?src=cpd" target="_blank">Upgrade to the Premium Version</a></h2>
     <ul>        
        <li>New 'Campaign' donation type captures name, address, employer and occupation - follows Federal Election Commission (FEC) regulations</li>
        <li>Now supports multiple currencies</li>
        <li>Add customized donation forms to your posts or pages</li>
        <li>Designate alternate PayPal accounts for donations</li>
        <li>Add donation form titles</li>
        <li>Manage multiple donation forms from the easy access admin interface</li>
        <li>Ability to edit donation widgets</li>
     </ul>
     <strong><a href="http://labs.hahncreativegroup.com/wordpress-paypal-plugin/?src=cpd" target="_blank">Upgrade to the Premium Version</a></strong>
     <hr />
     <h3>Try also - <a href="http://labs.hahncreativegroup.com/wordpress-gallery-plugin/?src=cpd" target="_blank">WP Easy Gallery Pro</a></h3>
     <p>WP Easy Gallery allows you to manage multiple image galleries through an easy to use admin interface.</p>     
</div>