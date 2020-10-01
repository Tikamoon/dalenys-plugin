<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Page\Admin\PaymentMethod;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
class CreatePage extends BaseCreatePage implements CreatePageInterface
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
     * {@inheritdoc}
     */
    public function setDalenysPluginGatewayMerchantId($merchantId)
    {
        $this->getDocument()->fillField('Merchant ID', $merchantId);
    }

    /**
     * {@inheritdoc}
     */
    public function setDalenysPluginGatewayKeyVersion($keyVersion)
    {
        $this->getDocument()->fillField('Key version', $keyVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function setDalenysPluginGatewayEnvironment($environment)
    {
        $this->getDocument()->selectFieldOption('Environment', $environment);
    }

    /**
     * {@inheritdoc}
     */
    public function findValidationMessage($message)
    {
        $elements = $this->getDocument()->findAll('css', '.sylius-validation-error');

        /** @var NodeElement $element */
        foreach ($elements as $element) {
            if ($element->getText() === $message) {
                return true;
            }
        }

        return false;
    }
}
