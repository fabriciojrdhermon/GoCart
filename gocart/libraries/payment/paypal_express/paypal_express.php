<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Paypal_express
{
	var $CI;
	
	//this can be used in several places
	var	$method_name	= 'Paypal Express';
	
	function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->CI->load->library('payment/paypal_express/paypal');
		$this->CI->load->library('payment/paypal_express/httprequest');
	}
	
	/*
	checkout_form()
	this function returns an array, the first part being the name of the payment type
	that will show up beside the radio button the next value will be the actual form if there is no form, then it should equal false
	there is also the posibility that this payment method is not approved for this purchase. in that case, it should return a blank array 
	*/
	
	//these are the front end form and check functions
	function checkout_form($post = false)
	{
		$settings	= $this->CI->Settings_model->get_settings('paypal_express');
		$enabled	= $settings['enabled'];
		
		$form			= array();
		if($enabled)
		{
			$form['name']	= $this->method_name;
			
			//retrieve form contents
			ob_start();
			include(APPPATH."libraries/payment/paypal_express/forms/checkout.php");
			$form['form'] = ob_get_contents();
			ob_end_clean(); 
		} else return array();
		
		return $form;
	}
	
	
	function checkout_check()
	{
		// Nothing to check in this module
		return false;
	}
	
	function description()
	{
		return 'Paypal Express';
	}
	
	//back end installation functions
	function install()
	{
		
		$config['username'] = "Paypal username";
		$config['password'] = "Paypal password"; 
		$config['signature'] = "Paypal API signature";
		$config['currency'] = "USD";
		
		$config['return_url'] = "pp_gate/pp_return/";
		$config['cancel_url'] = "pp_gate/pp_cancel/";
		$config['SANDBOX'] = true;
		
		$config['enabled'] = "0";

		$this->CI->Settings_model->save_settings('paypal_express', $config);
	}
	
	function uninstall()
	{
		$this->CI->Settings_model->delete_settings('paypal_express');
	}
	
	//payment processor
	function process_payment()
	{
		$process	= false;
		
		$store		= $this->CI->config->item('company_name');
		// this will forward the page to the paypal interface, leaving gocart behind
		// the user will be sent back and authenticated by the paypal gateway controller pp_gate.php
		$this->CI->paypal->doExpressCheckout($this->CI->go_cart->total(), $store.' order');
				
		// If we get to this step at all, something went wrong	
		return lang('paypal_error');
			
	}
	
	//admin end form and check functions
	function form($post	= false)
	{
		//this same function processes the form
		if(!$post)
		{
			$settings	= $this->CI->Settings_model->get_settings('paypal_express');
			$enabled	= $settings['enabled'];
		}
		else
		{
			$settings = $post;
			$enabled	= $settings['enabled'];
		}
		//retrieve form contents
		ob_start();
		include(APPPATH."libraries/payment/paypal_express/forms/admin_form.php");
		$form = ob_get_contents();
		ob_end_clean(); 
		
		return $form;
	}
	
	function check()
	{	
		$error	= false;
		
		// The only value that matters is currency code.
		//if ( empty($_POST['']) )
			//$error = "<div>You must enter a valid currency code</div>";
					
		//count the errors
		if($error)
		{
			return $error;
		}
		else
		{
			//we save the settings if it gets here
			$this->CI->Settings_model->save_settings('paypal_express', $_POST);
			
			return false;
		}
	}
}
