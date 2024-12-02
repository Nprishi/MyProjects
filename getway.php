    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Get Way</title>
    </head>

    <body>

        <!-- <div class="Paymnet Id/Password">
        Try Esewa Account
         eSewa ID: 9806800001/2/3/4/5
        Password: Nepal@123 MPIN: 1122 (for application only)
        Token:123456
        </div> -->
        <form action="https://uat.esewa.com.np/epay/main" method="POST">
            <!-- Total Amount -->
            <input type="hidden" name="tAmt" value="1000">
            <!-- Actual Amount -->
            <input type="hidden" name="amt" value="1000">
            <!-- Tax Amount (if any) -->
            <input type="hidden" name="txAmt" value="0">
            <!-- Service Charge (if any) -->
            <input type="hidden" name="psc" value="0">
            <!-- Delivery Charge (if any) -->
            <input type="hidden" name="pdc" value="0">
            <!-- Merchant Code (Test) -->
            <input type="hidden" name="scd" value="EPAYTEST">
            <!-- Transaction Reference ID (Unique for each payment) -->
            <input type="hidden" name="pid" value="<?php echo uniqid('test-invoice-'); ?>">
            <!-- Success URL (Publicly accessible) -->
            <input type="hidden" name="su" value="http://xyz.ngrok.io/esewa_success.php?q=su">
            <!-- Failure URL (Publicly accessible) -->
            <input type="hidden" name="fu" value="http://xyz.ngrok.io/esewa_failure.php?q=fu">
            <!-- Submit Button -->
            <input type="submit" value="Pay with eSewa">
        </form>

    </body>

    </html>