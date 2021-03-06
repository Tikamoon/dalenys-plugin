<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Action;

use Monolog\Logger;
use Tikamoon\DalenysPlugin\Legacy\SimplePayment;
use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;
use Payum\Core\Payum;
use Psr\Log\LoggerInterface;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var DalenysBridgeInterface
     */
    private $dalenysBridge;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Payum $payum
     * @param LoggerInterface $logger
     */
    public function __construct(Payum $payum, $logger)
    {
        $this->payum = $payum;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($dalenysBridge)
    {
        if (!$dalenysBridge instanceof DalenysBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->dalenysBridge = $dalenysBridge;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        /** @var TokenInterface $token */
        $token = $request->getToken();

        $transactionReference = isset($model['transactionReference']) ? $model['transactionReference'] : null;

        if ($transactionReference !== null) {

            if ($this->dalenysBridge->isPostMethod()) {

                $model['status'] = $this->dalenysBridge->paymentVerification() ?
                    PaymentInterface::STATE_COMPLETED : PaymentInterface::STATE_CANCELLED;

                $model['authorisationId'] = $this->dalenysBridge->getAuthorisationId();

                $request->setModel($model);

                return;
            }

            if ($model['status'] === PaymentInterface::STATE_COMPLETED) {

                return;
            }
        }

        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $secretKey = $this->dalenysBridge->getSecretKey();

        $dalenys = $this->dalenysBridge->createDalenys($secretKey);

        $environment = $this->dalenysBridge->getEnvironment();
        $merchantId = $this->dalenysBridge->getMerchantId();
        $apiKeyId = $this->dalenysBridge->getApiKeyId();
        $keyVersion = $this->dalenysBridge->getKeyVersion();
        $numberOfPayments = $this->dalenysBridge->getNumberOfPayments();

        $automaticResponseUrl = $notifyToken->getTargetUrl();
        $currencyCode = $payment->getCurrencyCode();
        $targetUrl = $request->getToken()->getTargetUrl();
        $amount = $payment->getAmount();
        $hfToken = $payment->getDetails()['dalenys-hf-token'] ?? null;
        $cardFullName = $payment->getDetails()['dalenys-card-full-name'] ?? null;
        $selectedBrand = $payment->getDetails()['dalenys-selected-brand'] ?? null;

        $transactionReference = "DalenysWS" . uniqid() . "OR" . $payment->getOrder()->getNumber();
        $orderChannel = $payment->getOrder()->getChannel()->getCode();

        $model['transactionReference'] = $transactionReference;

        $simplePayment = new SimplePayment(
            $dalenys,
            $merchantId,
            $apiKeyId,
            $keyVersion,
            $numberOfPayments,
            $environment,
            $amount,
            $targetUrl,
            $currencyCode,
            $transactionReference,
            $orderChannel,
            $automaticResponseUrl,
            $hfToken,
            $cardFullName,
            $selectedBrand,
            $payment->getOrder(),
            $notifyToken->getHash(),
            $this->logger
        );

        $response = $simplePayment->execute();
        $model['response'] = $response;
        $request->setModel($model);
    }

    /**
     * @param string $gatewayName
     * @param object $model
     *
     * @return TokenInterface
     */
    private function createNotifyToken($gatewayName, $model)
    {
        return $this->payum->getTokenFactory()->createNotifyToken(
            $gatewayName,
            $model
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
