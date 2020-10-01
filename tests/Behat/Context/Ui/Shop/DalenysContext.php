<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Tests\Tikamoon\DalenysPlugin\Behat\Mocker\DalenysMocker;
use Tests\Tikamoon\DalenysPlugin\Behat\Page\External\DalenysCheckoutPageInterface;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class DalenysContext implements Context
{
    /**
     * @var DalenysMocker
     */
    private $dalenysMocker;

    /**
     * @var CompletePageInterface
     */
    private $summaryPage;

    /**
     * @var DalenysCheckoutPageInterface
     */
    private $dalenysCheckoutPage;

    /**
     * @var ShowPageInterface
     */
    private $orderDetails;

    /**
     * @param CompletePageInterface $summaryPage
     * @param DalenysMocker $dalenysMocker
     * @param DalenysCheckoutPageInterface $dalenysCheckoutPage
     * @param ShowPageInterface $orderDetails
     */
    public function __construct(
        DalenysMocker $dalenysMocker,
        CompletePageInterface $summaryPage,
        DalenysCheckoutPageInterface $dalenysCheckoutPage,
        ShowPageInterface $orderDetails
    ) {
        $this->orderDetails = $orderDetails;
        $this->dalenysCheckoutPage = $dalenysCheckoutPage;
        $this->summaryPage = $summaryPage;
        $this->dalenysMocker = $dalenysMocker;
    }

    /**
     * @When I confirm my order with Dalenys payment
     * @Given I have confirmed my order with Dalenys payment
     */
    public function iConfirmMyOrderWithDalenysPayment()
    {
        $this->summaryPage->confirmOrder();
    }

    /**
     * @When I sign in to Dalenys and pay successfully
     */
    public function iSignInToDalenysAndPaySuccessfully()
    {
        $this->dalenysMocker->completedPayment(function () {
            $this->dalenysCheckoutPage->pay();
        });
    }

    /**
     * @When I cancel my Dalenys payment
     * @Given I have cancelled Dalenys payment
     */
    public function iCancelMyDalenysPayment()
    {
        $this->dalenysMocker->canceledPayment(function () {
            $this->dalenysCheckoutPage->cancel();
        });
    }

    /**
     * @When I try to pay again Dalenys payment
     */
    public function iTryToPayAgainDalenysPayment()
    {
        $this->dalenysMocker->completedPayment(function () {
            $this->orderDetails->pay();
        });
    }
}
