<?php

namespace Tikamoon\DalenysPlugin\Legacy;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
class Dalenys
{
    const TEST = "https://secure-test.dalenys.com/front/service/rest/process";
    const PRODUCTION = "https://secure-magenta1.dalenys.com/front/service/rest/process";

    const INTERFACE_VERSION = "3.0";

    /** @var ShaComposer */
    private $secretKey;

    private $pspURL = self::TEST;

    private $responseData;

    private $parameters = [];

    private $pspFields = [
        'amount', 'cardExpiryDate', 'cardNumber', 'cardCSCValue',
        'currencyCode', 'merchantId', 'interfaceVersion', 'sealAlgorithm',
        'transactionReference', 'keyVersion', 'paymentMeanBrand', 'customerLanguage',
        'billingAddress.city', 'billingAddress.company', 'billingAddress.country',
        'billingAddress', 'billingAddress.postBox', 'billingAddress.state',
        'billingAddress.street', 'billingAddress.streetNumber', 'billingAddress.zipCode',
        'billingContact.email', 'billingContact.firstname', 'billingContact.gender',
        'billingContact.lastname', 'billingContact.mobile', 'billingContact.phone',
        'shippingAddress.city', 'shippingAddress.street', 'shippingAddress.streetNumber',
        'shippingAddress.zipCode', 'shippingAddress.country',
        'customerAddress', 'customerAddress.city', 'customerAddress.company',
        'customerAddress.country', 'customerAddress.postBox', 'customerAddress.state',
        'customerAddress.street', 'customerAddress.streetNumber', 'customerAddress.zipCode',
        'customerEmail', 'customerContact', 'customerContact.email', 'customerContact.firstname',
        'customerContact.gender', 'customerContact.lastname', 'customerContact.mobile',
        'customerContact.phone', 'customerContact.title', 'expirationDate', 'automaticResponseUrl',
        'templateName', 'paymentMeanBrandList', 'instalmentData.number', 'instalmentData.datesList',
        'instalmentData.transactionReferencesList', 'instalmentData.amountsList', 'paymentPattern',
        'captureDay', 'captureMode', 'merchantTransactionDateTime', 'fraudData.bypass3DS', 'seal',
        'orderChannel', 'orderId', 'returnContext', 'transactionOrigin', 'merchantWalletId', 'paymentMeanId',
        'hfToken', 'apiKeyId', 'cardFullName', 'selectedBrand', 'numberOfPayments',
    ];

    private $requiredFields = [
        'amount', 'currencyCode', 'interfaceVersion', 'keyVersion', 'merchantId', 'normalReturnUrl', 'orderChannel',
        'transactionReference', 'hfToken', 'apiKeyId', 'cardFullName', 'selectedBrand', 'numberOfPayments',
    ];

    public $allowedlanguages = [
        'nl', 'fr', 'de', 'it', 'es', 'cy', 'en'
    ];

    private static $currencies = [
        'EUR' => '978', 'USD' => '840', 'CHF' => '756', 'GBP' => '826',
        'CAD' => '124', 'JPY' => '392', 'MXP' => '484', 'TRY' => '949',
        'AUD' => '036', 'NZD' => '554', 'NOK' => '578', 'BRC' => '986',
        'ARP' => '032', 'KHR' => '116', 'TWD' => '901', 'SEK' => '752',
        'DKK' => '208', 'KRW' => '410', 'SGD' => '702', 'XPF' => '953',
        'XOF' => '952',
    ];

    public function __construct($secret)
    {
        $this->secretKey = $secret;
    }

    /** @return string */
    public function getUrl()
    {
        return $this->pspURL;
    }

    public function setUrl($pspUrl): void
    {
        $this->validateUri($pspUrl);
        $this->pspURL = $pspUrl;
    }

    public function setNormalReturnUrl($url): void
    {
        $this->validateUri($url);
        $this->parameters['normalReturnUrl'] = $url;
    }

    public function setAutomaticResponseUrl($url): void
    {
        $this->validateUri($url);
        $this->parameters['automaticResponseUrl'] = $url;
    }

    protected function validateUri($uri)
    {
        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Uri is not valid");
        }
        if (strlen($uri) > 200) {
            throw new \InvalidArgumentException("Uri is too long");
        }
    }

    public function setTransactionReference($transactionReference): void
    {
        if (preg_match('/[^a-zA-Z0-9_-]/', $transactionReference)) {
            throw new \InvalidArgumentException("TransactionReference cannot contain special characters");
        }
        $this->parameters['transactionReference'] = $transactionReference;
    }

    /**
     * Set amount in cents, eg EUR 12.34 is written as 1234
     */
    public function setAmount($amount): void
    {
        if (!is_int($amount)) {
            throw new \InvalidArgumentException("Integer expected. Amount is always in cents");
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Amount must be a positive number");
        }
        $this->parameters['amount'] = $amount;
    }

    public function setMerchantId($merchantId): void
    {
        $this->parameters['merchantId'] = $merchantId;
    }

    public function setApiKeyId(string $apiKeyId): void
    {
        $this->parameters['apiKeyId'] = $apiKeyId;
    }

    public function setKeyVersion($keyVersion): void
    {
        $this->parameters['keyVersion'] = $keyVersion;
    }

    public function setNumberOfPayments(int $numberOfPayments): void
    {
        $this->parameters['numberOfPayments'] = $numberOfPayments;
    }

    public function setMethod($method): void
    {
        $this->parameters['method'] = $method;
    }

    public function setCurrency($currency): void
    {
        if (!array_key_exists(strtoupper($currency), self::getCurrencies())) {
            throw new \InvalidArgumentException("Unknown currency");
        }
        $this->parameters['currencyCode'] = self::convertCurrencyToCurrencyCode($currency);
    }

    public static function getCurrencies()
    {
        return self::$currencies;
    }

    public static function convertCurrencyToCurrencyCode($currency)
    {
        if (!in_array($currency, array_keys(self::$currencies)))
            throw new \InvalidArgumentException("Unknown currencyCode $currency.");
        return self::$currencies[$currency];
    }

    public function setCustomerEmail($email)
    {
        $this->parameters['customerEmail'] = $email;
    }

    public function setPaymentBrand($brand): void
    {
        $this->parameters['paymentMeanBrandList'] = '';
        if (!array_key_exists(strtoupper($brand), $this->brandsmap)) {
            throw new \InvalidArgumentException("Unknown Brand [$brand].");
        }
        $this->parameters['paymentMeanBrandList'] = strtoupper($brand);
    }

    public function setBillingContactEmail($email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email is invalid");
        }
        $this->parameters['billingContact.email'] = $email;
    }

    public function setBillingAddressStreet($street): void
    {
        $this->parameters['billingAddress.street'] = \Normalizer::normalize($street);
    }

    public function setBillingAddressCountry($country): void
    {
        $this->parameters['billingAddress.country'] = $country;
    }

    public function setShippingAddressCountry($country): void
    {
        $this->parameters['shippingAddress.country'] = $country;
    }

    public function setBillingAddressStreetNumber($nr)
    {
        $this->parameters['billingAddress.streetNumber'] = \Normalizer::normalize($nr);
    }

    public function setBillingAddressZipCode($zipCode): void
    {
        $this->parameters['billingAddress.zipCode'] = \Normalizer::normalize($zipCode);
    }

    public function setBillingAddressCity($city): void
    {
        $this->parameters['billingAddress.city'] = \Normalizer::normalize($city);
    }

    public function setBillingContactPhone($phone): void
    {
        $this->parameters['billingContact.phone'] = $phone;
    }

    public function setBillingContactFirstname($firstname): void
    {
        $this->parameters['billingContact.firstname'] = str_replace(array("'", '"'), '', \Normalizer::normalize($firstname)); // replace quotes
    }

    public function setBillingContactLastname($lastname)
    {
        $this->parameters['billingContact.lastname'] = str_replace(array("'", '"'), '', \Normalizer::normalize($lastname)); // replace quotes
    }

    public function setShippingAddressStreet($street): void
    {
        $this->parameters['shippingAddress.street'] = \Normalizer::normalize($street);
    }

    public function setShippingAddressZipCode($zipCode): void
    {
        $this->parameters['shippingAddress.zipCode'] = \Normalizer::normalize($zipCode);
    }

    public function setShippingAddressCity($city): void
    {
        $this->parameters['shippingAddress.city'] = \Normalizer::normalize($city);
    }

    public function getCustomerFullName()
    {
        return $this->parameters['billingContact.firstname'] . ' ' . $this->parameters['billingContact.lastname'];
    }

    public function getBillingAddress()
    {
        if (isset($this->parameters['billingAddress.streetNumber'])) {
            return $this->parameters['billingAddress.streetNumber'] . ' ' . $this->parameters['billingAddress.street'];
        }

        return $this->parameters['billingAddress.street'];
    }

    public function getShippingAddress()
    {
        if (isset($this->parameters['shippingAddress.streetNumber'])) {
            return $this->parameters['shippingAddress.streetNumber'] . ' ' . $this->parameters['shippingAddress.street'];
        }

        return $this->parameters['shippingAddress.street'];
    }

    public function setCaptureDay($number)
    {
        if (strlen($number) > 2) {
            throw new \InvalidArgumentException("captureDay is too long");
        }
        $this->parameters['captureDay'] = $number;
    }

    public function setCaptureMode($value)
    {
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException("captureMode is too long");
        }
        $this->parameters['captureMode'] = $value;
    }

    public function setMerchantTransactionDateTime($value)
    {
        if (strlen($value) > 25) {
            throw new \InvalidArgumentException("merchantTransactionDateTime is too long");
        }
        $this->parameters['merchantTransactionDateTime'] = $value;
    }

    public function setOrderChannel($value)
    {
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException("orderChannel is too long");
        }
        $this->parameters['orderChannel'] = $value;
    }

    public function setOrderId($value)
    {
        if (strlen($value) > 32) {
            throw new \InvalidArgumentException("orderId is too long");
        }
        $this->parameters['orderId'] = $value;
    }

    public function setReturnContext($value)
    {
        if (strlen($value) > 255) {
            throw new \InvalidArgumentException("returnContext is too long");
        }
        $this->parameters['returnContext'] = $value;
    }

    public function setTransactionOrigin($value)
    {
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException("transactionOrigin is too long");
        }
        $this->parameters['transactionOrigin'] = $value;
    }

    public function setCardNumber($number)
    {
        if (strlen($number) > 19) {
            throw new \InvalidArgumentException("cardNumber is too long");
        }
        if (strlen($number) < 12) {
            throw new \InvalidArgumentException("cardNumber is too short");
        }
        $this->parameters['cardNumber'] = $number;
    }

    public function setCardExpiryDate($date)
    {
        if (strlen($date) != 6) {
            throw new \InvalidArgumentException("cardExpiryDate value is invalid");
        }
        $this->parameters['cardExpiryDate'] = $date;
    }

    public function setCardCSCValue($value)
    {
        if (strlen($value) > 4) {
            throw new \InvalidArgumentException("cardCSCValue value is invalid");
        }
        $this->parameters['cardCSCValue'] = $value;
    }

    public function setHfToken(string $hfToken)
    {
        $this->parameters['hfToken'] = $hfToken;
    }

    public function setSelectedBrand(string $selectedBrand)
    {
        $this->parameters['selectedBrand'] = $selectedBrand;
    }

    public function setCardFullName(string $cardFullName)
    {
        $this->parameters['cardFullName'] = $cardFullName;
    }

    public function setExtraData(string $extradata)
    {
        $this->parameters['extraData'] = $extradata;
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 3) == 'set') {
            $field = lcfirst(substr($method, 3));
            if (in_array($field, $this->pspFields)) {
                $this->parameters[$field] = $args[0];
                return;
            }
        }

        if (substr($method, 0, 3) == 'get') {
            $field = lcfirst(substr($method, 3));
            if (array_key_exists($field, $this->parameters)) {
                return $this->parameters[$field];
            }
        }

        throw new \BadMethodCallException("Unknown method $method");
    }

    public function validate()
    {
        foreach ($this->requiredFields as $field) {
            if (empty($this->parameters[$field])) {
                throw new \RuntimeException($field . " can not be empty");
            }
        }
    }

    /** @var string */
    const DATA_FIELD = "DATA";

    public function setResponse(array $httpRequest)
    {
        // use lowercase internally
        $httpRequest = array_change_key_case($httpRequest, CASE_UPPER);

        // set sha sign
        $this->shaSign = $this->extractShaSign($httpRequest);

        // filter request for Sips parameters
        $this->parameters = $this->filterRequestParameters($httpRequest);
    }

    /**
     * @var string
     */
    private $shaSign;

    private $responseRequest;

    private $parameterArray;

    /**
     * Checks if the response is valid
     * @return bool
     */
    public function isValid()
    {
        $resultat = false;

        $signature = $this->responseData;
        $compute = hash('sha256', utf8_encode($signature . $this->secretKey));
        if (strcmp($this->shaSign, $compute) == 0) {
            if (strcmp($this->parameters['EXECCODE'], "00") == 0) {
                $resultat = true;
            }
        }
        return $resultat;
    }

    function getXmlValueByTag($inXmlset, $needle)
    {
        $resource = xml_parser_create(); //Create an XML parser
        xml_parse_into_struct($resource, $inXmlset, $outArray); // Parse XML data into an array structure
        xml_parser_free($resource); //Free an XML parser
        for ($i = 0; $i < count($outArray); $i++) {
            if ($outArray[$i]['tag'] == strtoupper($needle)) {
                $tagValue = $outArray[$i]['value'];
            }
        }
        return $tagValue;
    }

    /**
     * Retrieves a response parameter
     * @param string $key
     * @throws \InvalidArgumentException
     */
    public function getParam($key)
    {
        return $this->parameterArray[$key];
    }

    public function getResponseRequest()
    {
        return $this->responseRequest;
    }

    /**
     * Compute a HASH from an array
     *
     * @param       $password
     * @param array $data
     * @return string
     */
    public function hash($password, $data = [])
    {
        $clear_string = $password;

        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                foreach ($value as $index => $val) {
                    $clear_string .= $key . '[' . $index . ']=' . $val . $password;
                }
            } else {
                if ($key == 'HASH') {
                    // Skip HASH parameter if supplied
                    continue;
                } else {
                    $clear_string .= $key . '=' . $value . $password;
                }
            }
        }

        return hash('sha256', $clear_string);
    }

    public function executeRequest()
    {
        $ch = curl_init();
        $params = [];

        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->addAmount($params);
        $params['APIKEYID'] = $this->parameters['apiKeyId'];
        $params['CARDFULLNAME'] = $this->parameters['cardFullName'];
        $params['CLIENTEMAIL'] = $this->parameters['customerEmail'];
        $params['CLIENTIDENT'] = $this->parameters['customerEmail'];
        $params['CLIENTIP'] = $_SERVER['REMOTE_ADDR'];
        $params['CLIENTUSERAGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $params['DESCRIPTION'] = "Payment for " . $this->parameters['customerEmail'];
        $params['HFTOKEN'] = $this->parameters['hfToken'];
        $params['IDENTIFIER'] = $this->parameters['merchantId'];
        $params['OPERATIONTYPE'] = "payment";
        $params['ORDERID'] = (string) $this->parameters['orderId'];
        $params['SELECTEDBRAND'] = $this->parameters['selectedBrand'];
        $params['VERSION'] = Dalenys::INTERFACE_VERSION;
        $params['3DSECURE'] = "yes";
        $params['3DSECUREDISPLAYMODE'] = "MAIN";

        $params['3DSECUREPREFERENCE'] = "sca";
        $params['BILLINGADDRESS'] = $this->getBillingAddress();
        $params['BILLINGCITY'] = $this->parameters['billingAddress.city'];
        $params['BILLINGCOUNTRY'] = $this->parameters['billingAddress.country'];
        $params['BILLINGPOSTALCODE'] = $this->parameters['billingAddress.zipCode'];
        $params['SHIPTOADDRESS'] = $this->getShippingAddress();
        $params['SHIPTOADDRESSTYPE'] = "new";
        $params['SHIPTOCITY'] = $this->parameters['shippingAddress.city'];
        $params['SHIPTOCOUNTRY'] = $this->parameters['shippingAddress.country'];
        $params['SHIPTOPOSTALCODE'] = $this->parameters['shippingAddress.zipCode'];
        $params['EXTRADATA'] = $this->parameters['extraData'];

        $params['HASH'] = $this->hash($this->secretKey, $params);
        $requestParams = ['method' => 'payment', 'params' => $params];

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestParams));

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        try {
            if (!$result) {
                throw new \Exception("curl error: ".curl_error($ch));
                curl_close($ch);
            }

            if ($info['http_code'] != 200) {
                throw new \Exception("service error: ".$info['http_code']."\n"."return: ".$result);
                curl_close($ch);
            }
            curl_close($ch);

            if (strlen($result) == 0) {
                throw new \Exception("service did not sent back data");
            }
        } catch (\Exception $exception) {

        }

        return json_decode($result, true);
    }

    private function addAmount(&$params): void
    {
        $numberOfPayments = $this->parameters['numberOfPayments'];
        $totalAmount = $this->parameters['amount'];
        if ($numberOfPayments === 1) {
            $params['AMOUNT'] = $totalAmount;
        } else {
            $amountLeft = $totalAmount;
            $dateUtc = new \DateTime("now", new \DateTimeZone("UTC"));
            $params['AMOUNTS'] = [];
            for ($i = 1; $i <= $numberOfPayments; $i++)
            {
                $formatedDate = $dateUtc->format('Y-m-d');
                if ($i === $numberOfPayments) {
                    $params['AMOUNTS'][$formatedDate] = $amountLeft;
                } else {
                    $partialAmount = (int) ceil($totalAmount / $numberOfPayments);
                    $params['AMOUNTS'][$formatedDate] = $partialAmount;
                    $amountLeft -= $partialAmount;
                }
                $dateUtc->add(new \DateInterval('P30D'));
            }
        }
    }
}
