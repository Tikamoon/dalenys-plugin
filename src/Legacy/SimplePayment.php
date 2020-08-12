<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Legacy;

use Payum\Core\Reply\HttpResponse;

/**
 * @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class SimplePayment
{
    /**
     * @var Dalenys|object
     */
    private $dalenys;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $keyVersion;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $transactionReference;

    /**
     * @var string
     */
    private $automaticResponseUrl;

    /**
     * @param Dalenys $dalenys
     * @param $merchantId
     * @param $keyVersion
     * @param $environment
     * @param $amount
     * @param $targetUrl
     * @param $currency
     * @param $transactionReference
     * @param $automaticResponseUrl
     */
    public function __construct(
        Dalenys $dalenys,
        $merchantId,
        $keyVersion,
        $environment,
        $amount,
        $targetUrl,
        $currency,
        $transactionReference,
        $automaticResponseUrl
    ) {
        $this->automaticResponseUrl = $automaticResponseUrl;
        $this->transactionReference = $transactionReference;
        $this->dalenys = $dalenys;
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->keyVersion = $keyVersion;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->targetUrl = $targetUrl;
    }

    public function execute()
    {
        $this->resolveEnvironment();

        $this->dalenys->setMerchantId($this->merchantId);
        $this->dalenys->setInterfaceVersion(Dalenys::INTERFACE_VERSION);
        $this->dalenys->setKeyVersion($this->keyVersion);
        $this->dalenys->setAmount($this->amount);
        $this->dalenys->setCurrency($this->currency);
        $this->dalenys->setOrderChannel("INTERNET");
        $this->dalenys->setTransactionReference($this->transactionReference);
        $this->dalenys->setNormalReturnUrl($this->targetUrl);
        $this->dalenys->setAutomaticResponseUrl($this->automaticResponseUrl);

        $this->dalenys->validate();

        $response = $this->dalenys->executeRequest();

        throw new HttpResponse($response);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function resolveEnvironment()
    {
        if (Dalenys::TEST === $this->environment) {
            $this->dalenys->setUrl(Dalenys::TEST);

            return;
        }

        if (Dalenys::PRODUCTION === $this->environment) {
            $this->dalenys->setUrl(Dalenys::PRODUCTION);

            return;
        }

        if (Dalenys::SIMULATION === $this->environment) {
            $this->dalenys->setUrl(Dalenys::SIMULATION);

            return;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'The "%s" environment is invalid. Expected %s or %s',
                $this->environment,
                Dalenys::PRODUCTION,
                Dalenys::TEST
            )
        );
    }
}
