<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\DalenysPlugin\Form\Type;

use Tikamoon\DalenysPlugin\Legacy\Dalenys;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Vincent Notebaert <vnotebaert@kisoc.com>
 */
final class DalenysGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'tikamoon.dalenys.production' => Dalenys::PRODUCTION,
                    'tikamoon.dalenys.test' => Dalenys::TEST,
                ],
                'label' => 'tikamoon.dalenys.environment',
            ])
            ->add('merchant_id', TextType::class, [
                'label' => 'tikamoon.dalenys.merchant_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.dalenys.merchant_id.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->add('api_key_id', TextType::class, [
                'label' => 'tikamoon.dalenys.api_key_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.dalenys.api_key_id.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->add('secret_key', TextType::class, [
                'label' => 'tikamoon.dalenys.secret_key',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.dalenys.secret_key.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->add('key_version', TextType::class, [
                'label' => 'tikamoon.dalenys.key_version',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.dalenys.key_version.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();
                $data['payum.http_client'] = '@tikamoon.dalenys.bridge.dalenys_bridge';
                $event->setData($data);
            });
    }
}
