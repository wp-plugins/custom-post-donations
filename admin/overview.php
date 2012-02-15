<?php
global $wpdb;
global $cpDonations_table;

if(isset($_POST['cpDonationBusiness']) && $_POST['cpDonationBusiness'] != "Enter Email Address") {
	update_option("cpDonations_Business_Name", $_POST['cpDonationBusiness']);	
}
else if (get_option("cpDonations_Business_Name") == "") {
	?>  
    <div class="updated"><p><strong><?php _e('Please enter your PayPal email address.' ); ?></strong></p></div>  
    <?php	
}

if(isset($_POST['cpDonationId'])) {	
	$wpdb->query( "DELETE FROM $cpDonations_table WHERE Id = '".$_POST['cpDonationId']."'" );
		
	?>  
	<div class="updated"><p><strong><?php _e('CP Donation Form has been deleted.' ); ?></strong></p></div>  
	<?php	
}

$cpDonationWidgets = $wpdb->get_results( "SELECT * FROM $cpDonations_table" );
?>
<div class='wrap'>
	<h2>Custom Post Donation Widgets</h2>
    <p style="float: left;">This is a listing of all CP Donation Forms.</p>
    <p style="float: right;">Try also - <a href="http://labs.hahncreativegroup.com/wordpress-plugins/wp-easy-gallery-pro-simple-wordpress-gallery-plugin/?src=cpd">WP Easy Gallery Pro</a></p>
    <table class="widefat post fixed" cellspacing="0">
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
            	<td><?php echo $widget->name; ?></td>
                <td><input type="text" size="40" value="[cpDonation id='<?php echo $widget->slug; ?>']" /></td>
                <td><?php echo $widget->description; ?></td>
                <td align="right" class="major-publishing-actions">
                <form name="delete_page_<?php echo $widget->Id; ?>" method ="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                	<input type="hidden" name="cpDonationId" value="<?php echo $widget->Id; ?>" />
                    <input type="submit" name="Submit" class="button-primary" value="Delete Donation Widget" />
                </form>
                </td>
            </tr>
			<?php } ?>
        </tbody>
     </table>
     <h2>Custom Post Donation Settings</h2>
     <form name="cpDonation_Settings" method ="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">      
     <table class="widefat post fixed" cellspacing="0">
    	<thead>
        <tr>
        	<th>CP Donation Business Name</th>
            <th>Description</th>            
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>CP Donation Business Name</th>
            <th>Description</th>
        </tr>
        </tfoot>
        <tbody>        				
            <tr>
                <td><input type="text" name="cpDonationBusiness" size="50" value="<?php echo get_option("cpDonations_Business_Name"); ?>" /></td>
                <td>Enter the email address associated with the PayPal account donation will be made to.</td>                
            </tr>
            <tr>
            	<td class="major-publishing-actions"><input type="submit" name="Submit" class="button-primary" value="Save Donation Settings" /></td>
                <td></td>
            </tr>			
        </tbody>
     </table>
     <br />
     <table class="widefat post fixed" cellspacing="0">
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
            <td><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YEAT8SE2TXE3S" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"></a><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></td>            
            </tr>
            </tbody>
            </table>
     <p><a href="http://labs.hahncreativegroup.com/wordpress-plugins/custom-post-donations-pro/?src=cpd"><img src="http://labs.hahncreativegroup.com/wp-content/uploads/2011/10/CustomPostDonationsPro-Banner.gif" width="374" height="60" border="0" alt="Custom Post Donations Pro" /></a></p>
     <h2><a href="http://labs.hahncreativegroup.com/wordpress-plugins/custom-post-donations-pro/?src=cpd">Upgrade to the Pro Version</a></h2>
     <ul>        
        <li>New 'Campaign' donation type captures name, address, employer and occupation - follows Federal Election Commission (FEC) regulations</li>
        <li>Now supports multiple currencies</li>
        <li>Add customized donation forms to your posts or pages</li>
        <li>Designate alternate PayPal accounts for donations</li>
        <li>Add donation form titles</li>
        <li>Manage multiple donation forms from the easy access admin interface</li>
        <li>Ability to edit donation widgets</li>
     </ul>
     <strong><a href="http://labs.hahncreativegroup.com/wordpress-plugins/custom-post-donations-pro/?src=cpd">Upgrade to the Pro Version</a></strong>
     <hr />
     <h3>Try also - <a href="http://labs.hahncreativegroup.com/wordpress-plugins/wp-easy-gallery-pro-simple-wordpress-gallery-plugin/?src=cpd">WP Easy Gallery Pro</a></h3>
     <p>WP Easy Gallery allows you to manage multiple image galleries through an easy to use admin interface.</p>
     </form>
</div>