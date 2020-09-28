<?php

/**
 * This file was created by the developers from Tikamoon.
 */

namespace spec\Tikamoon\DalenysPlugin;

use Tikamoon\DalenysPlugin\DalenysGatewayFactory;
use PhpSpec\ObjectBehavior;
use Payum\Core\GatewayFactory;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class DalenysGatewayFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DalenysGatewayFactory::class);
        $this->shouldHaveType(GatewayFactory::class);
    }

    function it_populateConfig_run()
    {
        $this->createConfig([]);
    }
}
