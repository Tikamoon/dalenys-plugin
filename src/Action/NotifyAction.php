<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Action;

use App\Entity\Order\Order;
use App\Entity\Payment\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /** @var RequestStack */
    private $requestStack;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(
        FactoryInterface $stateMachineFactory,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        EntityManagerInterface $em
    ) {
        $this->stateMachineFactory = $stateMachineFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     *
     * @param $request Notify
     */
    public function execute($request)
    {
        $accountKey = $this->dalenysBridge->getAccountKey();
        $secretKey = $this->dalenysBridge->getSecretKey();

        $dalenys = $this->dalenysBridge->createDalenys($secretKey);
        $requestCurrent = $this->requestStack->getCurrentRequest();

        $params = !empty($requestCurrent->query) ? $requestCurrent->query->all() : $requestCurrent->request->all();
        $hash = $dalenys->hash($accountKey, $params);

        if ($params && null === $request->getModel() && $params['HASH'] === $hash) {

            /** @var OrderRepository $orderRepository */
            $orderRepository = $this->em->getRepository(Order::class);

            /** @var OrderInterface $order */
            $order = $orderRepository->find($params['ORDERID']);
            $payment = $order->getPayments()[0];

            if (array_key_exists('EXTRADATA', $params) && !str_contains($params['EXTRADATA'], 'method')) {
                $getTokenRequest = new GetToken($params['EXTRADATA']);
                $this->gateway->execute($getTokenRequest);

                $notifyRequest = new Notify($getTokenRequest->getToken());
            } else {

                $details = $payment->getDetails();
                $details['response'] = $params;
                $payment->setDetails($details);

                /** @var PaymentMethodRepository $paymentMethodRepository */
                $paymentMethodRepository = $this->em->getRepository(PaymentMethod::class);
                $paymentMethod = explode('method=', $params['EXTRADATA']);
                /** @var PaymentMethodInterface|null $paymentMethod */
                $paymentMethod = $paymentMethodRepository->findOneBy([
                    'code' => $paymentMethod[1],
                ]);
                $payment->setMethod($paymentMethod);

                $notifyRequest = new Notify($payment);
            }

            $this->gateway->execute($notifyRequest);

            $statusRequest = new Status($notifyRequest->getModel());
            $this->gateway->execute($statusRequest);

            $statusRequest->getModel()->offsetSet('response', $params);
            $this->gateway->execute($statusRequest);

            switch ($params['EXECCODE']) {
                case '0000':
                    $paymentState = PaymentTransitions::TRANSITION_COMPLETE;
                    $orderState = OrderTransitions::TRANSITION_CREATE;
                    $this->flashBag->add('success', $this->translator->trans('tikamoon.payment.completed'));
                    $route = 'sylius_shop_order_pay';
                    break;

                default:
                    $paymentState = PaymentTransitions::TRANSITION_FAIL;
                    $orderState = OrderTransitions::TRANSITION_CREATE;
                    $this->flashBag->add('error', $this->translator->trans('tikamoon.payment.failed'));
                    $route = 'sylius_shop_order_show';
                    break;
            }

            if ($this->stateMachineFactory->get($order, OrderTransitions::GRAPH)->can($orderState)) {
                $this->stateMachineFactory->get($order, OrderTransitions::GRAPH)->apply($orderState);
            }
            if ($this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->can($paymentState)) {
                $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->apply($paymentState);
            }

            $url = $this->router->generate($route, ['tokenValue' => $order->getTokenValue()]);

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
