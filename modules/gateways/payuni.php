<?php
function payuni_MetaData()
{
	return [
		'DisplayName' => 'payuni',
		'APIVersion' => '1.0', // Use API Version
		'DisableLocalCreditCardInput' => true,
		'TokenisedStorage' => false,
	];
}

function payuni_config()
{
	return [
		'FriendlyName' => [
			'Type' => 'System',
			'Value' => 'Payuni',
		],
		// a text field type allows for single line text input
		'MerID' => [
			'FriendlyName' => 'MerID (商店代號)',
			'Type' => 'text',
			'Size' => '25',
			'Default' => '',
			'Description' => '在此處輸入您的商店代號',
		],
		// a password field type allows for masked text input
		'hashKey' => [
			'FriendlyName' => 'Hash Key',
			'Type' => 'password',
			'Size' => '25',
			'Default' => '',
			'Description' => '在此輸入您生成的 Hash Key',
		],
		'hashIV' => [
			'FriendlyName' => 'Hash IV',
			'Type' => 'password',
			'Size' => '25',
			'Default' => '',
			'Description' => '在此輸入您生成的 Hash IV',
		],
		// the yesno field type displays a single checkbox option
		'testMode' => [
			'FriendlyName' => '測試模式',
			'Type' => 'yesno',
			'Description' => '勾選以啟用測試模式。',
		],
	];
}


function payuni_link($params)
{
	require_once __DIR__ . '/payuni/payuni.php';
	$service = new \Mod\Gateway\Payuni\PayUni(
		$params['MerID'],
		$params['hashKey'],
		$params['hashIV'],
	);
	$url = $service->getEndpointUrl($params['testMode']);

	$inputs = "";
	foreach ($service->getFormData($params) as $k => $v) {
		$inputs .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />";
	}
	$html = "<form method=\"post\" action=\"{$url}\">";
	$html .= $inputs;
	$html .= "<input class=\"btn btn-success\" type=\"submit\" value=\"{$params['langpaynow']}\" />";
	$html .= "</form>";

	return $html;
}
