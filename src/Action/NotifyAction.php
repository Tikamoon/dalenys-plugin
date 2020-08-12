<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Action;

use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;
use Payum\Core\Request\Notify;
use Sylius\Component\Payment\PaymentTransitions;
use Webmozart\Assert\Assert;
use SM\Factory\FactoryInterface;

/**
 * @author @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class NotifyAction implements ActionInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var DalenysBridgeInterface
     */
    private $dalenysBridge;

    /**
     * @var FactoryInterface
     */
    private $stateMachineFactory;

    /**
     * @param FactoryInterface $stateMachineFactory
     */
    public function __construct(FactoryInterface $stateMachineFactory)
    {
        $this->stateMachineFactory = $stateMachineFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);

        if ($this->dalenysBridge->paymentVerification()) {

            /** @var PaymentInterface $payment */
            $payment = $request->getFirstModel();

            $payment->getDetails()['authorisationId'] = $this->dalenysBridge->getAuthorisationId();

            Assert::isInstanceOf($payment, PaymentInterface::class);

            $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->apply(PaymentTransitions::TRANSITION_COMPLETE);
        }
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
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayObject
        ;
    }
}
