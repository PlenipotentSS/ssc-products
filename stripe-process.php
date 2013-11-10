<?
session_start();
$pay_error = false;
require_once('../../../wp-load.php');
require_once('Stripe.php');
$product_options = get_option('ss_product_options');
$secret_key = $product_options['stripe_secret_key'];
Stripe::setApiKey($secret_key);
$subscription_title = '';
$payment_form_url = $product_options['payment_form_url'];
$test = '/';
if ( !(substr_compare($payment_form_url, $test, -strlen($test), strlen($test)) === 0) ) {
	$payment_form_url .= "/";
}
/*
 *	If payment-form sent payment token for stripe
 */
if ( isset($_POST['remove_customer_card']) ) {
	global $userdata;
	$c_id = $userdata->str_uid;
	$customer = Stripe_Customer::retrieve($c_id);
	$customer->cancelSubscription(array("at_period_end" => true));
	$customer->save();
	update_user_meta( $userdata->ID,'str_uid','');
	update_user_meta( $userdata->ID,'last_4_main','');
	$ext = $_POST['get-vars'];
	header("Location: ".$payment_form_url.$ext);
} else if ( isset($_POST['stripe_token']) || isset($_POST['customer_card']) ) {
	// get the credit card details submitted by the form
	$token = '';
	if ( isset($_POST['stripe_token']) ) {
		$token = $_POST['stripe_token'];
	}
	global $userdata;

	// process purchased items
	$subscription_cents = 0;
	$subscription_title = '';
	$remaining_purchases = "";
	$purch_list = preg_split("/[|]/",$_POST['trans_det']);
	array_shift($purch_list);
	for ($i=0; $i < count($purch_list); $i++ ) {
		if ( strpos(strtolower($purch_list[$i]),'subscription') ) {
			$subs_title = trim($purch_list[$i]);
			$args = array( 'post_type' => 'product');
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$id = $loop->post->ID;
				$this_title =  $loop->post->post_title;
				$meta = get_post_meta($id);
				if ( trim($purch_list[$i]) == trim($this_title) ) {
					$subscription_title = $this_title;
					$subscription_cents = $meta['cost'][0]*100;
					unset($purch_list[$i]);
					$purch_list2 =  array_values($purch_list);
					for ($j=0; $j < count($purch_list2); $j++ ) {
						$remaining_purchases .= " | ".$purch_list2[$j];
					}
					break;
				}
			}
			break;
		}
	}
	
	/*
	 *	attempt charging Stripe, catch errors and send via SESSION
	 */
    try {
		$c_id;
		if ( isset($_POST['customer_card']) ) {
			$c_id = $userdata->str_uid;
			$customer = Stripe_Customer::retrieve($c_id);
		}



		/*
		 * process other payments
		 * 
		 */
		$amount_cents = ($_POST['trans_cost']*100) - $subscription_cents;
		if ( $amount_cents != 0 ) {		//deal with all other payments with single charge
			//create single charge
			if ( isset($_POST['customer_card']) ) {
		        Stripe_Charge::create(array(
			 		'description' => 'MemberID: '.$userdata->unique_id.' | '.$subscription_title. ' | '.$remaining_purchases,
			        'amount'    => ($subscription_cents+$amount_cents),
			        'currency'  => 'usd',
			        'customer'  => $c_id,
				));	
			}
		}


		/*
		 * process memberships
		 *  ---> get charged now for membership, and if auto-renew
		 *	then don't charge until next year
		 */
		$thedate = strtotime(date('n/j/Y'));
		$first_of_month = date('n/1/Y');
		$beg_next_month = strtotime( "+1 month", strtotime($first_of_month) );
		$stripe_expiration =  strtotime("+1 year", $beg_next_month);
		$our_exp_date = strtotime("+1 year", strtotime(date($userdata->exp_date)));
		if ( $our_exp_date > $stripe_expiration ) {
			$stripe_expiration = $our_exp_date;
		}
		if ( $subscription_cents != 0 ) {
			//if automatic renewal membership
			if ( isset($_POST['subs_renew']) && $_POST['subs_renew'] == 'yes') {	
				$customer;
				// make plan object if necessary
				try {
					Stripe_Plan::retrieve($subscription_title);
				} catch( Exception $e ) {
					Stripe_Plan::create(array(
		  				"amount" => $subscription_cents,
		  				"interval" => "year",
		  				"name" => $subscription_title,
		  				"currency" => "usd",
		  				"id" => $subscription_title)
					);
				}
				
				// create customer or retrieve customer
				if ( $userdata->str_uid == "") {
					$customer = Stripe_Customer::create(array(
		 			 	"card" => $token,
		  				"description" => "MemberID: ".$userdata->unique_id,
		  				"email" => $_POST['trans_email'],
						)
					);
					$c_id = $customer->id;
					update_user_meta( $userdata->ID,'str_uid',$customer->id);
				} else {
					$c_id = $userdata->str_uid;
					$customer = Stripe_Customer::retrieve($c_id);
					$customer->card = $token;
					$customer->save();
				}
				$plan_attrs = array("plan" => $subscription_title, 
									"prorate" => false,
									"trial_end" => $stripe_expiration
						);
				$customer->updateSubscription($plan_attrs);
				Stripe_Charge::create(array(
		            'description' => 'MemberID: '.$userdata->unique_id.' | '.$subscription_title. ' | '.$remaining_purchases,
		            'amount'    => ($subscription_cents+$amount_cents),
		            'currency'  => 'usd',
		            'customer'  => $c_id,
		        ));	
			} else { 
				try {
					Stripe_Plan::retrieve($subscription_title);
				} catch( Exception $e ) {
					Stripe_Plan::create(array(
		  				"amount" => $subscription_cents,
		  				"interval" => "year",
		  				"name" => $subscription_title,
		  				"currency" => "usd",
		  				"id" => $subscription_title)
					);
				}
				if ( isset($_POST['customer_card']) ) {
					$plan_attrs = array("plan" => $subscription_title, 
									"prorate" => false,
									"trial_end" => $stripe_expiration
					);
					$customer->updateSubscription($plan_attrs);
			        Stripe_Charge::create(array(
				 		'description' => 'MemberID: '.$userdata->unique_id.' | '.$subscription_title,
				        'amount'    => $subscription_cents,
				        'currency'  => 'usd',
				        'customer'  => $c_id,
					));	
				} else {
			        Stripe_Charge::create(array(
			            'description' => 'MemberID: '.$userdata->unique_id.' | '.$subscription_title.' '.$remaining_purchases,
		            'amount'    => ($subscription_cents+$amount_cents),
			            'currency'  => 'usd',
			            'card'  => $token,
			        ));
				}
			}
		}
    } catch(Stripe_CardError $e) {
        // Since it's a decline, Stripe_CardError will be caught
        $body = $e->getJsonBody();
        $err  = $body['error'];
		$message = $err['message'];

    } catch (Stripe_InvalidRequestError $e) {
        $body = $e->getJsonBody();
        $err  = $body['error'];
		$message = $err['message'];
    } catch (Stripe_AuthenticationError $e) {
        $body = $e->getJsonBody();
        $err  = $body['error'];
		$message = $err['message'];
    } catch (Stripe_Error $e) {
        $body = $e->getJsonBody();
        $err  = $body['error'];
		$message = $err['message'];
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
	if ( !$message == "" ) {
		$pay_error = true;
		$_SESSION['error_msg'] = $message;
	}
	$_SESSION['payment_products'] = $_POST['trans_det'];
	$_SESSION['subscription_product'] = $subscription_title;
	$_SESSION['subs_cost'] = $subscription_cents/100;
	$_SESSION['payment_sent'] = $pay_error;
	header("Location: ".$payment_form_url);
} else {
	header("Location: ".get_site_url()."/");
}
?>