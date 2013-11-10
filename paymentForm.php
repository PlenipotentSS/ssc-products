<?
add_shortcode('payment-form', 'ss_payment_form_shortcode');
function ss_payment_form_shortcode($atts) {
	$allow_phys_cards = false;
	$site_url = get_site_url()."/";
?>
<p><script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"></script>
</p>
<div id='default_payment' class="box_payment" style="float:left;">
<h2>Item List</h2>
<table style='width: 100%; margin-bottom: 5px;'><Tr><td><strong>Item Information</strong></td><td style="width: 100px; text-align:center;"><strong>Cost</strong></td></tr></table>
<table style='width: 100%;'>
<?
$args = array( 'post_type' => 'product', 'orderby' => 'date', 'order' => 'ASC');
$loop = new WP_Query( $args );
$payment_options = get_option('ss_product_options');
$payment_form_url = $payment_options['payment_form_url'];
$test = '/';
if ( !(substr_compare($payment_form_url, $test, -strlen($test), strlen($test)) === 0) ) {
	$payment_form_url .= "/";
}
while ( $loop->have_posts() ) {
	$loop->the_post();
	$id = $loop->post->ID;
	$title =  $loop->post->post_title;
	$meta = get_post_meta($id);
	$cost = $meta['cost'][0];
	$tag = $meta['tag'][0];
	$type = $meta['type'][0];
	$img_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
	$htmlcode = '&quot;';
    $character = '"';
	$content =  $loop->post->post_content;
	$content = str_replace($character, $htmlcode, $content);
	if ( $type == 'subscription') {
		
		echo "<tr><td style='padding: 0px 15px;'><span class='product-title'><a href='".$payment_form_url."?mem-type=".$tag."' id='".$id."' data-img='".$img_url[0]."' data-info=\"".$content."\" class='prod-link'>".$title."</a></span></td><td style='width: 100px; text-align:center;'>$<span class='product-cost'>$cost</span></td></tr>";
	} else {
		echo "<tr><td style='padding: 0px 15px;'><span class='product-title'><a href='".$payment_form_url."?gen-prod0=".$tag."' id='".$id."' data-img='".$img_url[0]."' data-info=\"".$content."\" class='prod-link'>".$title."</a></span></td><td style='width: 100px; text-align:center;'>$<span class='product-cost'>$cost</span></td></tr>";
	}
}
?>
</table>
</div>
<style>
#more_info_div {
	position: absolute;
	left: 0px; 
	top: 0px; 
	width:150px; 
	display: none;
	background-color: #FFF ;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	-ms-sizing: border-box;
        border-radius: 5px;

	-moz-box-shadow: 0 0 2px rgba(80,84,92,0.3), 0 1px 1px rgba(80,84,92,0.5);
	-webkit-box-shadow: 0 0 2px rgba(80, 84, 92, 0.3), 0 1px 1px rgba(80, 84, 92, 0.5);
	-ms-box-shadow: 0 0 2px rgba(80, 84, 92, 0.3), 0 1px 1px rgba(80, 84, 92, 0.5);
	box-shadow: 0 0 2px rgba(80, 84, 92, 0.3), 0 1px 1px rgba(80, 84, 92, 0.5);
}
</style>
<div id='more_info_div' style=" "><center>
<img id='content_img' style='max-width:150px;' src="" />
</center><center><span id='content_info'>Information</span></center>
</div>
<script>
$('.prod-link').mouseenter(function(e) {
    a = $('#content-container');
    x =  e.pageX - a.offset().left;
    y =  e.pageY - a.offset().top;
    $('#more_info_div').css({ position: "absolute", marginLeft: 0, marginTop: 0, left: x, top: y, } );
});

$(".prod-link").hover(	function (){ $(this).showElement(); },
						function (){ $('#more_info_div').hide();});
jQuery.fn.showElement = function()
{
    return this.each(function()
    {
        document.getElementById('content_info').innerHTML = $(this).data("info");
        document.getElementById("more_info_div").getElementsByTagName("img")[0].src = $(this).data("img");
        $('#more_info_div').show();
    });
};
</script>
<div id='back-to-members' style="float:left;"><a href="<? echo $site_url; ?>."members/#membership_pay"><< Click to change Membership</a></div>
<p><script>
function updateTotal(){
	var elems = document.getElementsByClassName('item-cost');
	var runningTotal = 0;
	for (var i=0; i<elems.length; i++){
		runningTotal += parseFloat(elems[i].innerHTML);
	}
	runningTotal = Math.round(runningTotal*100)/100;
	runningTotal = runningTotal.toFixed(2);
	document.getElementById('total-cost').innerHTML = runningTotal;
	document.getElementById('trans_cost').value = runningTotal;
	document.getElementById('trans_cost2').value = runningTotal;
}
</script><br />
<!---- main invoice content----------></p>
<div style='width: 100%px; margin: -10px 10px;'>
<center><p>
<div style="display: none;" class="payment-errors">Errors</div>
<div style="display: none;" class="payment-success">Success</div>
</p></center>
</div>
<div id='payment_information' class="box_payment" >
<h2>Payment Information</h2>
<table style='width: 100%; margin-bottom: 5px;'><Tr><td><strong>Item Information</strong></td><td style="width: 100px; text-align:center;"><strong>Cost</strong></td></tr></table>
<table id='all-items' style='width: 100%;'>

</table>
<table style='width: 100%; margin-bottom: 0px;'>
<Tr><td>&nbsp;</td><td style="width: 100px; text-align:center;"><strong><span id='total-cost-title'>Total Cost</span></strong></td></tr>
<Tr><td>&nbsp;</td><td style="width: 100px; text-align:center;"><strong>$<span id='total-cost'>0</span></strong></td></tr>
<tr height='5px'><td></td></tr>
</table>
</div>

<div id='card-choice' style='width: 100%; display: block; float: right; margin-right: 10px; text-align: right;'><table align='right'><tr><td width='80px'><strong>Select Card</strong></td><td style="width:10px;">&nbsp;</td><td><select id='card-select' value=''><option id='card-1'>-Save Card Info-</option><option id='card-2' value='one-time'>-One Time Charge-</option></select></td></tr></table></div>
<form action="<? echo $site_url; ?>wp-content/plugins/products/stripe-process.php" method="POST" style="width:600px; float: left; left: -300px;" >
<div id='payment-tag' style='float: right; margin: 4px 10px;'><table><TR><td><script src="<? echo $site_url; ?>wp-content/plugins/products/tag.js"></script><link rel="stylesheet" href="<? echo $site_url; ?>wp-content/plugins/products/themes/stripe.css" type="text/css">
  <div id='addr_box_payment' class="box_payment">
    <span class="box_titles">CONFIRM BILLING INFORMATION</span><br /><p>
	<?
	global $userdata;
  	$product_options = get_option('ss_product_options');
 	$pub_key = $product_options['stripe_pub_key'];
	$full_name = $userdata->first_name." ".$userdata->last_name;
	$addr1 = $userdata->addr1;
	$zip = $userdata->zip;
	$last_4 = $userdata->last_4_main;
	$user_email = $userdata->user_email;
	$get_to_string = '';
	if ( !empty($_GET) ) {
		$get_to_string = '?';
		foreach ($_GET as $k => $v) {
  			$get_to_string .= $k.'='.$v.'&';
		}
	$get_to_string =  substr($get_to_string,0,-1);
	}

	
	?>
  <input type="text" name="cust_name" class="cust_name" value="<? echo $full_name; ?>" /><br><br>
    <input type="text" size="26" class="cust_addr1" value="<? echo $addr1; ?>" /> <input type="text" size="4" class="cust_zip" value="<? echo $zip; ?>" />
  </p></div>
  <payment key="<? echo $pub_key; ?>"></payment>
  <span style='float: right; width: 200px; margin-top:10px'><span id='auto-renew-check' ><input type='checkbox' id='renew_check' name='subs_renew' value="yes" checked> <strong>Auto-Renew Subscription</strong></span></span>
  <span style='margin-left: 10px; margin-top: 10px; float: left;'><input type="submit" class="submit" value="Pay Now with Stripe!"></span>
  <input type="hidden" id='trans_det' name="trans_det" value="" />
  <input type="hidden" id='trans_cost' name="trans_cost" value="" />
  <input type="hidden" name="trans_email" value="<? echo $user_email; ?>" />
</form>
</td></tr></table>
<div style='display: block; float: right; width: 568px;'><small>Any payment listed above is secure via SSL and we do not keep your card number or billing information on our server. Our payment service, Stripe, is PCI certified and securely processes every transaction with the highest level of security including high level encryption. If you would like to know more about the security of your information or the payment process, don't hesitate to visit <a href="https://www.stripe.com">Stripe.com</a>.</small></div>
</div>
<div id='stored-change-card' style='float: right; margin: 4px 10px; display: none;'>
 <div id='change_card' class="box_payment" style='margin-top: -6px; float: left; width: 250px; height: 150px;'>
<form action="<? echo $site_url; ?>wp-content/plugins/products/stripe-process.php" method="POST">
<span style='display: block; margin-top:20px; width: 100%; text-align: center;'><h2 style='margin-bottom: 0px;'>Remove Card</h2></span><br /><br />
<input type='submit' class='submit-button' style='margin-left: 85px; margin-top: -5px;' value="Select" /><input type='hidden' name='get-vars' value='<? echo $get_to_string; ?>'><input type='hidden' name='remove_customer_card' value='yes'>
</form>
 </div>
 <div id='bill_card' class="box_payment" style='margin-top: -6px; float: left; width: 250px; height: 150px;' >
<form action="<? echo $site_url; ?>wp-content/plugins/products/stripe-process.php" method="POST" onsubmit="return confirm('Bill this card ending in <? echo $last_4; ?>?');">
<span style='display: block; margin-top:20px; width: 100%; text-align: center;'><h2 style='margin-bottom: 0px;'>Use Stored Card</h2></span><br /><br />
<input type='submit' class='submit-button' style='margin-left: 85px; margin-top: -5px;' value="Select" /><input type='hidden' name='get-vars' value='<? echo $get_to_string; ?>'><input type='hidden' id='trans_det2' name='trans_det' value=''><input type='hidden' id='trans_cost2' name='trans_cost' value=''><input type='hidden' name='customer_card' value='yes'>

</form>
 </div>
</div>
<?
global $userdata;
$style_show = 'none';
$style_show_mem = 'none';
$style_show_block = 'none';
$show_card_choice = 'none';
$style_hide_default = 'none';
$stored_card = 'none';
$auto_renew_display = 'inline';
$type = "";
$cost = "";
$found = false;
$one_mem = false;
$trans_list = "";
$arr = array();
$content_to_page = false;
if ( isset($_GET['mem-type']) ) {
	$args = array( 'post_type' => 'product', );
	$loop = new WP_Query( $args );
	while ( $loop->have_posts() ) {
		$loop->the_post();
		$id = $loop->post->ID;
		$meta = get_post_meta($id);
		if ( strtolower($meta['type'][0]) == 'subscription' && strtolower($meta['tag'][0]) == $_GET['mem-type']) {
			array_push($arr, array('subscription',$_GET['mem-type']));
		}
	}
}
$gen_prod_index = 0;
while ( isset($_GET['gen-prod'.$gen_prod_index]) ) {
	array_push($arr, array('gen-prod',$_GET['gen-prod'.$gen_prod_index], $gen_prod_index));
	$gen_prod_index++;
}
if ( count($arr) > 0 ) { 
	echo "
<script>
	var table = document.getElementById('all-items');
	var rowCount = table.rows.length;";

	$args = array( 'post_type' => 'product');
	$loop = new WP_Query( $args );
	$phys_card_output = '';
	$contains_phys_card = false;
	while ( $loop->have_posts() ) { //look through products to compare
		$loop->the_post();
		$id = $loop->post->ID;
		$meta = get_post_meta($id);
		for ( $i=0; $i < count($arr); $i++ ){
			if ( $arr[$i][0] == strtolower($meta['type'][0]) ) { // if subscription
				if ( $arr[$i][1] == strtolower($meta['tag'][0]) && !$one_mem) { //if valid subscription
					$htmlcode = '&quot;';
   					$character = '"';
					$content =  $loop->post->post_content;
					$content = str_replace($character, $htmlcode, $content);
					$type =  $loop->post->post_title;
					$trans_list .= " | ".$type;
					$cost = $meta['cost'][0];
					$img_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
				
					$removed_item = "mem-type=".$arr[$i][1];
					$current_get_vars = $_SERVER["REQUEST_URI"];
					$url = str_replace($removed_item,'',$current_get_vars);
					$url = get_site_url().$url;
					echo "
	var link_".$i." = document.createElement('a');
	link_".$i.".setAttribute('href','javascript:void(0)');
	link_".$i.".setAttribute('id','".$id."');
	link_".$i.".setAttribute('data-img','".$img_url[0]."');
	link_".$i.".setAttribute('data-info',\"".$content."\");
	link_".$i.".setAttribute('class','purch-link');
	link_".$i.".innerHTML = \"".$type."\";

	var link2_".$i." = document.createElement('a');
	link2_".$i.".setAttribute('href','".$url."');
	link2_".$i.".innerHTML = \"&nbsp;&nbsp;&nbsp; [ <font color='red'>remove item</font> ] \";

	var row_".$i." = table.insertRow(rowCount);
	var r_".$i."_cell1 = row_".$i.".insertCell(0);
	r_".$i."_cell1.style.paddingLeft = '15px';
	r_".$i."_cell1.style.width = '375px';
	var r_".$i."_span1 = document.createElement('span');
	r_".$i."_span1.setAttribute('id','title-type');
	r_".$i."_span1.appendChild(link_".$i.");
	r_".$i."_span1.appendChild(link2_".$i.");
	r_".$i."_span1.value = \"".$type."\";
	r_".$i."_cell1.appendChild(r_".$i."_span1);
	var r_".$i."_cell2 = row_".$i.".insertCell(1);
	r_".$i."_cell2.style.width = '100px';
	var r_".$i."_cell3 = row_".$i.".insertCell(2);
	r_".$i."_cell3.appendChild(document.createTextNode('$'));
	var r_".$i."_span2 = document.createElement('span');
	r_".$i."_span2.setAttribute('id','title-cost');
	r_".$i."_span2.setAttribute('class','item-cost');
	r_".$i."_span2.innerHTML = '".$cost."';
	r_".$i."_span2.value = '".$cost."';
	r_".$i."_cell3.appendChild(r_".$i."_span2);
	rowCount++;
";
					$one_mem = true;
					$found = true;
					$content_to_page = true;
				} //end if
			} else if ( $arr[$i][1] == strtolower($meta['tag'][0]) && strtolower($meta['type'][0]) != 'subscription' ) { //if not subscription
				$htmlcode = '&quot;';
   				$character = '"';
				$content =  $loop->post->post_content;
				$content = str_replace($character, $htmlcode, $content);
				$type =  $loop->post->post_title;
				$trans_list .= " | ".$type;
				$cost = $meta['cost'][0];
				$img_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
				
				$removed_item = "gen-prod".$arr[$i][2]."=".$arr[$i][1];
				$current_get_vars = $_SERVER["REQUEST_URI"];
				$url = str_replace($removed_item,'',$current_get_vars);
				for ( $j=$arr[$i][2]; $j<$gen_prod_index; $j++ ){
					$old = "gen-prod".($j+1);
					$new = "gen-prod".$j;
					$url = str_replace($old,$new,$url);
				}
				$url = get_site_url().$url;
				$output =  "
	var link_".$i." = document.createElement('a');
	link_".$i.".setAttribute('href','javascript:void(0)');
	link_".$i.".setAttribute('id','".$id."');
	link_".$i.".setAttribute('data-img','".$img_url[0]."');
	link_".$i.".setAttribute('data-info',\"".$content."\");
	link_".$i.".setAttribute('class','purch-link');
	link_".$i.".innerHTML = \"".$type." \";

	var link2_".$i." = document.createElement('a');
	link2_".$i.".setAttribute('href','".$url."');
	link2_".$i.".innerHTML = \"&nbsp;&nbsp;&nbsp; [ <font color='red'>remove item</font> ] \";

	var row_".$i." = table.insertRow(rowCount);
	var r_".$i."_cell1 = row_".$i.".insertCell(0);
	r_".$i."_cell1.style.paddingLeft = '15px';
	r_".$i."_cell1.style.width = '375px';
	var r_".$i."_span1 = document.createElement('span');
	r_".$i."_span1.setAttribute('id','title-type');
	r_".$i."_span1.appendChild(link_".$i.");
	r_".$i."_span1.appendChild(link2_".$i.");
	r_".$i."_cell1.appendChild(r_".$i."_span1);
	var r_".$i."_cell2 = row_".$i.".insertCell(1);
	r_".$i."_cell2.style.width = '100px';
	var r_".$i."_cell3 = row_".$i.".insertCell(2);
	r_".$i."_cell3.appendChild(document.createTextNode('$'));
	r_".$i."_cell3.style.paddingRight = '15px';
	var r_".$i."_span2 = document.createElement('span');
	r_".$i."_span2.setAttribute('id','title-cost');
	r_".$i."_span2.setAttribute('class','item-cost');
	r_".$i."_span2.innerHTML = '".$cost."';
	r_".$i."_span2.value = '".$cost."';
	r_".$i."_cell3.appendChild(r_".$i."_span2);
	rowCount++;
";
				if ($arr[$i][1] == 'physical_card') {
					$phys_card_output = $output;
					$contains_phys_card = true;
				} else {
					echo $output;
					$found = true;
					$content_to_page = true;
				}
			}
		}
	}
	if ($contains_phys_card && $one_mem ) {
		echo $phys_card_output;
	} else if ($one_mem && $allow_phys_cards) {
		$url = get_site_url().$_SERVER["REQUEST_URI"]."&gen-prod".$gen_prod_index."=physical_card";
		$ask_add_card = "
			var link_inq_cards = document.createElement('a');
			link_inq_cards.setAttribute('href','".$url."');
			link_inq_cards.innerHTML = \" - Add Physical Card (&#36;2.50) - \";
		
			var row_inq_card = table.insertRow(rowCount);
			var r_card_cell1 = row_inq_card.insertCell(0);
			r_card_cell1.style.paddingLeft = '100px';
			r_card_cell1.style.width = '200px';
			var r_card_span1 = document.createElement('span');
			r_card_span1.appendChild(link_inq_cards);
			r_card_cell1.appendChild(r_card_span1);
			rowCount++;
		";
		echo $ask_add_card;
	}
	echo "
</script>
";
}
if( $found ) {
	$style_show = 'inline';
	$style_show_block = 'block';
	$style_hide_default = 'none';
	if ( isset($_GET['mem-type']) ) {
		$style_show_mem = 'inline';
		$auto_renew_display = 'inline';
	} else if ( isset($_GET['gen-prod0']) ) {
		$style_show_mem = 'none';
		$auto_renew_display = 'none';
		echo "
<script>
document.getElementById('renew_check').checked = false;
</script>
";
	}
}

if ( !empty($_GET) ) {
	if ( $userdata->str_uid != '' ) {
		$show_card_choice = 'inline';
		$stored_card = 'inline';
		$style_show = 'none';
		echo "
		<script>
			document.getElementById('card-1').value = '".$userdata->str_uid."';
			document.getElementById('card-1').innerHTML = '(Default) **** **** **** ".$userdata->last_4_main."';
			var select = document.getElementById('card-select');
			select.onchange = function() {
				if ( this.value == 'one-time' ) {
					document.getElementById('payment-tag').style.display = 'inline';
					document.getElementById('stored-change-card').style.display = 'none';
					document.getElementById('auto-renew-check').style.display = 'none';
				} else {
					document.getElementById('payment-tag').style.display = 'none';
					document.getElementById('stored-change-card').style.display = 'inline';
				}
			};
		</script>
	";	
	}
}

if ( isset($_SESSION['payment_sent']) ) {
	$content_to_page = true;
	$purchased = $_SESSION['payment_products'];
	$purchased_list = preg_split("/[|]/",$purchased);
	array_shift($purchased_list);
	echo "
<script>
	var table = document.getElementById('all-items');
	var rowCount = table.rows.length;";

	$args = array( 'post_type' => 'product');
	$loop = new WP_Query( $args );
	while ( $loop->have_posts() ) {
		$loop->the_post();
		$id = $loop->post->ID;
		$type =  $loop->post->post_title;
		$meta = get_post_meta($id);
		for ( $i=0; $i < count($purchased_list); $i++ ){
			if ( preg_match('/'.trim($purchased_list[$i]).'/', '/'.trim($type).'/') ) {
				$htmlcode = '&quot;';
   				$character = '"';
				$content =  $loop->post->post_content;
				$content = str_replace($character, $htmlcode, $content);
				$type =  $loop->post->post_title;
				$trans_list .= " | ".$type;
				$cost = $meta['cost'][0];
				$img_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );

				echo "
	var link_".$i." = document.createElement('a');
	link_".$i.".setAttribute('href','javascript:void(0)');
	link_".$i.".setAttribute('id','".$id."');
	link_".$i.".setAttribute('data-img','".$img_url[0]."');
	link_".$i.".setAttribute('data-info',\"".$content."\");
	link_".$i.".setAttribute('class','purch-link');
	link_".$i.".innerHTML = \"".$type."\";

	var row_".$i." = table.insertRow(rowCount);
	var r_".$i."_cell1 = row_".$i.".insertCell(0);
	r_".$i."_cell1.style.paddingLeft = '15px';
	r_".$i."_cell1.style.width = '375px';
	var r_".$i."_span1 = document.createElement('span');
	r_".$i."_span1.setAttribute('id','title-type');
	r_".$i."_span1.appendChild(link_".$i.");
	r_".$i."_cell1.appendChild(r_".$i."_span1);
	var r_".$i."_cell2 = row_".$i.".insertCell(1);
	r_".$i."_cell2.style.width = '100px';
	var r_".$i."_cell3 = row_".$i.".insertCell(2);
	r_".$i."_cell3.appendChild(document.createTextNode('$'));
	r_".$i."_cell3.style.paddingRight = '15px';
	var r_".$i."_span2 = document.createElement('span');
	r_".$i."_span2.setAttribute('id','title-cost');
	r_".$i."_span2.setAttribute('class','item-cost');
	r_".$i."_span2.innerHTML = '".$cost."';
	r_".$i."_span2.value = '".$cost."';
	r_".$i."_cell3.appendChild(r_".$i."_span2);
	rowCount++;
";
			}	//end if			
		}	//end for
	}	//end while
	if ( !(bool)$_SESSION['payment_sent'] ) { 
		echo "

	document.getElementById('total-cost-title').innerHTML = 'Total Charged';
</script>
";
		$style_show = 'none';
		$style_show_mem = 'none';
		$style_show_block = 'block';
		$auto_renew_display = 'none';
		$style_hide_default = 'none';
		$show_card_choice = 'none';
		$stored_card = 'none';
		echo "
		<script type='text/javascript'>
			$(document).ready(function(){setTimeout(msg,100);});
			function msg() {
   					$('.payment-success').text('Your card has been successfully processed, you will be getting an email when payment has been completed! When payment has passed through our payment service, any change in account information will be updated at that time. Thank you for your payment! '); 
					$('.payment-success').show();
			}
		</script>
";
		unset($_SESSION['payment_products']);
	} else {
		echo "
</script>
";
		echo "
		<script type='text/javascript'>
			$(document).ready(function(){setTimeout(msg, 100);});
			function msg() {
					$('.payment-errors').text('".$_SESSION['error_msg']."'); 
					$('.payment-errors').show();
			}
		</script>
";

		$style_hide_default = 'none';
		$trans_list = $_SESSION['payment_products'];
		if ( $userdata->str_uid != '' ) {
			$show_card_choice = 'inline';
			$stored_card = 'inline';
			$style_show_block = 'block';
			$style_show = 'none';
			echo "
			<script>
				document.getElementById('card-1').value = '".$userdata->str_uid."';
				document.getElementById('card-1').innerHTML = '(Default) **** **** **** ".$userdata->last_four_main."';
				var select = document.getElementById('card-select');
				select.onchange = function() {
					if ( this.value == 'one-time' ) {
						document.getElementById('payment-tag').style.display = 'inline';
						document.getElementById('stored-change-card').style.display = 'none';
						document.getElementById('auto-renew-check').style.display = 'none';
					} else {
						document.getElementById('payment-tag').style.display = 'none';
						document.getElementById('stored-change-card').style.display = 'inline';
					}
				};
			</script>
		";	
		} else {			
			$style_show = 'inline';
			$style_show_mem = 'inline';
			$style_show_block = 'block';
			$auto_renew_display = ($_SESSION['subscription_product'] != '' ) ? 'inline' : 'none';
		}
	}
	unset($_SESSION['payment_sent']);
}
 ?>
<script>
document.getElementById('stored-change-card').style.display = '<?echo $stored_card; ?>';
document.getElementById('trans_det').value = '<?echo $trans_list; ?>';
document.getElementById('trans_det2').value = '<?echo $trans_list; ?>';
document.getElementById('payment-tag').style.display = '<?echo $style_show; ?>';
document.getElementById('back-to-members').style.display =  '<?echo $style_show_mem; ?>';
document.getElementById('payment_information').style.display =  '<?echo $style_show_block; ?>';
document.getElementById('auto-renew-check').style.display = '<?echo $auto_renew_display; ?>';
document.getElementById('card-choice').style.display = '<?echo $show_card_choice; ?>';
document.getElementById('default_payment').style.display =  '<?echo $style_hide_default; ?>';
</script>
<style>
#purch_info_div {
	position: absolute;
	left: 0px; 
	top: 0px; 
	width:150px; 
	display: none;
	background-color: #FFF ;
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	-ms-sizing: border-box;
        border-radius: 5px;

	-moz-box-shadow: 0 0 2px rgba(80,84,92,0.3), 0 1px 1px rgba(80,84,92,0.5);
	-webkit-box-shadow: 0 0 2px rgba(80, 84, 92, 0.3), 0 1px 1px rgba(80, 84, 92, 0.5);
	-ms-box-shadow: 0 0 2px rgba(80, 84, 92, 0.3), 0 1px 1px rgba(80, 84, 92, 0.5);
	box-shadow: 0 0 2px rgba(80, 84, 92, 0.3), 0 1px 1px rgba(80, 84, 92, 0.5);
}
#content_img {
	max-width: 150px;
}
</style>
<div id='purch_info_div' style=" "><center>
<img id='content_img' src="" />
</center><center><span id='purch_content_info'>Information</span></center>
</div>
<script>
$('.purch-link').mouseenter(function(e) {
    a = $('#content-container');
    x =  e.pageX - a.offset().left;
    y =  e.pageY - a.offset().top;
    $('#purch_info_div').css({ position: "absolute", marginLeft: 0, marginTop: 0, left: x, top: y, } );
});

$(".purch-link").hover(	function (){ $(this).showPurchaseElement(); },
						function (){ $('#purch_info_div').hide();});
jQuery.fn.showPurchaseElement = function()
{
    return this.each(function()
    {
        document.getElementById('purch_content_info').innerHTML = $(this).data("info");
        document.getElementById("purch_info_div").getElementsByTagName("img")[0].src = $(this).data("img");;
        $('#purch_info_div').show();
    });
};
</script>
<?
if ( !$content_to_page ) {
	echo "<center><h2>- Your cart is empty -</h2></center>";
}
?>
<script>
updateTotal();
</script>
<?
} //end ss_payment_form_shortcode
?>