<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class StatusAction implements ActionInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $requestCurrent = $this->requestStack->getCurrentRequest();

        $transactionReference = isset($model['transactionReference']) ? $model['transactionReference'] : null;

        $status = isset($model['response']['EXECCODE']) ? $model['response']['EXECCODE'] : null;

        if ((null === $transactionReference) && !$requestCurrent->isMethod('POST')) {

            $request->markNew();

            return;
        }

        if ($transactionReference && $status === "0000") {
            $request->markCaptured();

            return;
        }

        if ((int) $status > 1) {
            $request->markCanceled();

            return;
        }

        if ($status === PaymentInterface::STATE_COMPLETED) {

            $request->markCaptured();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
