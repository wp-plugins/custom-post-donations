<?php
global $wpdb;

// add new donation widget
if(isset($_POST['cpDonation_add']))
	{
		if($_POST['cpDonationName'] != "") {
			if($_POST['cpDonationType'] > 2 && $_POST['cpDonationMaxItems'] == null) {
			  $cpDonationName = $_POST['cpDonationName'];
			  $publicationDate = $_POST['publicationDate'];	  
			  $slug = strtolower(str_replace(" ", "", $_POST['cpDonationName']));
			  $cpDonationDescription = $_POST['cpDonationDescription'];
			  $cpDonationAmount = $_POST['cpDonationAmount'];
			  $cpDonationType = $_POST['cpDonationType'];			  
			  $cpDonationMaxItems = $_POST['cpDonationMaxItems'];
				?>  
			  <div class="updated"><p><strong><?php _e('Please enter a maximum item number.' ); ?></strong></p></div>  
			  <?php
			}
			else {			  
			  
			  global $wpdb;
			  
			  $cpDonationName = $_POST['cpDonationName'];
			  $publicationDate = $_POST['publicationDate'];	  
			  $slug = strtolower(str_replace(" ", "", $_POST['cpDonationName']));
			  $cpDonationDescription = $_POST['cpDonationDescription'];
			  $cpDonationAmount = $_POST['cpDonationAmount'];
			  $cpDonationType = $_POST['cpDonationType'];
			  $cpDonationMaxItems = ($_POST['cpDonationMaxItems'] != null) ? $_POST['cpDonationMaxItems'] : 1;
			  $donationAdded = $wpdb->insert( 'wp_cp_donations', array( 'name' => $cpDonationName, 'slug' => $slug, 'description' => $cpDonationDescription, 'donationtype' => $cpDonationType, 'defaultdonation' => $cpDonationAmount, 'maxitems' => $cpDonationMaxItems ) );
			  
			  if($donationAdded) {
			  ?>  
			  <div class="updated"><p><strong><?php _e('CP Donation Widget Added.' ); ?></strong></p></div>  
			  <?php
			  }		
			
			else {
				?>  
			  <div class="updated"><p><strong><?php _e('Please enter a widget name.' ); ?></strong></p></div>  
			  <?php
			}
			}			
		}
	}
?>
<div class='wrap'>
	<h2>Add Donation - Create custom donation widget</h2>
    <form name="add_cpdonation_form" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
    <input type="hidden" name="cpDonation_add" value="true" />
    <table class="widefat post fixed" cellspacing="0">
    	<thead>
        <tr>
        	<th width="250">Field Name</th>
            <th>Value</th>
            <th>Description</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>Field Name</th>
            <th>Value</th>
            <th>Description</th>
        </tr>
        </tfoot>
        <tbody>
        	<tr>
            	<td><strong>Enter Unique Widget Name:</strong></td>
                <td><input type="text" size="30" name="cpDonationName" value="<?php echo $cpDonationName; ?>" /></td>
                <td>This name is the internal name for the donation widget.<br />Please avoid non-letter characters such as ', ", *, etc.</td>
            </tr>
            <tr>
            	<td><strong>Enter Widget Description:</strong></td>
                <td><input type="text" size="50" name="cpDonationDescription" value="<?php echo $cpDonationDescription; ?>" /></td>
                <td>This name is the internal description for the donation widget.</td>
            </tr>
            <tr>
            	<td><strong>Enter Default/per Item Amount:</strong></td>
                <td><input type="text" size="30" name="cpDonationAmount" value="<?php echo $cpDonationAmount; ?>" /></td>
                <td>This is the default donation or per item price.</td>
            </tr>
            <tr>
            	<td><strong>Select Widget Type:</strong></td>
                <td>
                  <select name="cpDonationType">
                  	<option value="1">Standard Donation</option>
                    <option value="2">Fixed + Additional</option>
                    <option value="3">Per Item + Additional</option>
                  </select>
                </td>
                <td>
                	This determines what type of widget appears in the post or page.
                    <ol>
                    	<li>Fixed Donation - one editable donation amount field</li>
                        <li>Fixed + Additional - one fixed donation amount with an additional editable donation amount field</li>
                        <li>Per Item + Additional - Fixed donation amount per item witn an additional editable donation amount field</li>
                    </ol>
                </td>
            </tr>
            <tr>
            	<td><strong>Enter Maximum Number of Items:</strong></td>
                <td><input type="text" size="30" name="cpDonationMaxItems" value="<?php echo $cpDonationMaxItems; ?>" /></td>
                <td>This is will set the maximum number of items that can be selected in a per item widget.</td>
            </tr>            
            <tr>
            	<td class="major-publishing-actions"><input type="submit" name="Submit" class="button-primary" value="Create Donation Widget" /></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
	</table>    
    </form>
    <br />
     <h2><a href="http://labs.hahncreativegroup.com/wordpress-plugins/custom-post-donations-pro/">Upgrade to the Pro Version</a></h2>
</div>