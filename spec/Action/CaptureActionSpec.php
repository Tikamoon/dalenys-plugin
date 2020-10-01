<?php

/**
 * This file was created by the developers from Tikamoon.
 */

namespace spec\Tikamoon\DalenysPlugin\Action;

use Tikamoon\DalenysPlugin\Action\CaptureAction;
use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Tikamoon\DalenysPlugin\Legacy\Dalenys;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Order\Model\Order;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class CaptureActionSpec extends ObjectBehavior
{
    function let(Payum $payum, DalenysBridgeInterface $dalenysBridge)
    {
        $this->beConstructedWith($payum, $dalenysBridge);
        $this->setApi($dalenysBridge);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CaptureAction::class);
    }

    function it_executes(
        Capture $request,
        \ArrayObject $arrayObject,
        PaymentInterface $payment,
        Token $token,
        Token $notifyToken,
        Payum $payum,
        GenericTokenFactory $genericTokenFactory,
        Order $order,
        DalenysBridgeInterface $dalenysBridge,
        Dalenys $dalenys
    ) {
        $dalenysBridge->getAccountKey()->willReturn('123');
        $dalenysBridge->getSecretKey()->willReturn('123');
        $dalenysBridge->getEnvironment()->willReturn(Dalenys::TEST);
        $dalenysBridge->getMerchantId()->willReturn('123');
        $dalenysBridge->getKeyVersion()->willReturn('3');
        $dalenysBridge->createDalenys('123')->willReturn($dalenys);
        $payment->getOrder()->willReturn($order);
        $payment->getCurrencyCode()->willReturn('EUR');
        $payment->getAmount()->willReturn(100);
        $notifyToken->getTargetUrl()->willReturn('url');
        $token->getTargetUrl()->willReturn('url');
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn([]);
        $genericTokenFactory->createNotifyToken('test', [])->willReturn($notifyToken);
        $payum->getTokenFactory()->willReturn($genericTokenFactory);
        $request->getModel()->willReturn($arrayObject);
        $request->getFirstModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $request->setModel(Argument::any())->shouldBeCalled();

        $this
            ->shouldThrow(HttpResponse::class)
            ->during('execute', [$request]);
    }
}
