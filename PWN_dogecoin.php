<?php
ob_start();
/* Authorize.net AIM Payment Gateway Class */
class PWN_Dogecoin extends WC_Payment_Gateway {

	// Setup our Gateway's id, description and other values
	function __construct() {

		// The global ID for this Payment method
		$this->id = "pwn_dogecoin_gateway";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = __( "Dogecoin Payments", 'pwn-dogecoin-gateway' );

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = __( "Dogecoin Payment Gateway Plug-in for WooCommerce", 'pwn-dogecoin-gateway' );
		
		

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = __( "Dogecoin gateway", 'pwn-dogecoin-gateway' );

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = null;

		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array( 'products' );

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		 $this->title = $this->get_option( 'title' );
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		// Lets check for SSL
		add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );
		
		// Save settings
		if ( is_admin() ) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} // End __construct()

	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'pwn-dogecoin-gateway' ),
				'label'		=> __( 'Enable this payment gateway', 'pwn-dogecoin-gateway' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'pwn-dogecoin-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'pwn-dogecoin-gateway' ),
				'default'	=> __( 'Dogecoin ', 'pwn-dogecoin-gateway' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'pwn-dogecoin-gateway' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'pwn-dogecoin-gateway' ),
				'default'	=> __( 'Pay securely using your Dogecoin Address.', 'pwn-dogecoin-gateway' ),
				'css'		=> 'max-width:350px;'
			),
			'apiLogin' => array(
				'title'		=> __( 'Dogecoin Address', 'pwn-dogecoin-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the API Login provided by Dogecoin when you signed up for an account.', 'pwn-dogecoin-gateway' ),
				
			),
			'trans_key' => array(
				'title'		=> __( 'Dogecoin Key', 'pwn-dogecoin-gateway' ),
				'type'		=> 'password',
				'desc_tip'	=> __( 'This is the Transaction Key provided by Dogecoin when you signed up for an account.', 'pwn-dogecoin-gateway' ),
			),
			'environment' => array(
				'title'		=> __( 'Dogecoin Test Mode', 'pwn-dogecoin-gateway' ),
				'label'		=> __( 'Enable Test Mode', 'pwn-dogecoin-gateway' ),
				'type'		=> 'checkbox',
				'description' => __( 'Place the payment gateway in test mode.', 'pwn-dogecoin-gateway' ),
				'default'	=> 'no',
			)
		);		
	}
	
	// Submit payment and handle response
	public function process_payment( $order_id ) {
		global $woocommerce;

		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order( $order_id );
        
		// Are we testing right now or is it a real transaction
		$environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';
		
		if(	$environment == TRUE){
		    
		    $order = wc_get_order( $order_id );

			WC()->session->set('myorder', $order->get_total());
			if($order){
			   $orderDetail = new WC_Order( $order_id );
				$orderDetail->update_status("wc-pending", 'Pending payment', TRUE);
			}
    		if ( $order->get_total() > 0 ) {
    			// Mark as processing or on-hold (payment won't be taken until delivery).
    			
    			
    			
                
    			$order->update_status( apply_filters( 'woocommerce_cod_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'Pending payment', $order ), __( 'Pending payment.', 'woocommerce' ) );  
    		} else {
    		//	$order->payment_complete();
    		}
    
    		// Remove cart.
    	//	WC()->cart->empty_cart();
  
    		// Return thankyou redirect.
    		return array(
    			'result'   => 'success',
    			'redirect' => get_bloginfo('url').'/thanks-order/?id='.$order_id.'&key='.base64_encode($this->get_return_url($order)),
    		);
		}
	}
	
	
	
	public function thankyou_page( $payment_id) {
         $my_fields = get_option('woocommerce_pwn_dogecoin_gateway_settings');

        if( 'pwn_dogecoin_gateway' === $payment_id ){
    		echo '<p>Dogecoin Payment Address</p>';
             echo '<p style="padding:5px;border:1px solid #f2f2f2;">'.$my_fields['apiLogin'].'</p>';
        }
	}
	
	// Validate fields
	public function validate_fields() {
		return true;
	}
	
	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check() {
			
	}
	
	
	
	
	

} 
