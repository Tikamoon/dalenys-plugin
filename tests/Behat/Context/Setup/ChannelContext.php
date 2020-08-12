<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Test\Services\DefaultChannelFactory;

/**
 * @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class ChannelContext implements Context
{
    /**
     * @var DefaultChannelFactory
     */
    private $defaultChannelFactory;

    /**
     * @param DefaultChannelFactory $defaultChannelFactory
     */
    public function __construct(DefaultChannelFactory $defaultChannelFactory)
    {
        $this->defaultChannelFactory = $defaultChannelFactory;
    }

    /**
     * @Given adding a new channel in :arg1
     */
    public function addingANewChannelIn()
    {
        $this->defaultChannelFactory->create('FR', 'France', 'EUR');
    }
}
