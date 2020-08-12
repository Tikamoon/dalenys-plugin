<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

/**
 * @author @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
interface CreatePageInterface extends BaseCreatePageInterface
{
    /**
     * @param string $secretKey
     */
    public function setDalenysPluginGatewaySecretKey($secretKey);

    /**
     * @param string $merchantId
     */
    public function setDalenysPluginGatewayMerchantId($merchantId);

    /**
     * @param string $keyVersion
     */
    public function setDalenysPluginGatewayKeyVersion($keyVersion);

    /**
     * @param string $environment
     */
    public function setDalenysPluginGatewayEnvironment($environment);

    /**
     * @param string $message
     *
     * @return bool
     */
    public function findValidationMessage($message);
}
