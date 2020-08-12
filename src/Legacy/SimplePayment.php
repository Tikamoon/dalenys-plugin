<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Legacy;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\HttpRedirect;
use Sylius\Component\Payment\Model\PaymentInterface;

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
    private $apiKeyId;

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
    private $orderChannel;

    /**
     * @var string
     */
    private $automaticResponseUrl;

    /**
     * @var string
     */
    private $hfToken;

    /**
     * @var string
     */
    private $cardFullName;

    /**
     * @var string
     */
    private $selectedBrand;

    /**
     * @var Order|object
     */
    private $order;

    public function __construct(
        Dalenys $dalenys,
        $merchantId,
        $apiKeyId,
        $keyVersion,
        $environment,
        $amount,
        $targetUrl,
        $currency,
        $transactionReference,
        $orderChannel,
        $automaticResponseUrl,
        $hfToken,
        $cardFullName,
        $selectedBrand,
        $order
    ) {
        $this->automaticResponseUrl = $automaticResponseUrl;
        $this->transactionReference = $transactionReference;
        $this->orderChannel =  $orderChannel;
        $this->dalenys = $dalenys;
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->apiKeyId = $apiKeyId;
        $this->keyVersion = $keyVersion;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->targetUrl = $targetUrl;
        $this->hfToken = $hfToken;
        $this->cardFullName = $cardFullName;
        $this->selectedBrand = $selectedBrand;
        $this->order = $order;
    }

    public function execute()
    {
        $this->resolveEnvironment();

        $this->dalenys->setMerchantId($this->merchantId);
        $this->dalenys->setInterfaceVersion(Dalenys::INTERFACE_VERSION);
        $this->dalenys->setApiKeyId($this->apiKeyId);
        $this->dalenys->setKeyVersion($this->keyVersion);
        $this->dalenys->setAmount($this->amount);
        $this->dalenys->setCurrency($this->currency);
        $this->dalenys->setTransactionReference($this->transactionReference);
        $this->dalenys->setOrderChannel($this->orderChannel);
        $this->dalenys->setNormalReturnUrl($this->targetUrl);
        $this->dalenys->setAutomaticResponseUrl($this->automaticResponseUrl);
        $this->dalenys->setCustomerEmail($this->order->getCustomer()->getEmail());
        $this->dalenys->setBillingContactFirstname($this->order->getCustomer()->getFirstName());
        $this->dalenys->setBillingContactLastname($this->order->getCustomer()->getLastName());
        $this->dalenys->setCustomerEmail($this->order->getCustomer()->getEmail());
        $this->dalenys->setHfToken($this->hfToken);
        $this->dalenys->setCardFullName($this->cardFullName);
        $this->dalenys->setSelectedBrand($this->selectedBrand);
        $this->dalenys->setOrderId($this->order->getId());
        $this->dalenys->setMethod('payment');

        $this->dalenys->validate();

        $response = $this->dalenys->executeRequest();

        if ($response['EXECCODE'] === '0000') {
            /** @var \Sylius\Component\Core\Model\Order $order */
            $order = $this->order;
            $payment = $order->getLastPayment();
            $payment->setDetails($response);
        }

        if ($response['EXECCODE'] === '0001' && $response['REDIRECT']) {
            throw new HttpRedirect($response['REDIRECT']);
        }
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
