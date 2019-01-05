<?php
//http://thereforei.am/2012/07/03/cancelling-subscriptions-created-with-paypal-standard-via-the-express-checkout-api/
/**
 * Performs an Express Checkout NVP API operation as passed in $action.
 *
 * Although the PayPal Standard API provides no facility for cancelling a subscription, the PayPal
 * Express Checkout  NVP API can be used.
 */
$profile_id = "I-MFT7YK2N5KPL";
define("USER","drashti.nagrecha_api1.ncrypted.com");
define("PASS","YFFUWCCLB5YAR3UT");
define("SIGN","AYxj2CP4qZByIUGcVuC0WUOwgLnnAcMXs7yEYiwtDy2d1Jp-CoI2IHw0");
// 'PAYPAL_API_APP_ID'  'APP-80W284485P519543T'

function change_subscription_status( $profile_id, $action ) { 
    $api_request = 'USER=' . urlencode(USER)
                .  '&PWD=' . urlencode(PASS)
                .  '&SIGNATURE=' . urlencode(SIGN)
                .  '&VERSION=76.0'
                .  '&METHOD=ManageRecurringPaymentsProfileStatus'
                .  '&PROFILEID=' . urlencode( $profile_id )
                .  '&ACTION=' . urlencode( $action )
                .  '&NOTE=' . urlencode( 'Old cycle was canceled.-Kuldip Bhatt' );
 
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp' ); // For live transactions, change to 'https://api-3t.paypal.com/nvp'
    curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
 
    // Uncomment these to turn off server and peer verification
    // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    // curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_POST, 1 );
 
    // Set the API parameters for this transaction
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_request );
 
    // Request response from PayPal
    $response = curl_exec( $ch );
 
    // If no response was received from PayPal there is no point parsing the response
    if( ! $response )
        die( 'Calling PayPal to change_subscription_status failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' ); 
    curl_close( $ch ); 
    // An associative array is more usable than a parameter string
    parse_str( $response, $parsed_response ); 
    return $parsed_response;
}
//change_subscription_status('I-NARPL1C00000','Cancel');
//change_subscription_status('I-NARPL1C00000','Reactivate');
$ret_resp=change_subscription_status($profile_id,'Suspend');
var_dump($ret_resp);
echo "<br><br><br>";
print_r($ret_resp);
?>