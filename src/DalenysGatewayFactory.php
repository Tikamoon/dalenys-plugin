<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin;

use Tikamoon\DalenysPlugin\Action\ConvertPaymentAction;
use Tikamoon\DalenysPlugin\Action\NotifyAction;
use Tikamoon\DalenysPlugin\Bridge\DalenysBridgeInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class DalenysGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'dalenys',
            'payum.factory_title' => 'Dalenys',

            'payum.action.convert' => new ConvertPaymentAction(),

            'payum.http_client' => '@tikamoon.dalenys.bridge.dalenys_bridge',
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => '',
                'secure_key' => '',
                'merchant_id' => '',
                'api_key_id' => '',
                'key_version' => '',
                'number_of_payments' => 1,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['account_key', 'secret_key', 'environment', 'merchant_id', 'key_version', 'api_key_id', 'number_of_payments'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var DalenysBridgeInterface $dalenysBridge */
                $dalenysBridge = $config['payum.http_client'];

                $dalenysBridge->setAccountKey($config['account_key']);
                $dalenysBridge->setSecretKey($config['secret_key']);
                $dalenysBridge->setMerchantId($config['merchant_id']);
                $dalenysBridge->setApiKeyId($config['api_key_id']);
                $dalenysBridge->setKeyVersion($config['key_version']);
                $dalenysBridge->setNumberOfPayments((int) $config['number_of_payments']);
                $dalenysBridge->setEnvironment($config['environment']);
                return $dalenysBridge;
            };
        }
    }
}
