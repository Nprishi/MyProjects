<?php
if (isset($_GET['q']) && $_GET['q'] == 'su') {
    $pid = $_GET['oid'];    // Payment ID
    $amt = $_GET['amt'];    // Amount
    $refId = $_GET['refId']; // Reference ID from eSewa
    
    // eSewa URL for payment verification (UAT/Test environment)
    $url = "https://uat.esewa.com.np/epay/transrec";
    
    // Prepare the verification request
    $data = [
        'amt' => $amt,
        'rid' => $refId,
        'pid' => $pid,
        'scd' => 'EPAYTEST'
    ];
    
    // Initialize cURL request
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // Execute cURL and get response
    $response = curl_exec($curl);
    curl_close($curl);
    
    // Print received GET parameters for debugging
    echo '<pre>';
    print_r($_GET);
    echo '</pre>';
    
    // Print cURL response for debugging
    echo '<pre>';
    print_r($response);
    echo '</pre>';
    
    // Check for success response
    if (strpos($response, "Success") !== false) {
        // Payment success, update your database here
        echo "Payment Successful!";
    } else {
        echo "Payment Verification Failed!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
</head>
<body>
    <h1>Payment Status</h1>
</body>
</html>
