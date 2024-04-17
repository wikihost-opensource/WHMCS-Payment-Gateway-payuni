<?php
ini_set("display_errors", 1);
if (file_exists("./../../../init.php")) require "./../../../init.php";
require "./../../../includes/functions.php";
require "./../../../includes/gatewayfunctions.php";
require "./../../../includes/invoicefunctions.php";
require __DIR__ . '/../payuni/payuni.php';

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
    die($e->getMessage());
}
$invoiceId = $data['MerTradeNo'];
$tradeNo = $data['TradeNo'];
$amount = $data['TradeAmt'];
$invoiceId = checkCbInvoiceID($invoiceId, $GATEWAY["name"]);
checkCbTransID($tradeNo);
addInvoicePayment($invoiceId, $tradeNo, $amount, $fee, $gatewaymodule);
logTransaction($gatewaymodule, $data, "异步入账");
exit("success");
