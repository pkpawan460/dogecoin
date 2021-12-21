<?php 
ob_start();

/* Template Name: thanks Template */ ?>
<?php

require __DIR__ . "/../vendor/autoload.php";
get_header();
$my_fields = get_option('woocommerce_pwn_etheriumn_gateway_settings');
print_r($my_fields);
global $woocommerce;
$amount = WC()->session->get('myorder');
if(empty($amount)){
	$amount=0;
}
 $order = wc_get_order( $order_id );
?>
<div style="margin-left:30px;">	
<?php
    echo"<h2 style='font-weight:bold;'>Thanks for Order. Your Order will process after payment.</h2>";
    echo"<h2 style='font-weight:bold;'>Please make the payment of Ð".$amount."</h2>";
    echo '<p style="font-weight:700;">Dogecoin Payment Address</p>';
    echo '<p style="padding:5px;font-weight:700;border: #f2f2f2;background: #f2f2f2;">'.$my_fields['apiLogin'].'</p>';

    ?>
	<p style="font-weight:400;">Awaiting Payment from You. please click on "I paid. Please Verify" after make a payment.</p>
	<p style="font-style:italic;font-size:13px;"><b>Note:</b> If you send any other dogecoin payment, System will ignore you.<p>
    <p>&nbsp;</p>
    
    <form method="post">
		  <input type="submit" name="cpaid" value="I paid. Please Verify" />
        <input type="submit" name="paid" value="Click Here if you have already sent Dogecoin." />
    </form>
</div>

<?php
if(isset($_REQUEST['key'])){
   $key = base64_decode($_REQUEST['key']);
}
if(isset($_REQUEST['id'])){
    $order_id = $_REQUEST['id'];
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dogechain.info/api/v1/address/balance/'.$my_fields['apiLogin'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
$bal = json_decode($response);
$amt = round($bal->balance);


if(isset($_REQUEST['paid'])){
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://dogechain.info/api/v1/address/balance/'.$my_fields['apiLogin'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response);
    $currect_bal = $result->balance;
	
   if($currect_bal > $amt){
	$diff = $currect_bal - $amt;
	if(number_format($diff,2) == number_format($amount,2)){
    	    echo'<p style="color:green">Your payment is verified successfully and your order is placed.</p>';
    	    WC()->session->set( 'myorder', null );
    	    $orderDetail = new WC_Order( $order_id );
    	    $orderDetail->update_status("wc-processing", 'Processing', TRUE);
            $orderDetail->update_meta_data( 'Transtion_ID', md5($order_id) );
            $orderDetail->save();
    	    wp_redirect($key);
            exit;
	     }
    }else{
        
		echo'<p style="color:red">your payment is not verified. Please try again</p>';
    }
}

if(isset($_REQUEST['cpaid'])){
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://dogechain.info/api/v1/address/balance/'.$my_fields['apiLogin'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response);
    $currect_bal = $result->balance;
	
    if($currect_bal < $amt){
    	$diff = $currect_bal - $amt;
    	if(number_format($diff,2) != number_format($amount,2)){
    	   echo'<p style="color:green">Your payment is verified successfully and your order is placed.</p>';
    	   WC()->session->set( 'myorder', null );
    	   $orderDetail = new WC_Order( $order_id );
    	   $orderDetail->update_status("wc-processing", 'Processing', TRUE);
    	   $orderDetail->update_meta_data( 'Transtion_ID', md5($order_id) );
    	   $orderDetail->save();
    	   wp_redirect($key);
           exit;
	   }	
	}else{
        
		echo'<p style="color:red">your payment is not verified. Please try again</p>';
    }
}


?>

<?php get_footer(); ?>