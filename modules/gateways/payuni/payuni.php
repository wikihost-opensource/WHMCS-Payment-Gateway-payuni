<?php

namespace Mod\Gateway\Payuni;

class PayUni
{
    public const MODULE_NAME = 'payuni';
    private $id, $key, $iv;
    public function __construct(string $id, string $key, string $iv)
    {
        $this->id = $id;
        $this->key = $key;
        $this->iv = $iv;
    }

    public static function getEndpointUrl($isTestMode = false)
    {
        return $isTestMode ? 'https://sandbox-api.payuni.com.tw/api/upp' : 'https://api.payuni.com.tw/api/upp';
    }

    public function decodeData(array $data)
    {
        if ($data['MerID'] != $this->id) throw new \Exception("ERR: Invaild Merchant ID");
        $resp = self::decrypt($data['EncryptInfo'], $this->key, $this->iv);
        parse_str($resp, $respData);

        if ($respData['Status'] != 'SUCCESS') {
            if (function_exists('logTransaction')) {
                logTransaction(self::MODULE_NAME, $respData, "入账失败");
            }
            throw new \Exception("ERR: Invaild Status");
        }

        return $respData;
    }

    public function getFormData(array $params)
    {
        // 帳單參數
        $invoiceId = $params['invoiceid'];
        $description = $params["description"];
        //由於支付接口不支援小數金額，因此取整數金額。
        $amount = floor($params['amount']);
        // 客戶參數
        $email = $params['clientdetails']['email'];

        // 系統參數
        $systemUrl = $params['systemurl'];
        $returnUrl = $params['returnurl'];

        //交易參數
        $postfields = [
            'MerID' => $this->id,
            'MerTradeNo' => $invoiceId,
            'TradeAmt' => (int) $amount,
            'Timestamp' => time(),
            'ReturnURL' => $systemUrl . 'modules/gateways/callback/payuni_return.php',
            'NotifyURL' => $systemUrl . 'modules/gateways/callback/payuni_notify.php',
            'UsrMail' => $email,
            'UsrMailFix' => 1,
            'ProdDesc' => $params["description"],
        ];

        $aesData = self::encrypt($postfields, $this->key, $this->iv);
        $sha256Data = self::aes_sha256_str($aesData, $this->key, $this->iv);

        return [
            'MerID' => $this->id,
            'Version' => '1.0',
            'EncryptInfo' => $aesData,
            'HashInfo' => $sha256Data,
        ];
    }

    public static function encrypt($postData, $hashKey, $hashIV)
    {
        $tag = ""; //預設為空
        $encrypted = openssl_encrypt(http_build_query($postData), "aes-256-gcm", trim($hashKey), 0, trim($hashIV), $tag);
        return trim(bin2hex($encrypted . ":::" . base64_encode($tag)));
    }

    public static function decrypt(string $encryptStr = "", string $merKey = "", string $merIV = "")
    {
        list($encryptData, $tag) = explode(":::", hex2bin($encryptStr), 2);
        return openssl_decrypt($encryptData, "aes-256-gcm", trim($merKey), 0, trim($merIV), base64_decode($tag));
    }

    public static  function addpadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }
    //SHA256 加密
    public static function aes_sha256_str($aesData, $hashKey, $hashIV)
    {
        return strtoupper(hash("sha256", "{$hashKey}{$aesData}{$hashIV}"));
    }
}
