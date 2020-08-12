<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace spec\Tikamoon\DalenysPlugin\Bridge;

use Tikamoon\DalenysPlugin\Bridge\DalenysBridge;
use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Tikamoon\DalenysPlugin\Legacy\Dalenys;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class DalenysBridgeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DalenysBridge::class);
        $this->shouldHaveType(DalenysBridgeInterface::class);
    }

    function let(RequestStack $requestStack)
    {
        $this->beConstructedWith($requestStack);
    }

    function it_is_post_method(
        RequestStack $requestStack,
        Request $request
    )
    {
        $request->isMethod('POST')->willReturn(true);
        $requestStack->getCurrentRequest()->willReturn($request);

        $this->isPostMethod()->shouldReturn(true);
    }

    function it_is_not_post_method(
        RequestStack $requestStack,
        Request $request
    )
    {
        $request->isMethod('POST')->willReturn(false);
        $requestStack->getCurrentRequest()->willReturn($request);

        $this->isPostMethod()->shouldReturn(false);
    }

    function it_creates_dalenys()
    {
        $this->createDalenys('key')->shouldBeAnInstanceOf(Dalenys::class);
    }

    function it_payment_verification_has_been_thrown(
        RequestStack $requestStack,
        Request $request
    )
    {
        $request->isMethod('POST')->willReturn(true);
        $requestStack->getCurrentRequest()->willReturn($request);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('paymentVerification', ['key'])
        ;
    }
}
