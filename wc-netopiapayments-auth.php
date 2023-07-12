<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Register the custom endpoint
add_action('rest_api_init', 'netopiaCustomEndpoint');

function netopiaCustomEndpoint()
{
    // Register the endpoint
    register_rest_route('netopiapayments/v1', '/credential', array(
        'methods' => 'POST',
        'callback' => 'getCredentialCallback',
    ));
}

// Callback function to handle the request
function getCredentialCallback($request)
{
    // Predefine response data
    $data = array();

    // Create a new response object
    $response = new WP_REST_Response();

    // Get the request parameters
    $params = $request->get_params();
    
     // validate data
    if (!validateParams($params)) {
        $data = array(
            'status' => false,
            'message' => 'Username or Password is not valid',
            'details' => array(),
            'timestamp' => time(),
        );
        $response->data = $data;
        $response->set_status(400);
        return $response;
    } 
    
    /**
     * Steps
     * 1 - Login to Netopia , get Cookie Value
     * 2 - get Signatures
     * 3 - get Live Api Keys
     * 4 - get sandbox Api Keys
     * 5 - generate a general JSON
     * 6 - return the Complate Json
     */

     $ntpAccessRes = ntpPlatformnLogin($params);
    //  die(var_dump($ntpAccessRes));
     if($ntpAccessRes['code'] !== 200) {
        $data = array(
            'status' => false,
            'message' => $ntpAccessRes['message'],
            'details' => $ntpAccessRes,
            'timestamp' => time(),
        );
        echo json_encode($data);
        exit;
     }

     $ntpSignatures = getNtpSignature($ntpAccessRes['data']['accessKey']);
     $ntpLiveApiKeys = getNTPApiKey($ntpAccessRes['data']['accessKey'], $isLive = true);
     $ntpSandboxApiKeys = getNTPApiKey($ntpAccessRes['data']['accessKey'], $isLive = false);

    // Retrieve and process data
    $data = array(
        'signature' => $ntpSignatures,
        'apiKeyLive' => $ntpLiveApiKeys,
        'apiKeySandbox' => $ntpSandboxApiKeys,
        'timestamp' => time(),
    );

    $response->data = $data;
    $response->set_status(200);
    return $response;
}


function validateParams(array $params)
{
    $expectedKeys = array("username","password");
    if(count($params) != count($expectedKeys) )
        return false;
    if(count(array_diff($expectedKeys, array_keys($params))) != 0 )
        return false;
    return true;
}

function ntpPlatformnLogin($params) {
    // JSON data to send
        $data = [
                'login' => [
                    'username' => $params['username'],
                    'password' => $params['password'],
                    'code'     => ''
                ]
            ];

        // Convert data to JSON format
        $jsonData = json_encode($data);

        // Initialize cURL
        $ch = curl_init();

        // Set the URL
        $url = 'https://admin.netopia-payments.com/api/auth/login';
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set the request method to POST
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the request body (JSON data)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Include headers in the response
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Return the response instead of outputting it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            die('cURL Error: ' . curl_error($ch));
        }

        // Separate headers and body
        list($headers, $body) = explode("\r\n\r\n", $response, 2);

        // Extract cookies from the headers
        preg_match_all('/^set-cookie:\s*([^;]*)/mi', $headers, $matches);
        $cookies = [];
        foreach ($matches[1] as $match) {
            parse_str($match, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        // Close cURL
        curl_close($ch);

        // Define NTP response
        $ntpPlatformResponse = array();

        // get response body
        $bodyArr = json_decode($body, true);

        // Check response HTTP Code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode != 200) {
            $ntpPlatformResponse['code'] = $httpCode;
            $ntpPlatformResponse['message'] = $bodyArr['message'];
            $ntpPlatformResponse['data'] = array();
            return $ntpPlatformResponse;
        }

        // Handle the extracted cookies
        if(!array_key_exists('ADM_SESS_ID', $cookies)) {
            $ntpPlatformResponse['code'] = 404;
            $ntpPlatformResponse['message'] = "Access key didn't found";
            $ntpPlatformResponse['data'] = array();
            return $ntpPlatformResponse;
        }

        if(is_null($cookies['ADM_SESS_ID']) || empty($cookies['ADM_SESS_ID'])) {
            $ntpPlatformResponse['code'] = 406;
            $ntpPlatformResponse['message'] = "Access key is Not Acceptable";
            $ntpPlatformResponse['data'] = array();
            return $ntpPlatformResponse;
        }

        // return access key
        $ntpPlatformResponse['code'] = $httpCode;
        $ntpPlatformResponse['message'] = "";
        $ntpPlatformResponse['data']['accessKey'] = $cookies['ADM_SESS_ID'];
        return $ntpPlatformResponse;
}

function getNtpSignature($accessKey) {
    // URL to send the request
    $url = 'https://admin.netopia-payments.com/api/pos/list';

    // Custom headers
    $headers = [
        'ADM_SESS_ID' =>  $accessKey
    ];

    // JSON data to send
    $data = [];

    // die(var_dump($headers));

    // Convert data to JSON format
    $jsonData = json_encode($data);

    // Initialize cURL
    $ch = curl_init();

    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set the request method to POST
    curl_setopt($ch, CURLOPT_POST, 1);

    // Set the request body (JSON data)
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Set the custom headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Include headers in the response
    curl_setopt($ch, CURLOPT_HEADER, 1);

    // Return the response instead of outputting it
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        die('cURL Error: ' . curl_error($ch));
    }

    // Close cURL
    curl_close($ch);

    // Display the response
    echo $response;



    // return array([
    //     "pos" => "AAAA-BBBB-CCCC-DDDD-EEEE",
    //     "active" => true,
    // ],[
    //     "pos" => "EEEE-DDDD-CCCC-BBBB-AAAA",
    //     "active" => false,
    // ]);
}

function getNTPApiKey($accessKey, $isLive) {
    return array($accessKey);
}

?>