<?php

declare(strict_types=1);

namespace Tikamoon\DalenysPlugin\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class DalenysExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSofortForm', [$this, 'getSofortForm'], ['needs_environment' => true])
        ];
    }

    public function getSofortForm(Environment $environment, $gatewayConfig, $order): string
    {
        $identifier = $gatewayConfig['merchant_id'];

        $data = [
            "IDENTIFIER" => $identifier,
            "OPERATIONTYPE" => "payment",
            "DESCRIPTION" => "payment",
            "CLIENTIDENT" => $order->getCustomer()->getEmail(),
            "CLIENTEMAIL" => $order->getCustomer()->getEmail(),
            "DESCRIPTION" => "Payment for " . $order->getCustomer()->getEmail(),
            "ORDERID" => $order->getId(),
            "AMOUNT" => $order->getTotal(),
            "BILLINGCOUNTRY" => $order->getBillingAddress()->getCountryCode(),
            "VERSION" => "3.0",
            "EXTRADATA" => "method=sofort_de"
        ];

        $password = $gatewayConfig['account_key'];
        $clear_string = $password;

        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                foreach ($value as $index => $val) {
                    $clear_string .= $key . '[' . $index . ']=' . $val . $password;
                }
            } else {
                if ($key == 'HASH') {
                    // Skip HASH parameter if supplied
                    continue;
                } else {
                    $clear_string .= $key . '=' . $value . $password;
                }
            }
        }

        $hash = hash('sha256', $clear_string);

        return $environment->render("@dalenys/_sofort_form.html.twig", ['hash' => $hash, 'identifier' => $identifier, 'order' => $order]);
    }
}
