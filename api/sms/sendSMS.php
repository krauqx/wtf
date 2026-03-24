
<?php
//This is where I will setup the SMS sending function. Pag nagpalit API papalitan lang dito.
require_once __DIR__ . '/../../config/config.php';
function sendSMS($recipient, $message, $sender = 'JAMClinic') {
    global $sms_token, $sms_send_endpoint;

    // Prepare payload
    $data = [
        'mocean-to'   => $recipient,
        'mocean-from' => $sender,
        'mocean-text' => $message
    ];

    // Initialize cURL
    $ch = curl_init($sms_send_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $sms_token",
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute and handle response
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'cURL error: ' . $error
        ];
    }

    return [
        'status' => 'sent',
        'response' => json_decode($response, true)
    ];
}
?>