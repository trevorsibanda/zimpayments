# zimpayments
A list of curated resources to help developers integrate with Zimbabwean payment APIs

PayNow Zimbabwe
-------

Paynow is a trading name of Softwarehouse (Private) Limited, a Payment Service Provider which is a member of the Webdev Group. Webdev was established in 2001 and remains the market leader 'all things online'. We develop and maintain software solutions for several blue chip financial institutions in Zimbabwe and the SADC region. Other Webdev online properties in Zimbabwe include www.classifieds.co.zw, www.property.co.zw, www.txt.co.zw. 

Example Usage:

```php
 <?php
 define('PAYNOW_ID' , '1234');
 define('PAYNOW_KEY' , '23rfdsad4rfsdg5436543');
 
 $paynow = new PayNow( array('id' => PAYNOW_ID , 'key' => PAYNOW_KEY) );
 $paynow->set_result_url('https://myapp.com/callback.php?gateway=paynow');
 
 $reference = 0; //get reference from database. must be unique 
 
 $transaction = $paynow->make_transaction($reference , 12.00 , 'Payment for something' , 'http://myapp.com/thank-you-for-paying')
 $response = $paynow->init_transaction($transaction);
 
 if( isset($response['error']) ){  }
 
 if( $response['status'] !== 'ok' )
 {
 	die('hashes mismatch'); 	
  }
  //redirect user to paynow pay url if everything ok
  header('Location: ' . $response['browserurl']); 
 
 *?>

```

Pay4App Zimbabwe
------------




