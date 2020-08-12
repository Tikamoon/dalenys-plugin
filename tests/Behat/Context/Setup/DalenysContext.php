<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tests\Tikamoon\DalenysPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Tikamoon\DalenysPlugin\Legacy\Dalenys;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class DalenysContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var ExampleFactoryInterface
     */
    private $paymentMethodExampleFactory;

    /**
     * @var FactoryInterface
     */
    private $paymentMethodTranslationFactory;

    /**
     * @var ObjectManager
     */
    private $paymentMethodManager;

    /**
     * @param SharedStorageInterface $sharedStorage
     * @param PaymentMethodRepositoryInterface $paymentMethodRepository
     * @param ExampleFactoryInterface $paymentMethodExampleFactory
     * @param FactoryInterface $paymentMethodTranslationFactory
     * @param ObjectManager $paymentMethodManager
     */
    public function __construct(
        SharedStorageInterface $sharedStorage,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ExampleFactoryInterface $paymentMethodExampleFactory,
        FactoryInterface $paymentMethodTranslationFactory,
        ObjectManager $paymentMethodManager
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodExampleFactory = $paymentMethodExampleFactory;
        $this->paymentMethodTranslationFactory = $paymentMethodTranslationFactory;
        $this->paymentMethodManager = $paymentMethodManager;
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and Dalenys Checkout gateway
     */
    public function theStoreHasAPaymentMethodWithACodeAndDalenysCheckoutGateway(
        $paymentMethodName,
        $paymentMethodCode
    ) {
        $paymentMethod = $this->createPaymentMethod($paymentMethodName, $paymentMethodCode, 'Dalenys');
        $paymentMethod->getGatewayConfig()->setConfig([
            'environment' => Dalenys::TEST,
            'merchant_id' => 'TEST',
            'key_version' => 'TEST',
            'secret_key' => 'TEST',
            'payum.http_client' => '@tikamoon.dalenys.bridge.dalenys_bridge',
        ]);

        $this->paymentMethodManager->flush();
    }

    /**
     * @param string $name
     * @param string $code
     * @param string $description
     * @param bool $addForCurrentChannel
     * @param int|null $position
     *
     * @return PaymentMethodInterface
     */
    private function createPaymentMethod(
        $name,
        $code,
        $description = '',
        $addForCurrentChannel = true,
        $position = null
    ) {

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => $description,
            'gatewayName' => 'dalenys',
            'gatewayFactory' => 'dalenys',
            'enabled' => true,
            'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);

        if (null !== $position) {
            $paymentMethod->setPosition($position);
        }

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
