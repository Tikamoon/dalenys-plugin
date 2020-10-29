<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
interface CreatePageInterface extends BaseCreatePageInterface
{
    /**
     * {@inheritdoc}
     */
    public function setDalenysPluginGatewayAccountKey($accountKey)
    {
        $this->getDocument()->fillField('Account key', $accountKey);
    }

    /**
     * {@inheritdoc}
     */
    public function setDalenysPluginGatewaySecretKey($secretKey)
    {
        $this->getDocument()->fillField('Secure key', $secretKey);
    }

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
     * @param int $numberOfPayments
     */
    public function setDalenysPluginGatewayNumberOfPayments($numberOfPayments);

    /**
     * @param string $message
     *
     * @return bool
     */
    public function findValidationMessage($message);
}
