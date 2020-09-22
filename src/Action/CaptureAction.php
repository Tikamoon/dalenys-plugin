<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Action;

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

/**
 * @author Vincent Notebaert <vnotebaert@kisoc.com>
 * @author @author Vincent Notebaert <vnotebaert@kisoc.com>
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

    /**
     * @param Payum $payum
     */
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
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
            $payment->getOrder()
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
