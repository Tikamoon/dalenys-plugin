<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Action;

use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Request\GetStatus as Status;
use Payum\Core\Request\GetToken;
use Payum\Core\GatewayAwareInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;


/**
 * @author @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var DalenysBridgeInterface */
    private $dalenysBridge;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        // check hash 
        $dalenys = $this->dalenysBridge->createDalenys($this->dalenysBridge->getSecretKey());
        $hash = $dalenys->hash($this->dalenysBridge->getSecretKey(), $_GET);

        if ($_GET['EXTRADATA'] && null === $request->getModel()) {
            if ($_GET['HASH'] === $hash) {
                $getTokenRequest = new GetToken($_GET['EXTRADATA']);
                $this->gateway->execute($getTokenRequest);

                $notifyRequest = new Notify($getTokenRequest->getToken());
                $this->gateway->execute($notifyRequest);

                $statusRequest = new Status($notifyRequest->getModel());
                $this->gateway->execute($statusRequest);

                $response = $_GET;
                $response['transactionReference'] = $response['EXTRADATA'];
                $response['response'] = $response;

                $statusRequest->getModel()->offsetSet('response', $response);
                $this->gateway->execute($statusRequest);
            }
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
        return $request instanceof Notify;
    }
}
