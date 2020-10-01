<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Bridge;

use Tikamoon\DalenysPlugin\Legacy\Dalenys;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
interface DalenysBridgeInterface
{
    /**
     * @param string $secretKey
     *
     * @return Dalenys
     */
    public function createDalenys($secretKey);

    /**
     * @return bool
     */
    public function paymentVerification();

    public function getAuthorisationId();

    /**
     * @return bool
     */
    public function isPostMethod();

    /**
     * @return string
     */
    public function getAccountKey();

    /**
     * @param string $accountKey
     */
    public function setAccountKey($accountKey);

    /**
     * @return string
     */
    public function getSecretKey();

    /**
     * @param string $secretKey
     */
    public function setSecretKey($secretKey);

    /**
     * @return string
     */
    public function getMerchantId();

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId);

    /**
     * @return string
     */
    public function getKeyVersion();

    /**
     * @param string $keyVersion
     */
    public function setKeyVersion($keyVersion);

    /**
     * @return string
     */
    public function getEnvironment();

    /**
     * @param string $environment
     */
    public function setEnvironment($environment);

    /**
     * @return string
     */
    public function getApiKeyId();

    /**
     * @param string $apiKeyId
     */
    public function setApiKeyId(string $apiKeyId);
}
