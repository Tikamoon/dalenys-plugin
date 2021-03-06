<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Legacy;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\HttpRedirect;
use Psr\Log\LoggerInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
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
     * @var int
     */
    private $numberOfPayments;

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

    /**
     * @var string
     */
    private $extraData;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        Dalenys $dalenys,
        $merchantId,
        $apiKeyId,
        $keyVersion,
        $numberOfPayments,
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
        $order,
        $extraData,
        $logger
    ) {
        $this->automaticResponseUrl = $automaticResponseUrl;
        $this->transactionReference = $transactionReference;
        $this->orderChannel =  $orderChannel;
        $this->dalenys = $dalenys;
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->apiKeyId = $apiKeyId;
        $this->keyVersion = $keyVersion;
        $this->numberOfPayments = $numberOfPayments;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->targetUrl = $targetUrl;
        $this->hfToken = $hfToken;
        $this->cardFullName = $cardFullName;
        $this->selectedBrand = $selectedBrand;
        $this->order = $order;
        $this->extraData = $extraData;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->resolveEnvironment();

        $this->dalenys->setMerchantId($this->merchantId);
        $this->dalenys->setInterfaceVersion(Dalenys::INTERFACE_VERSION);
        $this->dalenys->setApiKeyId($this->apiKeyId);
        $this->dalenys->setKeyVersion($this->keyVersion);
        $this->dalenys->setNumberOfPayments($this->numberOfPayments);
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
        $this->dalenys->setBillingAddressCountry($this->order->getBillingAddress()->getCountryCode());
        $this->dalenys->setBillingAddressStreet($this->order->getBillingAddress()->getStreet());
        $this->dalenys->setBillingAddressCity($this->order->getBillingAddress()->getCity());
        $this->dalenys->setBillingAddressZipCode($this->order->getBillingAddress()->getPostcode());
        $this->dalenys->setShippingAddressCountry($this->order->getShippingAddress()->getCountryCode());
        $this->dalenys->setShippingAddressStreet($this->order->getShippingAddress()->getStreet());
        $this->dalenys->setShippingAddressCity($this->order->getShippingAddress()->getCity());
        $this->dalenys->setShippingAddressZipCode($this->order->getShippingAddress()->getPostcode());
        $this->dalenys->setExtraData($this->extraData);

        $this->logger->info(sprintf('[DALENYS SIMPLE PAYMENT] Before validate orderid "%s"', $this->order->getId()));
        $this->dalenys->validate();
        $this->logger->info(sprintf('[DALENYS SIMPLE PAYMENT] After validate / Before execute orderid "%s"', $this->order->getId()));

        $response = $this->dalenys->executeRequest();
        $this->logger->info(sprintf('[DALENYS SIMPLE PAYMENT] After execute orderid "%s" with response "%s"', $this->order->getId(), json_encode($response)));

        if ($response['EXECCODE'] === '0000') {
            /** @var \Sylius\Component\Core\Model\Order $order */
            $order = $this->order;
            $payment = $order->getLastPayment();
            $payment->setDetails($response);
        }

        if ($response['EXECCODE'] === '0001' && $response['REDIRECTHTML']) {
            throw new HttpResponse(base64_decode($response['REDIRECTHTML']));
        }

        return $response;
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
