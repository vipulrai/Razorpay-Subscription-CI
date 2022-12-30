<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct(){
	    error_reporting(0);
        parent::__construct();
        $this->load->library(array('form_validation','session','cart'));
		//$this->load->database();
    } 
	public function index(){	   
		$this->load->view('subscription');		
	}
	
	public function initiateSubscriptions() {
    $json = array();
    $firstName 	= $this->input->post('full_name');
    $email 		= $this->input->post('pay_email');
    $contactNum = $this->input->post('pay_phone');
    $address 	= $this->input->post('pay_address');
    if (empty($firstName)) {
        $json['error']['fullname'] = 'Please enter full name.';
    }
    if (empty(trim($email))) {
        $json['error']['email'] = 'Please enter valid email.';
    } else if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
        $json['error']['email'] = 'Please enter valid email.';
    }
    if (empty(trim($contactNum))) {
        $json['error']['contactno'] = 'Please enter valid contact no.';
    } else if (strlen($contactNum) < 7 || !is_numeric($contactNum)) {
        $json['error']['contactno'] = 'Please enter valid contact no.';
    }
    if (empty($address)) {
        $json['error']['address'] = 'Please enter address';
    }
    if(empty($json['error'])){          
           
            $planID = 'plan_JDgG3Q5GGeusiL'; // CReate From Razorpay Dashboard, Subscriptions->Plans //           
            $note = 'CodyPaste Write Anything';           
            //$offer_id = 'offer_Ky3C9fbr3qg4Ju'; // Not mandatory, but if you have any offer in Razorpay Dashboard, you can mention here. //
            
            $subscriptionData = array(
                'plan_id' => $planID,
                'customer_notify' => 1,
                'total_count' => '1000',
                /*'addons' => 
                  array (
                    0 => 
                    array (
                      'item' => 
                      array (
                        'name' => 'Coupon Discount',
                        'amount' => '500',
                        'currency' => 'INR',
                      ),
                    ),
                  ),*/
                  
                'offer_id'=> $offer_id,
                'notes' => array(
                    'name' => $note,
                ),
            );
            $ch = $this->get_curl_handle_subscriptions($subscriptionData); // Method/Function created below with Razorpay Key and Secret //
            $result = curl_exec($ch);
			
			//print_r($result); die();
            
            $data = json_decode($result);
            $json['subscription_id'] = $data->id;
            $json['plan_id'] = $data->plan_id;
            // store value in session
            $this->session->set_userdata(
                array(
                    'subscription_id' => $data->id,
                    'plan_id' => $data->plan_id,
                    'created_at' => $data->created_at,
                    'charge_at' => $data->charge_at,
                    'start_at' => $data->start_at,
                    'offer_id' => $data->offer_id,
                )
            );

        
    }
		$this->output->set_header('Content-Type: application/json');
		echo json_encode($json);
	}
	
	// initialized cURL Request subscription
	private function get_curl_handle_subscriptions($subscriptionData) {
		$url = 'https://api.razorpay.com/v1/subscriptions';
		$key_id = 'rzp_test_PgK1AWmEYNiWMd'; // Generate from Razorpay Dashboard, Setting -> API Keys
		$key_secret = '6jP4b9WrnKt5JDyDMmJH7zdE'; // Generate from Razorpay Dashboard, Setting -> API Keys
		//cURL Request
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 1);
		$data = $subscriptionData;
		$params = http_build_query($data);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		return $ch;
	}
	
	    // create subscription first time
public function createSubscription() {
    $fullName 	= $this->input->post('full_name');
    $email 		= $this->input->post('pay_email');
    $contactNum = $this->input->post('pay_phone');
    $address 	= $this->input->post('pay_address');
	
	$totalamount = '100';
	$productinfo = 'ORDERID-01';
	$surl = base_url().'subscription-thankyou';
    $furl = base_url().'subscription-failed';
	$orderid = 'ORD-01';
	
    $dataFlesh = array(
        'txnid' => time(),
        'card_holder_name' 	=> $fullName,
        'amount' 			=> $totalamount,
        'email' 			=> $email,
        'phone' 			=> $contactNum,
        'productinfo' 		=> $productinfo,
        'surl' 				=> $surl,
        'furl' 				=> $furl,
        'currency_code' 	=> 'INR',
        'order_id' 			=> $orderid,
        'lang' 				=> 'en',
        'store_name' 		=> 'Cody Paste',
        'return_url' 		=> site_url() . 'home/callbacksubscriptions',
        'payment_type' 		=> 'create_subscriptions',
        'subscription_id' 	=> $this->session->userdata('subscription_id'), // Session craeted at 'initiateSubscriptions()'
        'plan_id' 			=> $this->session->userdata('plan_id'),
        'created_at' 		=> $this->session->userdata('created_at'),
        'charge_at' 		=> $this->session->userdata('charge_at'),
        'date_end_plan_at' 	=> strtotime("+10 years", $this->session->userdata('charge_at')),
        'start_at' 			=> $this->session->userdata('start_at'),
        'package' 			=> 'CodyPaste Subsription',
        'price' 			=> '100',
        'package_plan_id' 	=> 'plan_JDgG3Q5GGeusiL',
        'package_type' 		=> 'Monthly', // it could be 'Days'
        'offer_id' 			=> $this->session->userdata('offer_id'), // Session craeted at 'initiateSubscriptions()'
    );

    $this->session->set_userdata('subscription_ci_seesion_key', $dataFlesh); 
    $payInfo = $dataFlesh;
    $json['payInfo'] = $payInfo;
    $json['msg'] = 'success';
    //$this->output->set_header('Content-Type: application/json');
    $this->load->view('Razorpay-Hidden-Form', $json);
	
	//--END -->
    
   
    //--======-----After that You can Insert in Database table for creating order from below === -->
		//----
	     
	}
	
	
	
	// callback method
public function callbacksubscriptions() {
    if (!empty($this->input->post('razorpay_payment_id')) && !empty($this->input->post('merchant_order_id'))) {
        $success = true;
        $error = '';
        try {
            // store temprary data
            $dataFlesh = array(
                'txnid' => $this->input->post('merchant_trans_id'),
                'card_holder_name' => $this->input->post('card_holder_name_id'),
                'productinfo' => $this->input->post('merchant_product_info_id'),
                'surl' => $this->input->post('merchant_surl_id'),
                'furl' => $this->input->post('merchant_furl_id'),
                'order_id' => $this->input->post('merchant_order_id'),
                'razorpay_payment_id' => $this->input->post('razorpay_payment_id'),
                'merchant_subscription_id' => $this->input->post('merchant_subscription_id'),
                'merchant_amount' => $this->input->post('merchant_amount'),
                'merchant_plan_id' => $this->input->post('merchant_plan_id'),
                'created_at' => time(),
            );
            $this->session->set_flashdata('paymentInfo', $dataFlesh);
            
            $this->session->set_userdata('paymentInfoReturn', $dataFlesh);
            
            
        } catch (Exception $e) {
            $success = false;
            $error = 'Request to Razorpay Failed';
        }
        if ($success === true) {
            if (!empty($this->session->userdata('ci_subscription_keys'))) {
                $this->session->unset_userdata('ci_subscription_keys');
            }
            if (!empty($order_info['order_status_id'])) {
                redirect($this->input->post('merchant_surl_id'));
            } else {
                redirect($this->input->post('merchant_surl_id'));
            }
        } else {
            redirect($this->input->post('merchant_furl_id'));
        }
    } else {
        echo 'An error occured. Contact site administrator, please!';
    }
	}
	
	public function subscription_thankyou(){
		echo'Thankyou For Subscription<br>';
		echo 'Subscription ID: '.$this->session->userdata('subscription_id');
		
	}
	public function subscription_failed(){
		echo'Sorry! Try Again.';
	}
	
}
