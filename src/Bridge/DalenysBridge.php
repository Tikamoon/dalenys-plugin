<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Bridge;

use Tikamoon\DalenysPlugin\Legacy\Dalenys;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class DalenysBridge implements DalenysBridgeInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $apiKeyId;

    /**
     * @var string
     */
    private $accountKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $keyVersion;

    /**
     * @var int
     */
    private $numberOfPayments;

    /**
     * @var string
     */
    private $environment;

    /** @var Dalenys */
    private $dalenys;

    /**
     * @var string
     */
    private $code;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function createDalenys($secretKey)
    {
        return new Dalenys($secretKey);
    }

    /**
     * {@inheritDoc}
     */
    public function paymentVerification()
    {
        if ($this->isPostMethod()) {

            $this->dalenys = new Dalenys($this->secretKey);
            $this->dalenys->setResponse($_POST);

            return $this->dalenys->isValid();
        }

        return false;
    }

    public function getAuthorisationId()
    {
        return $this->dalenys->getAuthorisationId();
    }

    /**
     * {@inheritDoc}
     */
    public function isPostMethod()
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return $currentRequest->isMethod('POST');
    }

    /**
     * @return string
     */
    public function getAccountKey()
    {
        return $this->accountKey;
    }

    /**
     * @param string $accountKey
     */
    public function setAccountKey($accountKey)
    {
        $this->accountKey = $accountKey;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getKeyVersion()
    {
        return $this->keyVersion;
    }

    /**
     * @param string $keyVersion
     */
    public function setKeyVersion($keyVersion)
    {
        $this->keyVersion = $keyVersion;
    }

    /**
     * @return int
     */
    public function getNumberOfPayments(): int
    {
        return $this->numberOfPayments;
    }

    /**
     * @param int $numberOfPayment
     */
    public function setNumberOfPayments(int $numberOfPayment): void
    {
        $this->numberOfPayments = $numberOfPayment;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getApiKeyId(): string
    {
        return $this->apiKeyId;
    }

    /**
     * @param string $apiKeyId
     */
    public function setApiKeyId(string $apiKeyId): void
    {
        $this->apiKeyId = $apiKeyId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
