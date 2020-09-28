<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Mocker;

use Tikamoon\DalenysPlugin\Legacy\Dalenys;
use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Sylius\Behat\Service\Mocker\Mocker;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class DalenysMocker
{
    /**
     * @var Mocker
     */
    private $mocker;

    /**
     * @param Mocker $mocker
     */
    public function __construct(Mocker $mocker)
    {
        $this->mocker = $mocker;
    }

    /**
     * @param callable $action
     */
    public function completedPayment(callable $action)
    {
        $openDalenysWrapper = $this->mocker
            ->mockService('tikamoon.dalenys.bridge.dalenys_bridge', DalenysBridgeInterface::class);

        $openDalenysWrapper
            ->shouldReceive('createDalenys')
            ->andReturn(new Dalenys('test'));

        $openDalenysWrapper
            ->shouldReceive('paymentVerification')
            ->andReturn(true);

        $openDalenysWrapper
            ->shouldReceive('isPostMethod')
            ->andReturn(true);

        $openDalenysWrapper
            ->shouldReceive('setAccountKey', 'setSecretKey', 'setEnvironment', 'setMerchantId', 'setKeyVersion');

        $openDalenysWrapper
            ->shouldReceive('getAccountKey')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getSecretKey')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getMerchantId')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getKeyVersion')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getEnvironment')
            ->andReturn(Dalenys::TEST);

        $action();

        $this->mocker->unmockAll();
    }

    /**
     * @param callable $action
     */
    public function canceledPayment(callable $action)
    {
        $openDalenysWrapper = $this->mocker
            ->mockService('tikamoon.dalenys.bridge.dalenys_bridge', DalenysBridgeInterface::class);

        $openDalenysWrapper
            ->shouldReceive('createDalenys')
            ->andReturn(new Dalenys('test'));

        $openDalenysWrapper
            ->shouldReceive('paymentVerification')
            ->andReturn(false);

        $openDalenysWrapper
            ->shouldReceive('isPostMethod')
            ->andReturn(true);

        $openDalenysWrapper
            ->shouldReceive('setSecretKey', 'setEnvironment', 'setMerchantId', 'setKeyVersion');

        $openDalenysWrapper
            ->shouldReceive('getSecretKey')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getMerchantId')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getKeyVersion')
            ->andReturn('test');

        $openDalenysWrapper
            ->shouldReceive('getEnvironment')
            ->andReturn(Dalenys::TEST);

        $action();

        $this->mocker->unmockAll();
    }
}
