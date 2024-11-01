<?php
/*
 * VouchSafe Library
 *
 * @package         VouchSafe
 * @engineered-by   Koneka Inc (http://www.koneka.com)
 * @version         1.1
 * @author          ShareThink Ltd.
 * @copyright       Copyright (C) 2011 vouchsafe.com. All rights reserved	
 *
 * Please visit http://www.vouchsafe.com/ for more information
 */

/**
 * VouchSafeResponse class object will handle all return messages which are sent from the VouchSafe Secure Server
 * @var boolean is_valid indicates if the VouchSafe challenge is answered correctly or not  
 * @var string error is an error message which is sent from the VouchSafe Secure Server
 */
class VouchSafeResponse {

    var $is_valid;
    var $error;

}

/**
 * Validate the VouchSafe answer
 * @param string $privateKey 
 * @param string $challengeID
 * @param string $response
 * @param int $serverToken
 * @return VouchSafeResponse 
 */
function vouchsafe_check_answer($privateKey, $challengeID, $response, $serverToken = null) {
    // Get the user IP address
    $respondentIP = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER['REMOTE_ADDR'];
    // Get the Server URL
    $serverUrl = "api{servertoken}.vouchsafe.com";
    
    // Checking the IP
    if ($respondentIP == null || $respondentIP == '') {
        die("For security reasons, you must pass the respondent ip to VouchSafe");
    }
    
    if (!is_null($serverToken)){
        $pattern = "/^\d*$/";
        if (!preg_match($pattern, $serverToken)){
            $vouchsafe_response = new VouchSafeResponse();
            $vouchsafe_response->is_valid = false;
            $vouchsafe_response->error = "Invalid server token";
            return $vouchsafe_response;
        }
    }else{
        $serverToken = "";
    }
    $serverUrl = str_replace("{servertoken}", $serverToken, $serverUrl);
    //discard spam submissions
    if ($challengeID == null || strlen($challengeID) == 0 || $response == null || strlen($response) == 0) {
        $vouchsafe_response = new VouchSafeResponse();
        $vouchsafe_response->is_valid = false;
        $vouchsafe_response->error = 'incorrect-input-parameters';
        return $vouchsafe_response;
    }

    $response = _vouchsafe_http_post($serverUrl, "/Challenge/Validate", array(
        'PrivateKey' => $privateKey,
        'StringChallengeKey' => $challengeID,
        'StringPath' => $response,
        'RespondentIP' => $respondentIP,
        'Host' => $_SERVER['HTTP_HOST']
    ));
    $data = json_decode($response[1]);
    $vouchsafe_response = new VouchSafeResponse();
    if ($data == null) {
        $vouchsafe_response->is_valid = false;
        $vouchsafe_response->error = "Invalid response from the VouchSafe Server.";
    } else if ($data->Success) {
        $vouchsafe_response->is_valid = true;
    } else {
        $vouchsafe_response->is_valid = false;
        $vouchsafe_response->error = $data->Message;
    }
    return $vouchsafe_response;
}

/**
 * Submits an HTTP POST to the VouchSafe Secure server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _vouchsafe_http_post($host, $path, $data, $port = 80) {

    $host = str_replace('http://', '', $host);
    $req = _vouchsafe_qsencode($data);

    $http_request = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
    $http_request .= "Content-Length: " . strlen($req) . "\r\n";
    $http_request .= "User-Agent: VouchSafe/PHP\r\n";
    $http_request .= "\r\n";
    $http_request .= $req;

    $response = '';
    if (false == ($fs = @fsockopen($host, $port, $errno, $errstr, 10))) {
        echo $errno . ':' . $errstr;
        die('Could not open socket');
    }

    fwrite($fs, $http_request);

    while (!feof($fs))
        $response .= fgets($fs, 1160); // One TCP-IP packet

    fclose($fs);
    $response = explode("\r\n\r\n", $response, 2);

    return $response;
}

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _vouchsafe_qsencode($data) {
    $req = "";
    foreach ($data as $key => $value)
        $req .= $key . '=' . urlencode(stripslashes($value)) . '&';

    // Cut the last '&'
    $req = substr($req, 0, strlen($req) - 1);
    return $req;
}

/**
 * Get the HTML form of the VouchSafe button
 * @param string $publicKey
 * @param string $serverUrl
 * @return string 
 */
function vouchsafe_get_html($publicKey, $serverUrl = null) {
    $serverUrl = getScheme() . "api.vouchsafe.com";
    return '<script type="text/javascript" src="' . $serverUrl . '/Challenge/Generate?PublicKey=' . $publicKey . '"></script>'; 
}

/**
 * Retrieve the scheme of the URL
 * @return string the scheme
 */
function getScheme() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
        return "https://";
    }
    return "http://";
}
?>