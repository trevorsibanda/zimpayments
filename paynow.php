<?php
/**
 * CI PayNow 
 *
 *
 * @author 		Trevor Sibanda<trevorsibb@gmail.com>
 * @date 		25 May 2015 Africa Day !
 *
 *
 * Sample usage:
 *
 *<?php
 *
 * define('PAYNOW_ID' , '1234');
 * define('PAYNOW_KEY' , '23rfdsad4rfsdg5436543');
 *
 * $paynow = new PayNow( array('id' => PAYNOW_ID , 'key' => PAYNOW_KEY) );
 * $paynow->set_result_url('https://myapp.com/callback.php?gateway=paynow');
 *
 * $reference = 0; //get reference from database. must be unique 
 *
 * $transaction = $paynow->make_transaction($reference , 12.00 , 'Payment for something' , 'http://myapp.com/thank-you-for-paying')
 * $response = $paynow->init_transaction($transaction);
 *
 * if( isset($response['error']) ){  }
 *
 * if( $response['status'] !== 'ok' )
 * {
 *	die('hashes mismatch'); 	
 * }
 * //redirect user to paynow pay url if everything ok
 * header('Location: ' . $response['browserurl']); 
 *
 *?>
 * @todo 		Add more error reporting
 */

class PayNow
{

	/**
	 * PayNow Integration Key
	 *
	 * Must be kept private and well stored !
	 */
	private $_intergration_key;

	/**
	 * Paynow Merchant Intergration ID
	 * Must be set in contrsuctor using config
	 */
	private $_intergration_id;

	/**
	 * Paynow API url to init transaction
	 */
	private $_init_transaction_url = 'https://www.paynow.co.zw/interface/initiatetransaction';

	/**
	 * PayNow Callback Url
	 *
	 * Paynow will post transaction details to this URL
	 */ 
	private $_result_url = 'https://';

	/**
	 * Transaction return Url
	 *
	 * Url to redirect the user to once the transaction is complete.
	 * NB: This value is overriden by the url in the transaction object.
	 */
	private $_return_url = 'https://';

	/** Last HTTP request data */
	private $_http_data = Null;
	
	/**
	 * Empty transaction array.
	 */
	private $_empty_transaction_request = array( 
		'reference' => '' , //Merchant Transaction ID 
		'amount' => 0.00 ,  //Amount
		'additionalinfo' => '' , //Additional info
		'returnurl' =>'' , //URL to redirect the user to after payment
		'authemail' => '' //User email . Recommended to be set to nothing
		);

	/** ctor 
 	 *
 	 * @param 	Array 		$	PayNow Config (Integration keys)
 	 *
 	 * @return 	None
	 */
	public function __construct( $config = array('id' => '' , 'key' => '' , 'result_url' => '') )
	{
		if( empty($config['id']) or empty($config['key'])  )
			die('PayNow Invalid Config Passed: ' . __FILE__ . ':' . __LINE__ );
		$this->_intergration_key = $config['key'];
		$this->_intergration_id = $config['id'];
		$this->_result_url = $config['result_url'];
		//check return url
		$this->_return_url = (isset($config['return_url'])  ? $config['return_url'] : '' );
	}

	/**
	 * Set the result URL
	 *
	 * @param 	String 		$	Result URL
	 *
	 * @return 	None
	 */
	 public function set_result_url( $url )
	 {
	 	$this->_result_url = $url;
	 }

	 /**
	  * Set return url
	  *
	  * Will be used if none if specified when making a transaction object
	  *
	  * @param 		String 		$	Url
	  * 
	  */
	 public function set_return_url( $url )
	 {
	 	$this->_return_url = $url;
	 }

	 /**
	  * Make a transaction object.
	  *
	  * The transaction object is later used to initiate a transaction.
	  *
	  * @param 		String 		$	Reference ID ( Your database order ID)
	  * @param 		Float 		$	Amout in USD
	  * @param 		String 		$	Additional info to pass to PayNow
	  * @param 		String 		$	Return Url, if not specified, PayNow::_return_url is used instead
	  *
	  * @return 	Array 		$	Transaction or Empty array on fail
	  */
	 public function make_transaction( $reference , $amount , $additionalinfo = '' , $return_url = '')
	 {
	 	$transaction = $this->_empty_transaction_request;
	 	//validate parameters
	 
	 	$transaction['reference'] = $reference;
	 	$transaction['amount'] = $amount;
	 	$transaction['additionalinfo'] = $additionalinfo;
	 	$transaction['returnurl'] = ( empty($return_url)  ? $this->_return_url  : $return_url );
	 	return $transaction;
	 }

	/**
	 * Initiate a transaction.
	 *
	 * @param 		Array 		$	Transaction
	 *
	 * @return 		Array 		$	Result. Returns empty array on fail
	 */ 
	public function init_transaction( $transaction )
	{
		//reorder to paynow order wich is utterly stupid and pathetic on Paynow's part !!!
		$paynow_ordered = array(
			'resulturl' => $this->_result_url ,  
            'returnurl' =>  ( empty( $transaction['returnurl'] ) ? $this->_return_url : $transaction['returnurl'] ),  
            'reference' =>  $transaction['reference'],  
            'amount' =>  $transaction['amount'],  
            'id' =>  $this->_integration_id,  
            'additionalinfo' =>  $transaction['additionalinfo'],  
            'authemail' =>  $transaction['authemail'],  
            'status' =>  'Message');

		$hash = $this->generate_hash(  $paynow_ordered );
		
		//post data
		$post_data = $this->make_http_request_param( $paynow_ordered , $hash );

		//perform request
		$this->_http_data = $this->http_request( $this->_init_transaction_url , 'POST' , $post_data );
		if( is_null($this->_http_data) )
		{
			return array();
		}
		//convert to array
		$result = $this->make_array( $this->_http_data );

		return $result;
	}

	/**
	 * Poll Paynow for the status of a transaction
	 *
	 * @param 		String 		$	Poll Url as stored in the initiated transaction.
	 *
	 * @return 		Array 		$ 	Empty array on fail
	 */
	public function poll_transaction(  $poll_url )
	{
		$post_data = '';
		$this->_http_data = $this->http_request($poll_url, 'POST' , $post_data);
		if( is_null($this->_http_data) )
		{
			return array();
		}
		return $this->make_array(  $this->_http_data );
	}

	/**
	 * Generate PayNow Hash
	 *
	 * Sent out to ensure authenticity of request
	 *
	 * @param 		Array 		$	Transaction
	 *
	 * @todo 		Make sure order is always the same
	 *
	 * @return 		String 		$	SHA512 Hash
	 */
	protected function generate_hash( $transaction )
	{
		$data = '';
		foreach(  $transaction as $key => $value )
		{
			if( is_null($value ) or ! is_string($value) )
			{
				continue;
			}
			$data .= $value;
		}
		//append secret key
		$data .= $this->_intergration_key;

		$hash = strtoupper( hash('sha512' , $data ) );
		return $hash;
	}

	/**
	 * Perform HTTP request.
	 * On fail sets an error
	 *
	 * @param 		String 		$	Url to request
	 * @param 		String 		$ 	Request type ( get , post )
	 * @param 		String 		$	Data to Post. Ignored if HTTP request
	 *
	 * @return 		Mixed 		$	Http Response data 
	 */
	protected function http_request( $url , $request = 'POST' ,   $post_data = Null )
	{
		$ch = curl_init();    
	    curl_setopt($ch, CURLOPT_URL, $url);
	    
	    if( $request == 'POST')  
	    {
	    	curl_setopt($ch, CURLOPT_POST, true);
	    	curl_setopt($ch, CURLOPT_POST, true);
	    }	  
	    else
	    {
	    	curl_setopt($ch, CURLOPT_GET , true );
	    }	
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	  
	    //execute post  
	    $result = curl_exec($ch);  
	  
	    if($result)  
	    {  
	    	$data = $result;
	    	curl_close($ch);
	    	return $data;
	    }
	    else
	    {
	    	$this->_error = curl_error($ch);
	    	return Null;
	    }

	}

	protected function make_http_request_param( $transaction , $hash )
	{
		$fields = array();  
         
        $transaction["hash"] = urlencode( $hash );  
        $fields_string = '';
        $delim  = '';
        foreach( $transaction as  $key => $value )
        {
        	$param = ( $delim . $key . '=' . urlencode($value ) ); 
        	$fields_string .= $param;
        	$delim = '&';
        }  
        return $fields_string; 
    }
	/**
	 * Convert data obtained from PayNow API into an Array
	 *
	 * @param 		String 		$	Url Encoded Data 
	 *
	 * @return 		Array 		$	Empty array on fail
	 */
	protected function make_array(  $paynow_http_request_data )
	{
		$parts = explode("&",$paynow_http_request_data);  
        $result = array();  
        foreach($parts as $i => $value) 
        {  
            $bits = explode("=", $value, 2);  
            if( count($bits) == 2 )
            {
            	$result[$bits[0]] = urldecode($bits[1]);	
            }
            else
            {
            	//log error
            }
              
        }  
  		return $result;  
	}


}
