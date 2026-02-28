<?php

// Mandatory claims
$mobicard_version = "2.0";
$mobicard_mode = "LIVE"; // production
$mobicard_merchant_id = "4";
$mobicard_api_key = "YmJkOGY0OTZhMTU2ZjVjYTIyYzFhZGQyOWRiMmZjMmE2ZWU3NGIxZWM3ZTBiZSJ9";
$mobicard_secret_key = "NjIwYzEyMDRjNjNjMTdkZTZkMjZhOWNiYjIxNzI2NDQwYzVmNWNiMzRhMzBjYSJ9";

$mobicard_token_id = abs(rand(1000000,1000000000));
$mobicard_token_id = "$mobicard_token_id";

$mobicard_txn_reference = abs(rand(1000000,1000000000)); // Your reference
$mobicard_txn_reference = "$mobicard_txn_reference";

$mobicard_service_id = "20000"; // Card Services ID
$mobicard_service_type = "BIINLOOKUP";

// Accepts 6-digit BIN, 8-digit BIIN, or full card number
$mobicard_card_number = "5173350006475601";
$mobicard_card_biin = substr($mobicard_card_number, 0, 8); // Extract first 8 digits

// Create JWT Header
$mobicard_jwt_header = [
    "typ" => "JWT",
    "alg" => "HS256"
];
$mobicard_jwt_header = rtrim(strtr(base64_encode(json_encode($mobicard_jwt_header)), '+/', '-_'), '=');

// Create JWT Payload
$mobicard_jwt_payload = array(
    "mobicard_version" => "$mobicard_version",
    "mobicard_mode" => "$mobicard_mode",
    "mobicard_merchant_id" => "$mobicard_merchant_id",
    "mobicard_api_key" => "$mobicard_api_key",
    "mobicard_service_id" => "$mobicard_service_id",
    "mobicard_service_type" => "$mobicard_service_type",
    "mobicard_token_id" => "$mobicard_token_id",
    "mobicard_txn_reference" => "$mobicard_txn_reference",
    "mobicard_card_biin" => "$mobicard_card_biin"
);

$mobicard_jwt_payload = rtrim(strtr(base64_encode(json_encode($mobicard_jwt_payload)), '+/', '-_'), '=');

// Generate Signature
$header_payload = $mobicard_jwt_header . '.' . $mobicard_jwt_payload;
$mobicard_jwt_signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $header_payload, $mobicard_secret_key, true)), '+/', '-_'), '=');

// Create Final JWT
$mobicard_auth_jwt = "$mobicard_jwt_header.$mobicard_jwt_payload.$mobicard_jwt_signature";

// Make API Call
$mobicard_request_access_token_url = "https://mobicardsystems.com/api/v1/biin_lookup";

$mobicard_curl_post_data = array('mobicard_auth_jwt' => $mobicard_auth_jwt);

$curl_mobicard = curl_init();
curl_setopt($curl_mobicard, CURLOPT_URL, $mobicard_request_access_token_url);
curl_setopt($curl_mobicard, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_mobicard, CURLOPT_POST, true);
curl_setopt($curl_mobicard, CURLOPT_POSTFIELDS, json_encode($mobicard_curl_post_data));
curl_setopt($curl_mobicard, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl_mobicard, CURLOPT_SSL_VERIFYPEER, false);
$mobicard_curl_response = curl_exec($curl_mobicard);
curl_close($curl_mobicard);

// Parse Response
$response_data = json_decode($mobicard_curl_response, true);

if(isset($response_data) && is_array($response_data)) {
    
    if($response_data['status'] === 'SUCCESS') {
        
        // Access card issuer information
        $card_scheme = $response_data['card_biin_information']['card_biin_scheme'];
        $issuer_bank = $response_data['card_biin_information']['card_biin_bank_name'];
        $card_type = $response_data['card_biin_information']['card_biin_type'];
        $country = $response_data['card_biin_information']['card_biin_country_name'];
        $is_prepaid = $response_data['card_biin_information']['card_biin_prepaid'];
        
        echo "BIIN Lookup Successful!
";
        echo "Card Scheme: " . $card_scheme . "
";
        echo "Issuer Bank: " . $issuer_bank . "
";
        echo "Card Type: " . $card_type . "
";
        echo "Country: " . $country . "
";
        echo "Prepaid: " . $is_prepaid . "
";
        
        // Use this information for risk assessment and payment processing
        if($is_prepaid === 'Yes') {
            echo "Note: Prepaid card detected - apply appropriate risk rules.";
        }
        
    } else {
        echo "Error: " . $response_data['status_message'] . " (Code: " . $response_data['status_code'] . ")";
    }
} else {
    echo "Error: Invalid API response";
}
