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
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use Payum\Core\GatewayAwareInterface;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus as Status;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var DalenysBridgeInterface */
    private $dalenysBridge;

    /** @var FactoryInterface */
    private $stateMachineFactory;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        FactoryInterface $stateMachineFactory,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->stateMachineFactory = $stateMachineFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function execute($request)
    {
        $accountKey = $this->dalenysBridge->getAccountKey();
        $secretKey = $this->dalenysBridge->getSecretKey();

        $dalenys = $this->dalenysBridge->createDalenys($secretKey);
        $hash = $dalenys->hash($accountKey, $_GET);
        $isGet = is_array($_GET) && array_key_exists('EXTRADATA', $_GET);

        if ($isGet && null === $request->getModel() && $_GET['HASH'] === $hash) {
            $getTokenRequest = new GetToken($_GET['EXTRADATA']);
            $this->gateway->execute($getTokenRequest);

            $notifyRequest = new Notify($getTokenRequest->getToken());
            $this->gateway->execute($notifyRequest);

            $statusRequest = new Status($notifyRequest->getModel());
            $this->gateway->execute($statusRequest);

            $statusRequest->getModel()->offsetSet('response', $_GET);
            $this->gateway->execute($statusRequest);

            /** @var PaymentInterface $payment */
            $payment = $statusRequest->getFirstModel();
            $details = $payment->getDetails();
            $details['response'] = $_GET;
            $payment->setDetails($details);

            switch ($_GET['EXECCODE']) {
                case '0000':
                    $paymentState = PaymentTransitions::TRANSITION_COMPLETE;
                    $orderState = OrderPaymentTransitions::TRANSITION_PAY;
                    $targetUrl = 'sylius_shop_order_thank_you';
                    $params = [];
                    $this->flashBag->add('success', $this->translator->trans('tikamoon.payment.completed'));
                    break;

                default:
                    $paymentState = PaymentTransitions::TRANSITION_FAIL;
                    $orderState = OrderPaymentTransitions::TRANSITION_CANCEL;
                    $targetUrl = 'sylius_shop_order_show';
                    $params = ['tokenValue' => $payment->getOrder()->getTokenValue()];
                    $this->flashBag->add('error', $this->translator->trans('tikamoon.payment.failed'));
                    /** @var OrderInterface $order */
                    $order = $payment->getOrder();
                    $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH)->apply($orderState);
                    break;
            }

            $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->apply($paymentState);

            $url = $this->router->generate($targetUrl, $params);

            throw new HttpRedirect($url);
        }

        return;
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
