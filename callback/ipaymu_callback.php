<?php
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');
require_once(dirname(__FILE__) . '/../ipaymu/Helper.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables('ipaymu');

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Retrieve data returned in payment gateway callback
// Varies per payment gateway
$success = $_POST["status"];
$trxId = $_POST["trx_id"];
$paymentAmount = $_POST["total"];
$paymentFee = $_POST["fee"];

$invoiceId = $_GET['order_id'];

$transactionStatus = $success ? 'Success' : 'Failure';


/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 *
 * @param int $invoiceId Invoice ID
 * @param string $gatewayName Gateway Name
 */
$invoiceIds = checkCbInvoiceID($invoiceId);

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 *
 * @param string $transactionId Unique Transaction ID
 */
// checkCbTransID($transactionId);

if ($gatewayParams['mode'] == 'sandbox') {
	$va = $gatewayParams['ipaymu_va_sandbox'];
	$key = $gatewayParams['ipaymu_apikey_sandbox'];
	$base = 'https://sandbox.ipaymu.com';
}else{
	$va = $gatewayParams['ipaymu_va'];
	$key = $gatewayParams['ipaymu_apikey'];
	$base = 'https://my.ipaymu.com';
}
$ipaymu = new Helper($base, $va, $key);

$cek = $ipaymu->check_transaction($trxId);


$success = false;

if (isset($cek['res']['Status'])) {
	if ($cek['res']['Status'] == 200) {
		if ($cek['res']['Success']) {
			$success = true;
		}
	}
}


/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string $gatewayName        Display label
 * @param string|array $debugData    Data to log
 * @param string $transactionStatus  Status
 */
// logTransaction($gatewayParams['name'], $_POST, $transactionStatus);

if ($success) {

	$paymentAmount = $cek['res']['Data']['Amount'];
	$paymentFee = $cek['res']['Data']['Fee'];
    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment(
        $invoiceId,
        $trxId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );

}else{
	$orgipn = "";
	foreach ($_POST as $key => $value) {
		$orgipn.= ("" . $key . " => " . $value . "\r\n");
	}
	logTransaction($gatewayModuleName, $orgipn, "Duitku Handshake Invalid");
	header("HTTP/1.0 200 OK");
	exit();
}

?>
