<?php
/*
Title: Settings for SSC iPhone App
Description: Settings for SSC iPhone App plugin
*/

function ss_products_options_validate($input) {
	return $input;
}

function ss_product_options_page() {
	$options = array(
		'stripe_pub_key' => '',
		'stripe_secret_key' => '',
		'payment_form_url' => '',
		'email_success' => '',
		'email_refund' => '',
		'email_fail' => ''
	);
	add_option('ss_product_options', $options, '', 'yes');	

	?>
	<div class="wrap">
	<form action="options.php" method="POST">
	<?php settings_fields('ss_product_options_group'); ?>
	<?php $options = get_option('ss_product_options'); ?>
	<?php $stripe_pub_key = $options['stripe_pub_key']; ?>
	<?php $stripe_secret_key = $options['stripe_secret_key']; ?>
	<?php $payment_form_url = $options['payment_form_url']; ?>
	<?php $email_charge_success = $options['email_success']; ?>
	<?php $email_charge_refund = $options['email_refund']; ?>
	<?php $email_charge_fail = $options['email_fail']; ?>
	<table style='float: left; width: 100%;'><TR><TD>
		<?php screen_icon(); ?>
		
		<h2>Product Settings</h2>
		<table style="width:100%;">
		<tr><td style='border: 1px solid #D0D0D0; border-width: 0px 0px 1px 0px;'>
			<h3 style='margin-bottom: 2px;'>Stripe Settings</h3>
			
		</td></tr><tr><td>
			<style>
				.label_inline {
					display: inline-block;
					width: 150px;
			}
			</style>
			<p>Please Provide Key (Note the difference, between Testing and live keys!)
</p>
			<p><span class='label_inline'>Publishable Key: </span>
				<input id='stripe_pub_key' type='text' size='50' name='ss_product_options[stripe_pub_key]' value ='<?php echo $stripe_pub_key; ?>'> </p>
			<p><span class='label_inline'>Secret Key: </span>
				<input id='stripe_secret_key' type='text' size='50' name='ss_product_options[stripe_secret_key]' value ='<?php echo $stripe_secret_key; ?>'> </p>
			<p><span class='label_inline'>URL of Payment Form: </span>
				<input id='payment_form_url' type='text' size='50' name='ss_product_options[payment_form_url]' value ='<?php echo $payment_form_url; ?>'> </p>

		</td></tr>
		<tr><td style='border: 1px solid #D0D0D0; border-width: 0px 0px 1px 0px;'>
			<h3 style='margin-bottom: 2px;'>Email Settings</h3>
			
		</td></tr><tr><Td>
<p>Following emails will be sent depending on action required. Take note of the following user-specific tags:
</p>
<blockquote style='padding: 20px;'>
<style>
.email_var {
	display: inline-block;
	width: 120px;
}
.email_var2 {
	display: inline-block;
	width: 150px;
}
</style>
<span class='email_var'>%name% </span>- User Full Name<br />
<span class='email_var'>%invoice_id% </span>- Invoice ID<br />
<span class='email_var'>%mem_id% </span>- User Member ID<br />
<span class='email_var'>%type% </span>- Purchased Item Name<br />
<span class='email_var'>%items% </span>- Names of all Purchased Items<br />
<span class='email_var'>%card_ending_in% </span>- Last four numbers of processed card<br />
<span class='email_var'>%amount% </span>- Amount Charged<br />
<span class='email_var'>%message% </span>- Output Error Message (when applicable)<br />
<Br/>
<b>Special Tags</b><Br/>
<span class='email_var2'>%subscription_purch% </span>- Containing mine is for membership purchased items only<br />
<span class='email_var2'>%expires% </span>- Membership Expires(Membership Purchase only)<br />
<span class='email_var2'>%subscription_purch% </span>- Containing mine is for non-membership purchased items only<br />
</blockquote>
			<p><b>Charge Succeeded:</b> <span style='color:#A0A0A0'>(the email user receives after payment)</span><Br />
				<textarea name='ss_product_options[email_success]' style='margin-left: 20px; width: 90%' rows='10'><? echo $email_charge_success; ?></textarea> </p>
			<p><b>Charge Refunded:</b> <span style='color:#A0A0A0'>(the email user receives after refund - partial or full)</span><br />
				<textarea name='ss_product_options[email_refund]' style='margin-left: 20px; width: 90%' rows='10'><? echo $email_charge_refund; ?></textarea> </p>
			<p><b>Charge Failed:</b> <span style='color:#A0A0A0'>(the email user receives after failed charge)</span><br />
				<textarea name='ss_product_options[email_fail]' style='margin-left: 20px; width: 90%' rows='10'><? echo $email_charge_fail; ?></textarea> </p>
		</td></tr>
		<tr><td style='border: 1px solid #D0D0D0; border-width: 1px 0px 0px 0px;'>
			<br>
			<input class='button-primary' style='float:right;' type='submit' name'Save2' value='<?php _e('Save Options'); ?>' id='submitbutton2'>
		</td></tr></table>

	</TD><TD style='width:200px; border: 1px solid #D0D0D0; border-width: 0px 0px 0px 1px;' valign='top'>
		<center>
		<h3 style='margin-bottom:0px;'>Products Plugin</h3>
		by <a href='http://www.stevenandleah.com'>Steven Stevenson</a><BR>
		email: <a href="mailto:stevensonhoyt@icloud.com">stevensonhoyt@icloud.com</a>
		</center>
		<BR>
	</TD></TR></TABLE>
	</form>
	</div>
	<?
}

?>