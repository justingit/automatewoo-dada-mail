<?php

/**
* Plugin Name: Dada Mail to AutomateWoo Bridge
* Description: 
* Author: Justin Simoni
* Version: 0.1
*/

$path               = '/home/youraccount/phplib';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once('DadaMailWebService.php'); 
static $server      = 'https://example.com/cgi-bin/dada/mail.cgi'; 
static $list        = 'list';
static $public_key  = 'public_key';                        
static $private_key = 'private_key';


if ( ! function_exists('write_log')) {
	function write_log ( $log )  {
	$myfile = fopen("/home/youraccount/public_html/wc-logs/dada_mail.log", "a") or die("Unable to open file!");
    if ( is_array( $log ) || is_object( $log ) ) {
   		fwrite($myfile, print_r( $log, true ));	   
    } else {
    	fwrite($myfile, $log);
    }
	
	fwrite($myfile, "\n");
	
	fclose($myfile);
   }
	
}
// write_log("hello!");

/* This is good for plain woocommerce:*/
add_action( 'woocommerce_payment_complete', 'so_payment_complete' );
	function so_payment_complete( $order_id ){	
	
	// How to get the email address from an order: 
	$order          = wc_get_order( $order_id );
	$billing_email  = $order->get_billing_email();
	
	$results        = ddm_validate_subscription($billing_email);	
	$results        = ddm_subscription($billing_email);	
	
}


/* Use this to subscribe from an order - we get the billing email from the cart/customer */

function my_automatewoo_action_ddm_subscribe( $workflow ) {
	
	// debug
	// my_automatewoo_action_ddm_test_obj( $workflow );
		
	$customer      = $workflow->data_layer()->get_customer();
	
	if (! $customer ) {
		write_log('no customer!');
		return;
	}
	else { 
		$billing_email = $customer->get_billing_email();
		$results       = ddm_validate_subscription($billing_email);	
		$results       = ddm_subscription($billing_email);	
	}
}

/* Use this to subscribe/unsubscribe from a new membership - we get the billing email from the user */
function my_automatewoo_action_ddm_subscribe_for_memberships( $workflow ) {
			
	$user      = $workflow->data_layer()->get_user();

	if (! $user ) {
		write_log('no user?!!');
		return;
	}
	else { 
		$results       = ddm_validate_subscription($user->data->user_email);	
		$results       = ddm_subscription($user->data->user_email);	
	}
}
function my_automatewoo_action_ddm_unsubscribe_for_memberships( $workflow ) {
			
	$user      = $workflow->data_layer()->get_user();

	if (! $user ) {
		write_log('no user?!!');
		return;
	}
	else { 
		$results       = ddm_unsubscription($user->data->user_email);	
	}
}


	
function my_automatewoo_action_ddm_order_unsubscribe( $workflow ) {
	
	$order = $workflow->data_layer()->get_order();
	if(!$order){ 
		write_log("no order?");
	}
	else { 
		write_log("get_billing_email:");
		write_log( $order->get_billing_email());
		ddm_unsubscription($order->get_billing_email()); 
	}
}


function ddm_validate_subscription( $email ){ 
	
	write_log('verifying subscription: ' . $email);
	
	$ws = new DadaMailWebService(
	    $GLOBALS['server'],
	    $GLOBALS['public_key'],
	    $GLOBALS['private_key']
	);
	$params = [
	    'addresses' => array(
	        [
	            'email'  => $email,
	        ]
	    ) 
	];
	$results  = $ws->request(
	    $GLOBALS['list'], 
	    'validate_subscription', 
	     $params
	);
	
	write_log(print_r($results, true) );
	
	return $results; 
}



function ddm_subscription( $email ){ 
	
	write_log('subscribing: ' . $email);
	
	$ws = new DadaMailWebService(
	    $GLOBALS['server'],
	    $GLOBALS['public_key'],
	    $GLOBALS['private_key']
	);
	$params = [
	    'addresses' => array(
	        [
	            'email'  => $email,
	        ]
	    ) 
	];
	$results  = $ws->request(
	    $GLOBALS['list'], 
	    'subscription', 
	     $params
	);
	
	write_log(print_r($results, true) );
	
	return $results; 
	
}




function ddm_unsubscription( $email ){ 
	
	write_log('unsubscribing: ' . $email);
	
	$ws = new DadaMailWebService(
	    $GLOBALS['server'],
	    $GLOBALS['public_key'],
	    $GLOBALS['private_key']
	);
	$params = [
	    'addresses' => array(
	        [
	            'email'  => $email,
	        ]
	    ) 
	];
	$results  = $ws->request(
	    $GLOBALS['list'], 
	    'unsubscription', 
	     $params
	);
	
	write_log(print_r($results, true) );
	
	return $results; 

}


/* This is good for AutomateWoo: */

function my_automatewoo_action_ddm_test_obj( $workflow ) {
	
	
	write_log("in: my_automatewoo_action_ddm_test_obj");	
	$customer      = $workflow->data_layer()->get_customer();
	
	if(!$customer){ 
		write_log("no customer");	
	} else {
		// DOES NOT WORK! NO!
		//$billing_email = $customer->get_billing_email();
		write_log("customer:");
		write_log(print_r($customer, true) );
	}

	$cart = $workflow->data_layer()->get_cart();
	if($cart){ 
		write_log("we have a cart:");
		write_log(print_r($cart, true) );
	}
	else { 
		write_log("no cart");
	}
	

/*	
	$guest = $workflow->data_layer()->get_guest();
	if($guest){ 
		write_log("we have a guest:");
		write_log(print_r($guest, true) );
	}
	else { 
		write_log("no guest");
	}
	
*/	
/*	
	$membership = $workflow->data_layer()->get_membership(); 
	if($membership){ 
		write_log("membership");
		write_log(print_r($membership, true) );
	}
	else { 
		write_log("no membership");
	}

*/
		
	$user = $workflow->data_layer()->get_user();
	if($user){ 

		// NO.
		// write_log("get_email");
		// write_log($user->get_email());
		
		// NO.
		// write_log("user->get()->user_email");
		// write_log($user->get()->user_email());
		
		
		// NO. 
		// $user_info = $user->get_userdata();
		// write_log("user_info->user_email");
		// write_log($user_info->user_email());
		
		// NO. 
		// $user_email = $user->user_email();
		// write_log("user->user_email()");
		// write_log($user->user_email());
		
		
		// NO. 
		// write_log('$user->ID()');
		// write_log($user->ID());

		//foreach ($user as $key => $value) {
		//    echo "$key => $value\n";
		//}

		// YES!
		// write_log('$user->data->user_email');	
		// write_log($user->data->user_email);
		

		write_log("we have a user:");
		write_log(print_r($user, true) );

	}
	else { 
		write_log("no user");
	}
	
	$order = $workflow->data_layer()->get_order();
	if($order){ 
		
		# THIS WORKS! YES!
		write_log("get_billing_email");
		write_log( $order->get_billing_email());
		
	//	write_log("user_email");
	//	write_log($order->user_email());
		
		write_log("we have an order:");
		write_log(print_r($order, true) );
	}
	else { 
		write_log("no order");
	}

	$subscription = $workflow->data_layer()->get_subscription();
	if($subscription){ 
		write_log("we have a subscription:");
		write_log(print_r($subscription, true) );
	}
	else { 
		write_log("no subscription");
	}
	
	
/*	
	$booking = $workflow->data_layer()->get_booking();
	if($booking){ 
		write_log("we have a booking:");
		write_log(print_r($booking, true) );
	}
	else { 
		write_log("no booking");
	}
*/	
	/*
	
	$order_item = $workflow->data_layer()->get_order_item();
	if($booking){ 
		write_log("we have a order_item:");
		write_log(print_r($order_item, true) );
	}
	else { 
		write_log("no order_item");
	}
	
*/	
	
//write_log(print_r($workflow, true) );


}
	
?>