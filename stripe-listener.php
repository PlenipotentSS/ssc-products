<?
//test key
//$secret_key = "sk_0GsTMPZ2W9fpPEBIV5eXyXWCG9fMZ"; 		
//live key
//$secret_key = "sk_0GsTtfXwVFFujoBW2U8jMweGsojSn";
require_once('../../../wp-load.php');
$product_options = get_option('ss_product_options');
$secret_key = $product_options['stripe_secret_key'];


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
			return array( $id, $email, $name, $status, $act_date, $exp_date);
		}
	}
}

function emailMembershipCoordinator($id, $name, $email, $status, $act_date, $next_exp_date, $member_uid, $subscription_title, $physical_card_purchase, $wp_user_id) {
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
	$file = '';
	if ( $physical_card_purchase ) {
		$subject = __('Payment Receipt : Savoy Swing Club New Membership And Physical Card!', 'Stripe');
		
		$address1 = get_user_meta($wp_user_id, 'addr1');
		$city = get_user_meta($wp_user_id, 'city');
		$state = get_user_meta($wp_user_id, 'thestate');
		$zip = get_user_meta($wp_user_id, 'zip');
		$country = get_user_meta($wp_user_id, 'country');
		$address2 = get_user_meta($wp_user_id, 'addr2');
		$phone = get_user_meta($wp_user_id, 'phone1');
		include('pdf_conv_cards/make_card_invoice.php');
		$info_array = array($name,
						$address1[0].' '.$address2[0],
						$city[0].', '.$state[0].' '.$zip[0].' '.$country[0],
						$phone[0],
						$subscription_title,
						$member_uid,
						$next_exp_date);			
		$pdf = new PDF("P","mm","A4",$info_array);
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetFont('Times','',12);
		$pdf->FancyTable($header);
		$file = "card_invoices/PhysicalCardRequest-".$info_array[0].".pdf";
		$pdf->Output($file,"F");
	}
	wp_mail($to, $subject, $message, $headers, $file);
}

function processSubscriptionPayment($event, $invoice, $desc) {
// retrieve the payer's information
	$inv_id = $invoice->id;
	$last_four = $invoice->card->last4;
	$item = preg_split('/[|]/', $desc);
	array_shift($item);
	$mem_id = preg_split('/[:]/', $item[0]);
	$amount = $invoice->amount / 100; // amount comes in as amount in cents, so we need to convert to dollars
 
	//process our server information
	// return array: $id, $email, $name, $status, $act_date, $exp_date
	list($id, $email, $name, $status, $act_date, $exp_date) = getUserDetails($mem_id[1]);
	$message = "";

	// if is on renewal get user info
	$next_exp_date;
	$the_date;
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

	update_user_meta($id, 'status', 'PAID');
	update_user_meta($id, 'exp_date', $next_exp_date);


	
	// email information
	$subscription_title = '';
	$subject = __('Payment Receipt : Savoy Swing Club Purchase', 'Stripe');
	$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
	$message = "Dear " . $name. ",\n\n";
	$message .= "You have successfully Purchased an item at savoyswing.org. Please refer to the following data for this invoice:\n\n";
	$message .= "Invoice ID:			  	 " .$inv_id . "\n";
	$message .= "Member ID: 			 	" . $mem_id[1] . "\n";
	for ($i=0; $i < count($item); $i++ ) {
		if ( strpos(strtolower($item[$i]),'subscription')) {
			$subscription_title = trim($item[$i]);
			$message .= "Subscription Purchased:	". $item[$i]."		Expires: ".$next_exp_date."\n";
		} else {
			$message .= "Other Purchased Item: 	".$i.": 	" . trim($item[$i]) . "\n";
		}
	}
	$message .= "Card Ending in:		  	 ".$last_four."\n";
	$message .= "Amount Charged:	 	 $" . $amount . "\n\n";
	$message .= "If you have any concerns on this purchase or any other, please reply here and someone should get back to you as soon as possible. Please reference the invoice ID so that we may process your claim.  We at Savoy Swing Club thank you for your purchase. Please check out our other opportunities at: https://www.savoyswing.org/ \n\n";
	$message .= "Sincerely,\n\n";
	$message .= "Savoy Swing Club Team\n";
	$message .= "https://www.savoyswing.org/";
 				
	if ( isset($email) && $email != '' ) {
		wp_mail($email, $subject, $message, $headers);
	}
	if ( $subscription_title != '' ) {
		$physical_card_purchase = false;
		for ($i=0; $i < count($item); $i++ ) {
			if ( strpos(strtolower($item[$i]),'physical membership card') ) {
				$physical_card_purchase = true;
			}
		}
		emailMembershipCoordinator($inv_id, $name, $email, $status, $act_date, $next_exp_date, $mem_id[1], $subscription_title, $physical_card_purchase, $id);
	}
}

if(isset($_GET['wps-listener']) && $_GET['wps-listener'] == 'stripe') {
 	echo "Listener Activated...\n";
	global $stripe_options;
 
	require_once('./Stripe.php');
 
	Stripe::setApiKey($secret_key);
 
	// retrieve the request's body and parse it as JSON
	$body = @file_get_contents('php://input');
 
	// grab the event information
	$event_json = json_decode($body);
 
	// this will be used to retrieve the event from Stripe
	$event_id = $event_json->id;
 	
	if(isset($event_json->id)) {
		try {
 
			// to verify this is a real event, we re-retrieve the event from Stripe 
			$event = Stripe_Event::retrieve($event_id);
			echo $event;
			$invoice = $event->data->object;
 
			/*
			* if subscription event updated (after initial charge)
			* or subscription event is created
			*/
			if ( $event->type == 'customer.subscription.updated' || $event->type == 'invoice.payment_succeeded'   ) { 
				$customer = Stripe_Customer::retrieve($invoice->customer);
				if ( $strpos($customer->description, 'MemberID:') == 0 ) {
	 				$desc = $customer->description;
					processSubscriptionPayment($event, $invoice, $desc);
				}
			} else if ( strpos($invoice->description, 'MemberID:') == 0 ){ // successful payment of membership
				
				if($event->type == 'charge.succeeded') {
					// send a payment receipt email here
 					$desc = $invoice->description;
					processSubscriptionPayment($event, $invoice, $desc);
				}
	
				if($event->type == 'charge.refunded') {
					// send a payment receipt email here
					
	 
					// retrieve the payer's information
					$desc = $invoice->description;
					$last_four = $invoice->card->last4;
					$item = preg_split('/[|]/', $desc);
					$mem_id = preg_split('/[:]/', $item[0]);
					array_shift($item);
					$all_items = implode(" | ",$item);
					list($id, $email, $name, $status, $act_date, $exp_date) = getUserDetails($mem_id[1]);
	
					$amount = $invoice->amount_refunded / 100; // amount comes in as amount in cents, so we need to convert to dollars
	 
					$subject = __('Payment Refund for Member ID: '.$mem_id[1].' ', 'Stripe');
					$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
					$message = "Dear " . $name . ",\n\n";
					$message .= "You have successfully been refunded for the purchase: ".$all_items."\n";
					$message .= "Total Refunded:	 $" . $amount . "\n";
					$message .= "Card ending in 	 ".$last_four."\n\n";
	
					$message .= "At Savoy Swing Club, we strive for satisfaction with all our members. Thank you for your service.\n";
					$message .= "Sincerely,\n\n";
					$message .= "Savoy Swing Club Team\n";
					$message .= "https://www.savoyswing.org/";
	 				
					wp_mail($email, $subject, $message, $headers);
				}
	 
				// failed payment
				if($event->type == 'charge.failed') {
					// send a failed payment notice email here
	 
					// retrieve the payer's information
					$desc = $invoice->description;
					$inv_id = $invoice->id;
					$failure_message = $invoice->failure_message;
					$last_four = $invoice->card->last4;
					$item = preg_split('/[|]/', $desc);
					$mem_id = preg_split('/[:]/', $item[0]);
					array_shift($item);
					$all_items = implode(" | ",$item);
					list($id, $email, $name, $status, $act_date, $exp_date) = getUserDetails($mem_id[1]);
	
					$amount = $invoice->amount / 100; // amount comes in as amount in cents, so we need to convert to dollars
	 
					$subject = __('Failed Payment for Member ID: '.$mem_id[1].' ', 'Stripe');
					$headers = 'From: "' . html_entity_decode(get_bloginfo('name')) . '" <' . get_bloginfo('admin_email') . '>';
					$message = "Dear " . $name . "\n\n";
					$message .= "We have recently had a problem attempting to process one of your orders. Your order details are as follows:\n\n";
					$message .= "We have failed to process your payment of $" . $amount . "\n";
					$message .= "Invoice ID: 	".$inv_id;
					$message .= "Items			".$all_items;
					$message .= "Message:		".$failure_message;
					$message = "\n\n";
					$message .= "If you have any questions, please don't hesitate to contact us. membership@savoyswing.org.\n\n";
	
					$message .= "Thank you.\n";
					$message .= "Savoy Swing Club Team";
	 
					echo $message;
					wp_mail($email, $subject, $message, $headers);
				}
			}
		} catch (Exception $e) {
			// something failed, perhaps log a notice or email the site admin
			$email = "membership@savoyswing.org";

			$subject = __('Payment Receipt', 'Stripe');
			$headers = 'From: membership@savoyswing.org';
			$message = "Hello Savoy Swing Club Administrator, \n\n";
			$message .= "There was an error with a recent purchase, please note the following error: \n\n";
			$message .= $e->getMessage()."\n\n";
			$message .= "Thank you.\n";
			$message .= "Savoy Swing Club Tech Team";
 				
			echo $message;
			wp_mail($email, $subject, $message, $headers);
		}
	}
} else {
	header("Location: https://www.savoyswing.org/");
}
?>