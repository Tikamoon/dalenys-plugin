<?php

/**
 * This file was created by the developers from Tikamoon.
 */

namespace spec\Tikamoon\DalenysPlugin\Action;

use Tikamoon\DalenysPlugin\Action\ConvertPaymentAction;
use PhpSpec\ObjectBehavior;
use Payum\Core\Request\Convert;
use Payum\Core\Model\PaymentInterface;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class ConvertPaymentActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    function it_execute(
        Convert $request,
        \ArrayObject $arrayObject,
        PaymentInterface $payment
    ) {
        $request->setResult([])->willReturn($arrayObject);
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->execute($request);
    }
}
