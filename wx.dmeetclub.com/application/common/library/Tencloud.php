<?php


namespace app\common\library;


class Tencloud
{

    //TENCENTCLOUD_SECRET_KEY
    const SECRET_KEY = "S5UgiZBdu3D8pMhrq0InAKH1pW6Vijps";
    //人脸核验
    const FACE_HOST =  "faceid.tencentcloudapi.com";
    //公共参数  版本
    const VERSION   =  "2018-03-01";
    //公共参数 action
    const ACTION    =  "IdCardVerification";
    //公共参数
    const SERVICE   =  "faceid";
    //公共参数
    const REGION    =  "";
    const ALGO      =  "TC3-HMAC-SHA256";

    protected $payload;
    protected $signedHeaders;
    protected $timestamp;

    protected $name;
    protected $idcard;

    public function __construct($name = '', $idcard = '')
    {
        $this->name = trim($name);
        $this->idcard = trim($idcard);
    }

    /**
     * 生成请求串
     */
    protected function buildRequest()
    {
        // step 1: build canonical request string
        $httpRequestMethod = "POST";
        $canonicalUri = "/";
        $canonicalQueryString = "";
        $canonicalHeaders = implode("\n", ["content-type:application/json; charset=utf-8",
            "host:".self::FACE_HOST,
            "x-tc-action:".strtolower(self::ACTION),
            ""]);
        $this->signedHeaders = implode(";", ["content-type",
            "host",
            "x-tc-action",]);
        $this->payload = '{"IdCard": "'.$this->idcard.'", "Name": "'.$this->name.'"}';
        $hashedRequestPayload = hash("SHA256", $this->payload);
        $canonicalRequest = $httpRequestMethod."\n"
            .$canonicalUri."\n"
            .$canonicalQueryString."\n"
            .$canonicalHeaders."\n"
            .$this->signedHeaders."\n"
            .$hashedRequestPayload;
        return $canonicalRequest;
    }

    /**
     * 生成签名
     */
    protected function sign()
    {
        $this->timestamp = time();
        $canonicalRequest = $this->buildRequest();
        // step 2: build string to sign
        $date = gmdate("Y-m-d", $this->timestamp);
        $credentialScope = $date."/".self::SERVICE."/tc3_request";
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $stringToSign = self::ALGO."\n"
            .$this->timestamp."\n"
            .$credentialScope."\n"
            .$hashedCanonicalRequest;

        // step 3: sign string
        $secretDate = hash_hmac("SHA256", $date, "TC3".self::SECRET_KEY, true);
        $secretService = hash_hmac("SHA256", self::SERVICE, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $stringToSign, $secretSigning);


        $authorization = self::ALGO
            ." Credential=".self::SECRET_ID."/".$credentialScope
            .", SignedHeaders=".$this->signedHeaders.", Signature=".$signature;

        return $authorization;
    }

    public function verify()
    {
        $authorization = $this->sign();
        $curl = "curl -X POST https://".self::FACE_HOST
            .' -H "Authorization: '.$authorization.'"'
            .' -H "Content-Type: application/json; charset=utf-8"'
            .' -H "Host: '.self::FACE_HOST.'"'
            .' -H "X-TC-Action: '.self::ACTION.'"'
            .' -H "X-TC-Timestamp: '.$this->timestamp.'"'
            .' -H "X-TC-Version: '.self::VERSION.'"'
            .' -H "X-TC-Region: '.self::REGION.'"'
            ." -d '".$this->payload."'";

        $result = shell_exec($curl);
        $result = json_decode($result, true);
        $response = $result['Response'] ?? [];
        if(!$response) return false;
        return $response['Result'] === '0';
    }

}