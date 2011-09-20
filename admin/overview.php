<?php
global $wpdb;

if(isset($_POST['cpDonationBusiness']) && $_POST['cpDonationBusiness'] != "Enter Email Address") {
	update_option("cpDonations_Business_Name", $_POST['cpDonationBusiness']);	
}
else if (get_option("cpDonations_Business_Name") == "") {
	?>  
    <div class="updated"><p><strong><?php _e('Please enter your PayPal email address.' ); ?></strong></p></div>  
    <?php	
}

if(isset($_POST['cpDonationId'])) {	
	$wpdb->query( "DELETE FROM wp_cp_donations WHERE Id = '".$_POST['cpDonationId']."'" );
		
	?>  
	<div class="updated"><p><strong><?php _e('CP Donation Form has been deleted.' ); ?></strong></p></div>  
	<?php	
}

$cpDonationWidgets = $wpdb->get_results( "SELECT * FROM wp_cp_donations" );
?>
<div class='wrap'>
	<h2>Custom Post Donation Widgets</h2>
    <p>This is a listing of all CP Donation Forms.</p>
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
     <hr />
     <br /><br />     
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
     <br />     
     <table class="widefat post fixed" cellspacing="0">
    	<thead>
        <tr>
        	<th>WordPress Blogging Tutorials and Resources</th>
            <th></th>                       
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>WordPress Blogging Tutorials and Resources</th>
            <th></th>
        </tr>
        </tfoot>
        <tbody>        				
            <tr>
            <td><a href="http://4ef7fcjby1v1mmaqtgrfsiv92v.hop.clickbank.net/" target="_blank"><img src="../wp-content/plugins/custom-post-donations/admin/images/seopressor.jpg" border="0" alt="" /></a><p><a href="http://4ef7fcjby1v1mmaqtgrfsiv92v.hop.clickbank.net/" target="_blank">SEOPressor simplifies On-Page SEO and helps Skyrocket your website into major Search Engines</a>.</p></td>            
            <td><a href="http://27819begx2p0jf02zmh2ly4cbz.hop.clickbank.net/" target="_blank"><img src="../wp-content/plugins/custom-post-donations/admin/images/blogsuccessacademy.jpg" border="0" alt="Blog Success Academy" /></a></td>       
            </tr>            
            </tbody>
            </table>
     </form>
</div>