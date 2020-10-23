<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\Tikamoon\DalenysPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;
use Webmozart\Assert\Assert;

/**
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class ManagingPaymentMethodsContext implements Context
{
    /**
     * @var CreatePageInterface
     */
    private $createPage;

    /**
     * @param CreatePageInterface $createPage
     */
    public function __construct(CreatePageInterface $createPage)
    {
        $this->createPage = $createPage;
    }

    /**
     * @Given I want to create a new payment method with Dalenys gateway factory
     */
    public function iWantToCreateANewPaymentMethodWithDalenysGatewayFactory()
    {
        $this->createPage->open(['factory' => 'dalenys']);
    }

    /**
     * @When I configure it with test Dalenys credentials
     */
    public function iConfigureItWithTestDalenysCredentials()
    {
        $this->createPage->setDalenysPluginGatewayAccountKey('test');
        $this->createPage->setDalenysPluginGatewaySecretKey('test');
        $this->createPage->setDalenysPluginGatewayMerchantId('test');
        $this->createPage->setDalenysPluginGatewayKeyVersion('test');
        $this->createPage->setDalenysPluginGatewayEnvironment('Test');
        $this->createPage->setDalenysPluginGatewayNumberOfPayments(1);
    }

    /**
     * @Then I should be notified that the secure key is invalid
     */
    public function iShouldBeNotifiedThatTheSecureKeyIsInvalid()
    {
        Assert::true($this->createPage->findValidationMessage('Please enter the Security Code.'));
    }

    /**
     * @Then I should be notified that the merchant ID is invalid
     */
    public function iShouldBeNotifiedThatTheMerchantIdIsInvalid()
    {
        Assert::true($this->createPage->findValidationMessage('Please enter the Merchant ID.'));
    }

    /**
     * @Then I should be notified that the Key version is invalid
     */
    public function iShouldBeNotifiedThatTheKeyVersionIsInvalid()
    {
        Assert::true($this->createPage->findValidationMessage('Please enter the Key version.'));
    }
}
