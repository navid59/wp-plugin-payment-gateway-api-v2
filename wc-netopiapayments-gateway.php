<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// include_once __DIR__ . '/vendor/autoload.php';
include_once('lib/log.php');
include_once('lib/request.php');

$request = new Request();
class netopiapayments extends WC_Payment_Gateway {
	// Setup our Gateway's id, description and other values
	function __construct() {
		$this->id 					= "netopiapayments";
		$this->method_title 		= __( "NETOPIA Payments", 'netopiapayments' );
		$this->method_description 	= __( "NETOPIA Payments V2 Plugin for WooCommerce", 'netopiapayments' );
		$this->title 				= __( "NETOPIA", 'netopiapayments' );
		$this->icon 				= NTP_PLUGIN_DIR . 'img/netopiapayments.gif';
		$this->has_fields 			= true;
		$this->notify_url        	= WC()->api_request_url( 'netopiapayments' );	// IPN URL - WC REST API
		
		/**
		 * Defination the plugin setting fiels in payment configuration
		 */
		$this->init_form_fields();
		
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		/**
		 * Define the checkNetopiapaymentsResponse methos as NETOPIA Payments IPN
		 */
		add_action('init', array(&$this, 'checkNetopiapaymentsResponse'));
		add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'checkNetopiapaymentsResponse' ) );

		// Save settings
		if ( is_admin() ) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * In Receipt page give short info to Buyer and then will start redirecting to payment page
		 */
		add_action('woocommerce_receipt_netopiapayments', array(&$this, 'receipt_page'));
	} 	

	/**
	 * Build the administration fields for this specific Gateway
	 */ 
	public function init_form_fields() {
		$this->form_fields = array(			
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'netopiapayments' ),
				'label'		=> __( 'Enable this payment gateway', 'netopiapayments' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'environment' => array(
				'title'		=> __( 'NETOPIA Payments Test Mode', 'netopiapayments' ),
				'label'		=> __( 'Enable Test Mode', 'netopiapayments' ),
				'type'		=> 'checkbox',
				'description' => __( 'Place the payment gateway in test mode.', 'netopiapayments' ),
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'netopiapayments' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'netopiapayments' ),
				'default'	=> __( 'NETOPIA Payments', 'netopiapayments' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'netopiapayments' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'netopiapayments' ),
				'css'		=> 'max-width:350px;',
			),
			'default_status' => array(
				'title'		=> __( 'Default status', 'netopiapayments' ),
				'type'		=> 'select',
				'desc_tip'	=> __( 'Default status of transaction.', 'netopiapayments' ),
				'default'	=> 'processing',
				'options' => array(
					'completed' => __('Completed'),
					'processing' => __('Processing'),
				),
				'css'		=> 'max-width:350px;',
			),
			'key_setting' => array(
                'title'       => __( 'Seller Account', 'netopiapayments' ),
                'type'        => 'title',
                'description' => '',
            ),
			'account_id' => array(
				'title'		=> __( 'Seller Account ID', 'netopiapayments' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Seller Account ID / Merchant POS identifier, is available in your NETOPIA account.', 'netopiapayments' ),
				'description'	=> __( 'Find it from NETOPIA Payments admin -> Seller Accounts -> Technical settings.', 'netopiapayments' ),
			),
			'live_api_key' => array(
				'title'		=> __( 'Live API Key: ', 'netopiapayments' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'In order to communicate with the payment API, you need a specific API KEY.', 'netopiapayments' ),
				'description' => __( 'Generate / Find it from NETOPIA Payments admin -> Profile -> Security', 'netopiapayments' ),
			),	
			'sandbox_api_key' => array(
				'title'		=> __( 'Sandbox API Key: ', 'netopiapayments' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'In order to communicate with the payment API, you need a specific API KEY.', 'netopiapayments' ),
				'description' => __( 'Generate / Find it from NETOPIA Payments admin -> Profile -> Security', 'netopiapayments' ),
			),
		);		
	}

	/**
	 * Display Method of payment in checkout page
	 */
	function payment_fields() {
		// Description of payment method from settings
      	if ( $this->description ) { ?>
        	<p><?php echo $this->description; ?></p>
  		<?php }
  		
  		$payment_methods = array('credit_card');
  		$name_methods = array(
		          'credit_card'	      => __( 'Credit Card - Api v2', 'netopiapayments' )
		          );
  		?>
  		<div id="netopia-methods">
	  		<ul>
	  		<?php  foreach ($payment_methods as $method) { ?>
	  			<?php 
	  			$checked ='';
	  			if($method == 'credit_card') $checked = 'checked="checked"';
	  			?>
	  				<li>
	  					<input type="radio" name="netopia_method_pay" class="netopia-method-pay" id="netopia-method-<?=$method?>" value="<?=$method?>" <?php echo $checked; ?> /><label for="inspire-use-stored-payment-info-yes" style="display: inline;"><?php echo $name_methods[$method] ?></label>
	  				</li> 			
	  		<?php } ?>
	  		</ul>
  		</div>

  		<style type="text/css">
  			#netopia-methods{display: inline-block;}
  			#netopia-methods ul{margin: 0;}
  			#netopia-methods ul li{list-style-type: none;}
		</style>
		<script type="text/javascript">
			jQuery(document).ready(function($){				
				var method_ = $('input[name=netopia_method_pay]:checked').val();
				$('.billing-shipping').show('slow');
			});
		</script>
  		<?php
  	}

  	// Submit payment
	public function process_payment( $order_id ) {
		global $woocommerce;		
		$order = new WC_Order( $order_id );			
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '>=' ) ) {
				/* 2.1.0 */
				$checkout_payment_url = $order->get_checkout_payment_url( true );
			} else {
				/* 2.0.0 */
				$checkout_payment_url = get_permalink( get_option ( 'woocommerce_pay_page_id' ) );
			}

			$method = $this->get_post( 'netopia_method_pay' );
			return array(
				'result' => 'success', 
				'redirect' => add_query_arg(
					'method', 
					$method, 
					add_query_arg(
						'key', 
						$order->get_order_key(), 
						$checkout_payment_url						
					)
				)
        	);
    }

	
	/**
	 * Validate fields
	 */
	public function validate_fields() {
		$method_pay            = $this->get_post( 'netopia_method_pay' );
		// Check card number
		if ( empty( $method_pay ) ) {
			wc_add_notice( __( 'Alege metoda de plata.', 'netopiapayments' ), $notice_type = 'error' );
			return false;
		}
		return true;
	}

  	/**
	* Receipt Page
	**/
	function receipt_page($order){
		$customer_order = new WC_Order( $order );
		echo '<p>'.__('Multumim pentru comanda, te redirectionam in pagina de plata NETOPIA payments.', 'netopiapayments').'</p>';
		echo '<p><strong>'.__('Total', 'netopiapayments').": ".$customer_order->get_total().' '.$customer_order->get_currency().'</strong></p>';
		echo $this->generateNetopiaPaymentLink($order);
	}

	/**
	* Generate payment Link / Payment button And redirect
	**/
	function generateNetopiaPaymentLink($order_id){
		global $woocommerce;

		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order( $order_id );
		$user = new WP_User( $customer_order->get_user_id());
		
		$request = new Request();
		$request->posSignature  = $this->account_id;                									// Your signiture ID hear
		$request->isLive        = $this->isLive($this->environment);
		if($request->isLive ) {
			$request->apiKey = $this->live_api_key;						// Live API key
		} else {
			$request->apiKey = $this->sandbox_api_key;					// Sandbox API key
		}
		$request->notifyUrl     = $this->notify_url;	            									// Your IPN URL
		$request->redirectUrl   = htmlentities(WC_Payment_Gateway::get_return_url( $customer_order ));  // Your backURL

		/**
		 * Prepare json for start action
		 */

		/** - Config section  */
		$configData = [
			'emailTemplate' => "",
			'notifyUrl'     => $request->notifyUrl,
			'redirectUrl'   => $request->redirectUrl,
			'language'      => "RO"
			];
		
		// /** - 3DS section  */
 		// $threeDSecusreData =  array(); 

		 /** - Order section  */
		$orderData = new \StdClass();
		
		$orderData->description             = "Wordpress - api2 - plugin_". rand(1000,9999);
		$orderData->orderID                 = $customer_order->get_order_number();
		$orderData->amount                  = $customer_order->get_total();
		$orderData->currency                = $customer_order->get_currency();

		$orderData->billing                 = new \StdClass();
		$orderData->billing->email          = $customer_order->get_billing_email();
		$orderData->billing->phone          = $customer_order->get_billing_phone();
		$orderData->billing->firstName      = $customer_order->get_billing_first_name();
		$orderData->billing->lastName       = $customer_order->get_billing_last_name();
		$orderData->billing->city           = $customer_order->get_billing_city();
		$orderData->billing->country        = 642;
		$orderData->billing->state          = $customer_order->get_billing_state();
		$orderData->billing->postalCode     = $customer_order->get_billing_postcode();

		$billingFullStr = $customer_order->get_billing_country() 
			.' , '.$orderData->billing->city
			.' , '.$orderData->billing->state
			.' , '.$customer_order->get_billing_address_1() . $customer_order->get_billing_address_2()
			.' , '.$orderData->billing->postalCode;
		$orderData->billing->details        = !empty($customer_order->get_customer_note()) ?  $customer_order->get_customer_note() . " | ". $billingFullStr : $billingFullStr;

		$orderData->shipping                = new \StdClass();
		$orderData->shipping->email         = $customer_order->get_billing_email();			// As default there is no shiping email, so use billing email
		$orderData->shipping->phone         = $customer_order->get_billing_phone();			// As default there is no shiping phone, so use billing phone
		$orderData->shipping->firstName     = $customer_order->get_shipping_first_name();
		$orderData->shipping->lastName      = $customer_order->get_shipping_last_name();
		$orderData->shipping->city          = $customer_order->get_shipping_city();
		$orderData->shipping->country       = 642 ;
		$orderData->shipping->state         = $customer_order->get_shipping_state();
		$orderData->shipping->postalCode    = $customer_order->get_shipping_postcode();

		$shippingFullStr = $customer_order->get_shipping_country() 
			.' , '.$orderData->shipping->city
			.' , '.$orderData->shipping->state
			.' , '.$customer_order->get_shipping_address_1() . $customer_order->get_shipping_address_2()
			.' , '.$orderData->shipping->postalCode;
		$orderData->shipping->details       = !empty($customer_order->get_customer_note()) ?  $customer_order->get_customer_note() . " | ". $shippingFullStr : $shippingFullStr;
		
		$orderData->products                = $this->getCartSummary(); // It's JSON

		/**	Add Woocomerce & Wordpress version to request*/
		$orderData->data				 	= new \StdClass();
		$orderData->data->wordpress 		= $this->getWpInfo();
		$orderData->data->wooCommerce 		= $this->getWooInfo();	

		/**
		 * Assign values and generate Json
		 */
		$request->jsonRequest = $request->setRequest($configData, $orderData);

		/**
		 * Send Json to Start action 
		 */
		$startResult = $request->startPayment();


		/**
		 * Result of start action is in jason format
		 * get PaymentURL & do redirect
		 */
		
		$resultObj = json_decode($startResult);
		
		switch($resultObj->status) {
			case 1:
				echo "<pre>";
				print_r($resultObj);
				echo "</pre>";
				if($resultObj->code == 200 &&  !is_null($resultObj->data->payment->paymentURL)) {
					$parsUrl = parse_url($resultObj->data->payment->paymentURL);
					$actionStr = $parsUrl['scheme'].'://'.$parsUrl['host'].$parsUrl['path'];
					parse_str($parsUrl['query'], $queryParams);
					$formAttributes = '';
					foreach($queryParams as $key => $val) {
						$formAttributes .= '<input type="hidden" name ="'.$key.'" value="'.$val.'">
						';
					}
					echo "<pre>";
					var_dump($parsUrl);
					echo "</pre>";
					try {						
						return '<form action="'.$actionStr.'" method="get" id="frmPaymentRedirect">
								'.$formAttributes.'
								<input type="submit" class="button-alt" id="submit_netopia_payment_form" value="'.__('Plateste prin NETOPIA payments', 'netopiapayments').'" />
								<a class="button cancel" href="'.$customer_order->get_cancel_order_url().'">'.__('Anuleaza comanda &amp; goleste cosul', 'netopiapayments').'</a>
								<script type="text/javascript">
								jQuery(function(){
								jQuery("body").block({
									message: "'.__('Iti multumim pentru comanda. Te redirectionam catre NETOPIA payments pentru plata.', 'netopiapayments').'",
									overlayCSS: {
										background		: "#fff",
										opacity			: 0.6
									},
									css: {
										padding			: 20,
										textAlign		: "center",
										color			: "#555",
										border			: "3px solid #aaa",
										backgroundColor	: "#fff",
										cursor			: "wait",
										lineHeight		: "32px"
									}
								});
								// jQuery("#submit_netopia_payment_form").click();});
								</script>
							</form>';
						} catch (\Exception $e) {
							echo '<p><i style="color:red">Asigura-te ca ai completat configurari in setarii,pentru mediul sandbox si live!. Citeste cu atentie instructiunile din manual!</i></p>
								 <p style="font-size:small">Ai in continuare probleme? Trimite-ne doua screenshot-uri la <a href="mailto:implementare@netopia.ro">implementare@netopia.ro</a>, unul cu setarile metodei de plata din adminul wordpress.</p>';
						}
				}else {
					echo $resultObj->message;
				}
			break;
			default:
				echo "<pre>";
				print_r($resultObj);
				echo "<hr>";
				echo "AAAAAAAAAAAAAAAAAAAAAABBBBBBBBBBBBBB";
				echo "</pre>";
			break;
		}
	}	

	/**
	* Check for valid NETOPIA server callback
	* This is the IPN for new plugin
	**/
	function checkNetopiapaymentsResponse(){
		//die("IPN IPN in NEW PLUGIN");

		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		include_once('lib/log.php');
		include_once('lib/ipn.php');

		require_once 'vendor/autoload.php';


		// Log
		$setRealTimeLog = ["IPN"    =>  "IPN Is hitting"];
		log::setRealTimeLog($setRealTimeLog);
		log::logHeader();

		// /**
		//  * get defined keys
		//  */
		$ntpIpn = new IPN();

		$ntpIpn->activeKey         = '1PD2-FYKC-R27B-55BW-NVGN'; // activeKey or posSignature
		$ntpIpn->posSignatureSet[] = '1PD2-FYKC-R27B-55BW-NVGN'; // The active key should be in posSignatureSet as well
		$ntpIpn->posSignatureSet[] = 'FAKE-FAKE-FAKE-FAKE-FAKE'; 
		$ntpIpn->posSignatureSet[] = 'FAKE-FAKE-FAKE-FAKE-FAKE'; 
		$ntpIpn->posSignatureSet[] = 'FAKE-FAKE-FAKE-FAKE-FAKE';
		$ntpIpn->hashMethod        = 'SHA512';
		$ntpIpn->alg               = 'RS512';
		

		$ntpIpn->publicKeyStr = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAy6pUDAFLVul4y499gz1P\ngGSvTSc82U3/ih3e5FDUs/F0Jvfzc4cew8TrBDrw7Y+AYZS37D2i+Xi5nYpzQpu7\nryS4W+qvgAA1SEjiU1Sk2a4+A1HeH+vfZo0gDrIYTh2NSAQnDSDxk5T475ukSSwX\nL9tYwO6CpdAv3BtpMT5YhyS3ipgPEnGIQKXjh8GMgLSmRFbgoCTRWlCvu7XOg94N\nfS8l4it2qrEldU8VEdfPDfFLlxl3lUoLEmCncCjmF1wRVtk4cNu+WtWQ4mBgxpt0\ntX2aJkqp4PV3o5kI4bqHq/MS7HVJ7yxtj/p8kawlVYipGsQj3ypgltQ3bnYV/LRq\n8QIDAQAB\n-----END PUBLIC KEY-----\n";

		$ipnResponse = $ntpIpn->verifyIPN();


		/**
		 * IPN Output
		 */
		echo json_encode($ipnResponse);
		
		die();
	}

	/**
	* Check for valid NETOPIA server callback
	* This is the IPN in old version
	**/
	// function check_netopiapayments_response(){
		// die("IPN IPN in OLD Plugin");

		// global $woocommerce;

		// require_once 'netopia/Payment/Request/Abstract.php';
		// require_once 'netopia/Payment/Request/Card.php';
		// require_once 'netopia/Payment/Request/Notify.php';
		// require_once 'netopia/Payment/Invoice.php';
		// require_once 'netopia/Payment/Address.php';

		// $errorCode 		= 0;
		// $errorType		= Netopia_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_NONE;
		// $errorMessage	= '';
		// $env_key    = $_POST['env_key'];
		// $data       = $_POST['data'];
		// $cipher     = 'rc4';
		// $iv         = null;
		// if(array_key_exists('cipher', $_POST))
		// {
		// 	$cipher = $_POST['cipher'];
		// 	if(array_key_exists('iv', $_POST))
		// 	{
		// 		$iv = $_POST['iv'];
		// 	}
		// }
		
		// $msg_errors = array('16'=>'card has a risk (i.e. stolen card)', '17'=>'card number is incorrect', '18'=>'closed card', '19'=>'card is expired', '20'=>'insufficient funds', '21'=>'cVV2 code incorrect', '22'=>'issuer is unavailable', '32'=>'amount is incorrect', '33'=>'currency is incorrect', '34'=>'transaction not permitted to cardholder', '35'=>'transaction declined', '36'=>'transaction rejected by antifraud filters', '37'=>'transaction declined (breaking the law)', '38'=>'transaction declined', '48'=>'invalid request', '49'=>'duplicate PREAUTH', '50'=>'duplicate AUTH', '51'=>'you can only CANCEL a preauth order', '52'=>'you can only CONFIRM a preauth order', '53'=>'you can only CREDIT a confirmed order', '54'=>'credit amount is higher than auth amount', '55'=>'capture amount is higher than preauth amount', '56'=>'duplicate request', '99'=>'generic error');
		
		// if ($this->environment == 'yes') {
		// 	$privateKeyFilePath 	= plugin_dir_path( __FILE__ ).'netopia/certificate/sandbox.'.$this->account_id.'private.key';
		// }
		// else {
		// 	$privateKeyFilePath 	= plugin_dir_path( __FILE__ ).'netopia/certificate/live.'.$this->account_id.'private.key';
		// }

		// if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0){
		// 	if(isset($_POST['env_key']) && isset($_POST['data'])){
		// 		try
		// 		{
		// 			$objPmReq = Netopia_Payment_Request_Abstract::factoryFromEncrypted($_POST['env_key'], $_POST['data'], $privateKeyFilePath, null, $cipher, $iv);
		// 			$action = $objPmReq->objPmNotify->action;
		// 			$params = $objPmReq->params;
		// 			$order = new WC_Order( $params['order_id'] );
		// 			$user = new WP_User( $params['customer_id'] );
		// 			$transaction_id = $objPmReq->objPmNotify->purchaseId;
		// 			if($objPmReq->objPmNotify->errorCode==0){
		// 				switch($action)
		// 	    		{
		// 	    			case 'confirmed':
		// 						#cand action este confirmed avem certitudinea ca banii au plecat din contul posesorului de card si facem update al starii comenzii si livrarea produsului
		// 						//update DB, SET status = "confirmed/captured"
		// 						$errorMessage = $objPmReq->objPmNotify->errorMessage;
								
		// 						$amountorder_RON = $objPmReq->objPmNotify->originalAmount; 
		// 						$amount_paid = is_null($objPmReq->objPmNotify->originalAmount) ? 0:$objPmReq->objPmNotify->originalAmount;
								
		// 						//original_amount -> the original amount processed;
		// 						//processed_amount -> the processed amount at the moment of the response. It can be lower than the original amount, ie for capturing a smaller amount or for a partial credit
		// 						if( $order->get_status() != 'completed' ) {
		// 							if( $amount_paid < $amountorder_RON ) {
		// 								//Update the order status
		// 								$order->update_status('on-hold', '');

		// 								//Error Note
		// 								$message = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
		// 								$message_type = 'notice';

		// 								//Add Customer Order Note
		// 								$order->add_order_note($message.'<br />Netopia Transaction ID: '.$transaction_id, 1);

		// 								//Add Admin Order Note
		// 								$order->add_order_note('Look into this order. <br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was &#8358; '.$amount_paid.' RON while the total order amount is &#8358; '.$amountorder_RON.' RON<br />Netopia Transaction ID: '.$transaction_id);

		// 								// Reduce stock levels
		// 								wc_reduce_stock_levels($order->get_id());

		// 								// Empty cart
		// 								wc_empty_cart();
		// 							}
		// 						else {
		// 							if( $order->get_status() == 'processing' ) {
		// 			                    $order->add_order_note('Plata prin NETOPIA payments<br />Transaction ID: '.$transaction_id);

		// 			                    //Add customer order note
		// 			 					$order->add_order_note('Plata receptionata.<br />Comanda este in curs de procesare.<br />Vom face livrarea in curand.<br />NETOPIA Transaction ID: '.$transaction_id, 1);

		// 								// Reduce stock levels
		// 								wc_reduce_stock_levels($order->get_id());

		// 								// Empty cart
		// 								wc_empty_cart();

		// 								//$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.';
		// 								//$message_type = 'success';
		// 			                }
		// 			                else {
		// 			                	if( $order->has_downloadable_item() ) {

		// 			                		//Update order status
		// 									$order->update_status( 'completed', 'Payment received, your order is now complete.' );

		// 				                    //Add admin order note
		// 				                    $order->add_order_note('Plata prin NETOPIA payments<br />Transaction ID: '.$transaction_id);

		// 				                    //Add customer order note
		// 				 					$order->add_order_note('Payment Received.<br />Your order is now complete.<br />NETOPIA Transaction ID: '.$transaction_id, 1);

		// 									//$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is now complete.';
		// 									//$message_type = 'success';

		// 			                	}
		// 			                	else {

		// 			                		//Update order status
		// 									$msgDefaultStatus = ($this->default_status == 'processing') ? 'Payment received, your order is currently being processed.' : 'Payment received, your order is now complete.';
		// 									$order->update_status( $this->default_status, $msgDefaultStatus );

		// 									//Add admin order noote
		// 				                    $order->add_order_note('Plata prin NETOPIA payments<br />Transaction ID: '.$transaction_id);

		// 				                    //Add customer order note
		// 									$order->add_order_note($msgDefaultStatus.'<br />NETOPIA Transaction ID: '.$transaction_id, 1);

		// 									$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.';
		// 									$message_type = 'success';
		// 			                	}

		// 								// Reduce stock levels
		// 								wc_reduce_stock_levels($order->get_id());

		// 								// Empty cart
		// 								wc_empty_cart();
		// 			                }
		// 			            }
		// 					}
		// 					else {}
		// 						break;
		// 					case 'paid':
		// 						//Update order status -> to be added, but on-hold should work for now
		// 						$order->update_status( 'on-hold', 'Your payment is currently being processed.' );
		// 						//Add admin order note
		// 				        $order->add_order_note('Payment remotely accepted via NETOPIA, make sure to capture it<br />Transaction ID: '.$transaction_id);
		// 						break;	
		// 					case 'confirmed_pending':
		// 						//Update order status
		// 						$order->update_status( 'on-hold', 'Your payment is currently being processed.' );
		// 						//Add admin order note
		// 				        $order->add_order_note('Payment pending via NETOPIA<br />Transaction ID: '.$transaction_id);
		// 						break;
		// 					case 'paid_pending':
		// 						//Update order status
		// 						$order->update_status( 'on-hold', 'Your payment is currently being processed.' );
		// 						//Add admin order note
		// 				        $order->add_order_note('Payment pending via NETOPIA<br />Transaction ID: '.$transaction_id);
		// 						break;
		// 				    case 'canceled':
		// 						#cand action este canceled inseamna ca tranzactia este anulata. Nu facem livrare/expediere.
		// 						//update DB, SET status = "canceled"
		// 						$errorMessage = $objPmReq->objPmNotify->errorMessage;							

		// 						$message = 	'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t received.';
		// 						//Add Customer Order Note
		// 	                   	$order->add_order_note($message.'<br />NETOPIA Transaction ID: '.$transaction_id, 1);

		// 	                    //Add Admin Order Note
		// 	                  	$order->add_order_note($message.'<br />NETOPIA Transaction ID: '.$transaction_id);

		// 		                //Update the order status
		// 						$order->update_status('cancelled', '');
		// 					    break;
		// 					case 'credit':
		// 						#cand action este credit inseamna ca banii sunt returnati posesorului de card. Daca s-a facut deja livrare, aceasta trebuie oprita sau facut un reverse. 
		// 						//update DB, SET status = "refunded"
		// 						if ($objPmReq->invoice->currency != 'RON') {
		// 							$rata_schimb = $objPmReq->objPmNotify->originalAmount/$objPmReq->invoice->amount;
		// 							}
		// 							else $rata_schimb = 1;
		// 						$refund_amount = $objPmReq->objPmNotify->processedAmount/$rata_schimb;

		// 						$args = array( 
		// 							'amount' => $refund_amount,  
		// 							'reason' => 'Netopia call',  
		// 							'order_id' => $params['order_id'],  
		// 							'refund_id' => null,  
		// 							'line_items' => array(),  
		// 							'refund_payment' => false,  
		// 							'restock_items' => false  
		// 							 ); 
									 
		// 						$refund = wc_create_refund($args);	
								 
		// 						$errorMessage = $objPmReq->objPmNotify->errorMessage;
		// 						$message = 	'Plata rambursata.';
		// 						//Add Customer Order Note
		// 	                   	$order->add_order_note($message.'<br />NETOPIA Transaction ID: '.$transaction_id, 1);

		// 	                    //Add Admin Order Note
		// 	                  	$order->add_order_note($message.'<br />NETOPIA Transaction ID: '.$transaction_id);

		// 						//Update the order status if fully refunded
		// 						if ($refund_amount == $objPmReq->objPmNotify->originalAmount) {
		// 						$order->update_status('refunded', '');
		// 						}
		// 					    break;	
		// 	    		}
		// 			}else{
		// 				$order->update_status('failed', '');

		// 				//Error Note
		// 				$message = $objPmReq->objPmNotify->errorMessage;
		// 				if(empty($message) && isset($msg_errors[$objPmReq->objPmNotify->errorCode])) $message = $msg_errors[$objPmReq->objPmNotify->errorCode];
		// 				$message_type = 'error';
		// 				//Add Customer Order Note
	    //                 $order->add_order_note($message.'<br />NETOPIA Transaction ID: '.$transaction_id, 1);
		// 			}					
		// 		}catch(Exception $e)
		// 		{
		// 			$errorType 		= Netopia_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_TEMPORARY;
		// 			$errorCode		= $e->getCode();
		// 			$errorMessage 	= $e->getMessage();
		// 		}
		// 	}else
		// 	{
		// 		$errorType 		= Netopia_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
		// 		$errorCode		= Netopia_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_POST_PARAMETERS;
		// 		$errorMessage 	= 'NETOPIA posted invalid parameters';
		// 	}
		// }else 
		// {
		// 	$errorType 		= Netopia_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
		// 	$errorCode		= Netopia_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_POST_METHOD;
		// 	$errorMessage 	= 'invalid request method for payment confirmation';
		// }
		
		// header('Content-type: application/xml');
		// echo <?xml version=\"1.0\" encoding=\"utf-8\">\n";
		// if($errorCode == 0)
		// {
		// 	echo "<crc>{$errorMessage}</crc>";
		// }
		// else
		// {
		// 	echo "<crc error_type=\"{$errorType}\" error_code=\"{$errorCode}\">{$errorMessage}</crc>";
			
		// }	
		// die();
	//}


	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check() {
		if( $this->enabled == "yes" ) {
			if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
				echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";	
			}
		}		
	}

	/**
	 * Get post data if set
	 */
	private function get_post( $name ) {
		if ( isset( $_REQUEST[ $name ] ) ) {
			return $_REQUEST[ $name ];
		}
		return null;
	}

	/**
	 * 
	 */
	public function ntpLog($contents){	
		$file = dirname(__FILE__).'/ntpDebugging_'.date('y-m-d').'.txt';	
		
		if (is_array($contents))
			$contents = var_export($contents, true);	
		else if (is_object($contents))
			$contents = json_encode($contents);
			
		file_put_contents($file, date('m-d H:i:s').$contents."\n", FILE_APPEND);
	}

	/**
	 * Save fields (Payment configuration) in DB
	 */
    public function process_admin_options() {
        $this->init_settings();
        $post_data = $this->get_post_data();
        // $cerValidation = $this->cerValidation();

        foreach ( $this->get_form_fields() as $key => $field ) {
            if ( ('title' !== $this->get_field_type( $field )) && ('file' !== $this->get_field_type( $field ))) {
                try {
                    $this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
                } catch ( Exception $e ) {
                    $this->add_error( $e->getMessage() );
                }
            }
        }
        return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
    }

	/**
	 * 
	 */
    private function _canManageWcSettings() {
        return current_user_can('manage_woocommerce');
	}

	/**
	 * 
	 */
	public function getCartSummary() {	
		$cartArr = WC()->cart->get_cart();
		$i = 0;	
		$cartSummary = array();	
		foreach ($cartArr as $key => $value ) {
			$cartSummary[$i]['name'] 				=  $value['data']->get_name();
			$cartSummary[$i]['code'] 				=  $value['data']->get_sku();
			$cartSummary[$i]['price'] 				=  floatval($value['data']->get_price());
			$cartSummary[$i]['quantity'] 			=  $value['quantity'];	
			$cartSummary[$i]['short_description'] 	=  !is_null($value['data']->get_short_description()) || !empty($value['data']->get_short_description()) ? substr($value['data']->get_short_description(), 0, 100) : 'no description';
			$i++;	
		}	
		return $cartSummary;	
	}	

	/**
	 * 
	 */
	public function getWpInfo() {	
		global $wp_version;	
		return 'Version '.$wp_version;	
	}

	/**
	 * 
	 */
	public function getWooInfo() {	
		$wooCommerce_ver = WC()->version;	
		return 'Version '.$wooCommerce_ver;	
	}

	/**
	 * 
	 */
	public function isLive($environment) {
		if ( $environment == 'no' ) {
			return true;
		} else {
			return false;
		}
	}
}