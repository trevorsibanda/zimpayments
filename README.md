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
By far the most innovative and best designed payment gateway in Zimbabwe. Pay4App offers a solution for EcoCash, TeleCash, VPayments and VISA payments for Zimbabwe

Example Usage:

```php
<?php
define('PAY4APP_MERCHANT' , '1234567');
define('PAY4APP_SECRET' , 'my-secret-here' );

$pay4app = new Pay4App( );
//set merchant id and secret. can also be set using config in constructor
$pay4app->merchant_id(  PAY4APP_MERCHANT );
$pay4app->api_secret( PAY4APP_SECRET );
//not in test mode
$pay4app->set_test_mode( False );
//return as array
$pay4app->set_json_format( 'array' );
//set url to redirect to when payment is complete
$pay4app->checkout_url( 'http://base2theory.com/we-rock');
$order_id = rand(); //get order id from database
$order_amount = 12.00; //order amount

$form_data  =$pay4app->make_form( $order_id , $order_amount );
?>
<html>
<body>
<form method="post" action="https://pay4app.com/v1/" >
<?php foreach(  $form_data as $key=>$value ): ?>
 <input type="hidden" name="<?= $key ?>" value="<?= $value ?>" />
<?php endforeach; ?>
</form>
``` 


VPayments Zimbabwe
------------

Coming soon

