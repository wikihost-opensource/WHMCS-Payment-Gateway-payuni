<?php
ini_set("display_errors", 1);
if (file_exists("./../../../init.php")) require "./../../../init.php";
require "./../../../includes/functions.php";
require "./../../../includes/gatewayfunctions.php";
require "./../../../includes/invoicefunctions.php";
require __DIR__ . '/../payuni/payuni.php';

global $CONFIG;
$gatewaymodule = "payuni";
$params = getGatewayVariables($gatewaymodule);
$service = new \Mod\Gateway\Payuni\PayUni(
    $params['MerID'],
    $params['hashKey'],
    $params['hashIV'],
);
try {
    $data = $service->decodeData($_POST);
} catch (\Exception $e) {
    header("Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices");
    exit();
}
$invoiceId = $data['MerTradeNo'];
$tradeNo = $data['TradeNo'];
$amount = $data['TradeAmt'];
$invoiceId = checkCbInvoiceID($invoiceId, $GATEWAY["name"]);
checkCbTransID($tradeNo);
addInvoicePayment($invoiceId, $tradeNo, $amount, $fee, $gatewaymodule);
logTransaction($gatewaymodule, $data, "同步入账");
header("Location: " . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceId);
exit();
