<?php

/**
 * CheetahPay API implementation
 *
 * PHP version 5
 *
 * @category Authentication
 * @package  CheetahPay
 * @author   Mbanusi Ikenna <b>Incofabikenna@gmail.com</b>
 * @license  http://opensource.org/licenses/BSD-3-Clause 3-clause BSD
 * @link     https://github.com/incofab
 */
class WC_CheetahPay_HTTP_Handler 
{
    private static $privateKey = ''; 
    private static $publicKey = ''; 
    private static $endpoint = '';
    
    const NETWORK_9_MOBILE = '9 MOBILE';
    const NETWORK_AIRTEL = 'AIRTEL';
    const NETWORK_GLO = 'GLO';
    const NETWORK_MTN = 'MTN';
    const NETWORK_MTN_TRANSFER = 'MTN TRANSFER';
    
    /**
     * Initialize CheetahPay object with your private and public key
     * @param unknown $apiKey
     * @param unknown $merchantId
     */
    function __construct($privateKey, $publicKey) 
    {
        self::$privateKey = $privateKey;
        
        self::$publicKey = $publicKey;
        
        self::$endpoint = WC_CPPG_DEV ? 'http://localhost/cheetahpay/api/v1' : 'https://cheetahpay.com.ng/api/v1';
    }
    
    private function formatPhoneNo($phone)
    {
        if(empty($phone)) return null; 
        
        $phone = ltrim($phone, '+234');
        
        $phone = ltrim($phone, '0');
        
        return '0' . $phone;
    }
    
    /**
     * Make a pin deposit
     * @param int $pin
     * @param int $amount
     * @param int $network
     * @param int $orderID A unique ID to identify this transaction. This iID will be return when airtime has been validated
     * @param unknown $depositorsPhoneNo
     * @return Array. The zoranga responses are already converted to array to ease use
     */
    function pinDeposit($pin, $amount, $network, $orderID, $depositorsPhoneNo = null) 
    {
        $this->verifyNetworkForPin($network);
        
        $curl_post_data = array
        (
            'amount' => $amount,
            'public_key' => self::$publicKey,
            'private_key' => self::$privateKey,
            'phone' =>  $this->formatPhoneNo($depositorsPhoneNo),
            'pin' => $pin,
            'network' => $network,
            'order_id' => $orderID,
            'pin333' => 'saksdewe',
        );
        
        $curl_response = $this->execute_curl($curl_post_data);
        
        return json_decode($curl_response, true);
        
    }
    
    private function verifyNetworkForPin($network)
    {
        if( $network == self::NETWORK_9_MOBILE || $network == self::NETWORK_AIRTEL
            || $network == self::NETWORK_GLO || $network == self::NETWORK_MTN) return true;
        
        $message = '<div>Only ';
        $message .= '<b>' . self::NETWORK_9_MOBILE . '</b>, ';
        $message .= '<b>' . self::NETWORK_AIRTEL . '</b>, ';
        $message .= '<b>' . self::NETWORK_GLO . '</b>, ';
        $message .= '<b>' . self::NETWORK_MTN . '</b>, ';
        $message .= ' are accepted for pin deposits';
        
        throw new \Exception($message);
    }
    
    /**
     * Deposit using airtime transfer
     * @param unknown $amount
     * @param unknown $network
     * @param unknown $depositorsPhoneNo
     * @param int $orderID A unique ID to identify this transaction. This iID will be return when airtime has been validated
     * @throws \Exception
     * @return mixed
     */
    function airtimeTransfer($amount, $network, $depositorsPhoneNo, $orderID) 
    {
        if( $network != self::NETWORK_MTN_TRANSFER)
        {
            $message = '<div>Only ';
            $message .= '<b>' . self::NETWORK_MTN_TRANSFER . '</b>, ';
            $message .= ' is accepted for airtime transfer';
            
            throw new \Exception($message);
        }
        
        if(empty($depositorsPhoneNo))
        {
            throw new \Exception('You must supply depositor\'s phone number');
        }
        
        $curl_post_data = array
        (
            'amount' => $amount,
            'private_key' => self::$privateKey,
            'public_key' => self::$publicKey,
            'phone' =>  $this->formatPhoneNo($depositorsPhoneNo),
            'network' => $network,
            'order_id' => $orderID,
        );
        
        $curl_response = $this->execute_curl($curl_post_data);
        
        return json_decode($curl_response, true);
    }
    
    /**
     * Helper function to create and execute curl requests
     * @param unknown $curl_post_data
     * @return mixed
     */
    private function execute_curl($curl_post_data) 
    {
        
        // Send this payload to Authorize.net for processing
        $response = wp_remote_post( self::$endpoint, array(
            'method'    => 'POST',
            'body'      => http_build_query( $curl_post_data ),
            'timeout'   => 90,
            'sslverify' => false,
        ) );
        
        // Retrieve the body's resopnse if no errors found
        $response_body = wp_remote_retrieve_body( $response );
        
        if ( is_wp_error( $response ) )
        {
            return json_encode(['success' => false, 'message' => 
                'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.']);
        }
        
        if ( empty($response_body) )
        {
            return json_encode(['success' => false, 'message' => 'Response not found']);
        }
        
        return $response_body;
        
//         $curl = curl_init(self::$endpoint);
        
//         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($curl, CURLOPT_POST, true);
//         curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        
//         $curl_response = curl_exec($curl);
        
//         $httpErrorCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
//         $error = curl_error($curl);
//         curl_close($curl);
        
//         if($error)
//         {
//             return json_encode(['success' => false, 'message' => $error]);
//         }
        
//         if(empty($curl_response) && $httpErrorCode != 200)
//         {
//             return json_encode(['success' => false,
//                 'message' => "Possibe error from server with status $httpErrorCode, try again later"]);
//         }
        
//         return $curl_response;
    }
    
    
}







