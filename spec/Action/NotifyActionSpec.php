<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace spec\Tikamoon\DalenysPlugin\Action;

use Tikamoon\DalenysPlugin\Action\NotifyAction;
use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Payum\Core\Request\Notify;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class NotifyActionSpec extends ObjectBehavior
{
    function let(
        DalenysBridgeInterface $dalenysBridge,
        FactoryInterface $stateMachineFactory
    ) {
        $this->beConstructedWith($stateMachineFactory);
        $this->setApi($dalenysBridge);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotifyAction::class);
    }

    function it_execute(
        Notify $request,
        \ArrayObject $arrayObject,
        DalenysBridgeInterface $dalenysBridge,
        PaymentInterface $payment,
        FactoryInterface $stateMachineFactory,
        StateMachineInterface $stateMachine
    ) {
        $request->getModel()->willReturn($arrayObject);
        $request->getFirstModel()->willReturn($payment);
        $dalenysBridge->isPostMethod()->willReturn(true);
        $dalenysBridge->paymentVerification()->willReturn(true);
        $stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->willReturn($stateMachine);

        $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE)->shouldBeCalled();

        $this->execute($request);
    }
}
