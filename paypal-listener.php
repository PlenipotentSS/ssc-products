<?
//correct use for this listener:
//https://www.savoyswing.org/wp-content/plugins/products/paypal-listener.php?wps-listener=paypal
require_once('../../../wp-load.php');
//wp_mail("president@savoyswing.org", "IPN Received", "Listener Initial Received!: From: Paypal Email (".$_POST['receiver_email'].")", 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>');
function getUserDetails($member_uid ) {
	$arr =  get_users();
	foreach ( $arr as &$x ) {
	    $id = $x->ID;
	    if ( $id == 1 ) {
	        continue;
	    }
	    $usr_data = get_userdata( $id);
		$one = '/'.trim($member_uid).'/';
		$two = '/'.trim($usr_data->unique_id).'/';
	 	if ( preg_match($one, $two) ){
			$email = $usr_data->user_email;
			$name = $usr_data->first_name . " " . $usr_data->last_name;
			$status = $usr_data->status;
			$act_date = $usr_data->act_date;
			$exp_date = $usr_data->exp_date;
			$paypal_last_txn = $usr_data->paypal_last_txn;
			return array( $id, $email, $name, $status, $act_date, $exp_date, $paypal_last_txn);
		}
	}
}

function validPaymentAmount($amount, $type) {
	if ($type == 'Regular' && $amount == '60.00') {
		return true;
	} else if ($type == 'Student' && $amount == '40.00') {
		return true;
	} else if ($type == 'Senior' && $amount == '40.00') {
		return true;
	} 
	return false;
}

function emailMembershipCoordinator($id, $name, $email, $status, $act_date, $next_exp_date, $member_uid, $subscription_title, $amount) {
	$to = "membership@savoyswing.org";

	// email information
	$subject = __('Payment Receipt : Savoy Swing Club New Membership!', 'Stripe');
	$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
	$message = "Dear Membership Coordinator,\n\n";
	$message .= "There is a new membership payment. See below for details: \n\n";
	$message .= "Invoice ID:			  	 	 " .$id . "\n";
	$message .= "Member Name:				 " . $name . "\n";
	$message .= "Member ID: 			 		" . $member_uid . "\n";
	$message .= "Member Email:				 " . $email . "\n";
	$message .=	"Activation Date:				 " . $act_date . "\n";
	$message .= "Subscription Purchased:		 ". $subscription_title."		Expires: ".$next_exp_date."\n";

	wp_mail($to, $subject, $message, $headers);
}

if(isset($_GET['wps-listener']) && $_GET['wps-listener'] == 'paypal' && $_POST['receiver_email'] == 'treas@savoyswing.com') {
 	//echo "Listener Activated...\n";
	//wp_mail("president@savoyswing.org", "IPN Process Started", "Listener Activated Successfully!", 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>');
 	
	// Read the notification from PayPal and create the acknowledgement response
 	$req = 'cmd=_notify-validate';               
  	foreach ($_POST as $key => $value) {         // Loop through the notification NV pairs
    	$value = urlencode(stripslashes($value));  // Encode the values
    	$req .= "&$key=$value";                    // Add the NV pairs to the acknowledgement
		// retrieve the request's body and parse it as JSON
	}

	// Assign payment notification values to local variables
  	$item_name = $_POST['item_name'];
 	$item_number = $_POST['item_number'];
  	$payment_status = $_POST['payment_status'];
  	$payment_amount = $_POST['mc_gross'];
  	$payment_currency = $_POST['mc_currency'];
  	$txn_id = $_POST['txn_id'];
  	$receiver_email = $_POST['receiver_email'];
  	$payer_email = $_POST['payer_email'];
	$name = $_POST['first_name'].' '.$_POST['last_name'];
  	$desc = $_POST['custom'];
	$info = $item_name."&".$item_number."&".$receiver_email."&".$payer_email."&".$name."&".$desc;

	//Set up the acknowledgement request headers
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	//$header .= "Host: www.sandbox.paypal.com\r\n";  // for test IPNS
	$header .= "Host: www.paypal.com\r\n";  // www.paypal.com for a live site
	$header .= "Content-Length: " . strlen($req) . "\r\n";
	$header .= "Connection: close\r\n\r\n";

  	//Open a socket for the acknowledgement request
  	//$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
	$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
  	// Post request back to PayPal for validation
	if (!$fp) {
		//wp_mail("president@savoyswing.org", "IPN Process SOCKET ERROR!", $fp, 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>');
		trigger_error('Could not connect for the IPN!');
	} else {
		//wp_mail("president@savoyswing.org", "IPN Process SOCKET!", $fp, 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>');
  		fputs ($fp, $header . $req);
	}
	$repeated_attempt = false;
	while (!feof($fp)) {                 
		$res = fgets ($fp, 1024);              // Get the acknowledgement response
		//wp_mail("president@savoyswing.org", "IPN Process 1", $res, 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>');
		$res = trim($res);
	    if (strcmp ($res, "VERIFIED") == 0) {  // Response is VERIFIED
	
	      	// Send an email announcing the IPN message is VERIFIED to Treasurer
			$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
	      	$mail_To = "treasurer@savoyswing.org";
		    $mail_Subject = "VERIFIED IPN-PAYPAL";
		    $mail_Body = "Recent purchase of Membership through Paypal!\r\n";
		    $mail_Body .= "Name: ".$name."\r\n";
		    $mail_Body .= "Amount: $".$payment_amount."\r\n";
		    $mail_Body .= $desc."\r\n";
			wp_mail($mail_To, $mail_Subject, $mail_Body, $headers);

			if ($item_name == 'MembershipBuyNow'){ //membership payment
				//Get membership type from option form
				$subscription_title = 'Regular';
				if (isset($_POST['option_selection2']) || isset($_POST['option_selection3'])) {
					$subscription_title = (isset($_POST['option_selection2'])) ? $_POST['option_selection2'] : $_POST['option_selection3'];
				}
				$subscription_title = trim($subscription_title);

				$mem_id = preg_split('/[:]/', $desc);	//get member id from description
				//$mem_id = preg_split('/[:]/', $item_number);	//get member id from description
				$mem_id = $mem_id[1];

				
				list($id, $email, $name, $status, $act_date, $exp_date, $paypal_last_txn) = getUserDetails($mem_id);	//get user info

				if ( $txn_id != $paypal_last_txn && $payment_currency == 'USD' && validPaymentAmount($payment_amount, $subscription_title) ) {
					if ($status == "PAID" ) {
						$next_exp_date = date("n/j/Y", strtotime("+1 year", strtotime($exp_date)));
					} else {
						$first_of_month = date('n/1/Y');
						$beg_next_month = strtotime( "+1 month", strtotime($first_of_month) );
						$next_exp_date =  date('n/1/Y', strtotime("+1 year", $beg_next_month));
					}
					if ($act_date == "" ) {
						$the_date = date('n/j/Y');
						update_user_meta($id, 'act_date', $the_date);
						$act_date = $the_date;
					}
					

					// email information
					$subject = __('Payment Receipt : Savoy Swing Club Membership Purchase', 'Paypal');
					$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
					$message = "Dear " . $name. ",\n\n";
					$message .= "You have successfully Purchased an item at savoyswing.org. Please refer to the following data for this invoice:\n\n";
					$message .= "Invoice ID:			  	 " .$txn_id . " (Paypal)\n";
					$message .= "Member ID: 			 	" . $mem_id . "\n";
					$message .= "Subscription Purchased:	". $subscription_title."		Expires: ".$next_exp_date."\n";
					$message .= "Amount Charged:	 	 $" . $payment_amount . "\n\n";
					$message .= "If you have any concerns on this purchase or any other, please reply here and someone should get back to you as soon as possible. Please reference the invoice ID so that we may process your claim.  We at Savoy Swing Club thank you for your purchase. Please check out our other opportunities at: https://www.savoyswing.org/ \n\n";
					$message .= "Sincerely,\n\n";
					$message .= "Savoy Swing Club Team\n";
					$message .= "https://www.savoyswing.org/";
	 				
					if ( isset($payer_email) && $payer_email != '' ) {
						wp_mail($payer_email, $subject, $message, $headers);
					}

					update_user_meta($id, 'status', 'PAID');
					update_user_meta($id, 'exp_date', $next_exp_date);
					update_user_meta($id, 'paypal_last_txn', $txn_id);

					emailMembershipCoordinator($txn_id, $name, $email, $status, $act_date, $next_exp_date, $mem_id, $subscription_title);
				} else if ( $txn_id !== $paypal_last_txn ) {
				  	$repeated_attempt = true;
				} else {
					//payment was either not correct amount or not USD
					$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
			      	$mail_To = "membership@savoyswing.org";
			      	$mail_Subject = "Recent Membership Payment was different amount - request additional money";
			      	$mail_Body = $req;
					wp_mail($mail_To, $mail_Subject, $mail_Body, $headers);
					break;
				}
			} else {  		//all other payments
				
			}

		    // Notification protocol is complete, OK to process notification contents
			 
		    // Possible processing steps for a payment might include the following:
		    // Check that the payment_status is Completed
		    // Check that txn_id has not been previously processed
		   	// Check that receiver_email is your Primary PayPal email
		    // Check that payment_amount/payment_currency are correct
		    // Process payment
	
	    }
	    if ($repeated_attempt || strcmp ($res, "INVALID") == 0 ) { // Response is INVALID or if repeat of txn
	
	      // Notification protocol is NOT complete, begin error handling
	
	      // Send an email announcing the IPN message is INVALID
	      	// Send an email announcing the IPN message is VERIFIED to Treasurer
			$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
	      	$mail_To = "webmaster@savoyswing.org";
	      	$mail_Subject = "INVALID IPN - PayPal Payment Failed";
	      	$mail_Body = $req."\r\n\r\nWebmaster has been notified, please follow up with member!";
			wp_mail($mail_To, $mail_Subject, $mail_Body, $headers);
			wp_mail("membership@savoyswing.org", $mail_Subject, $mail_Body, $headers);
			break;
	    }
	  }
 	  fclose ($fp);  //close file pointer

} else {
	header("Location: https://www.savoyswing.org/");
}
?>