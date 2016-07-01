<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bankInfo', TextType::class, [
                'required' => false,
            ])
            ->add('billingAddress', TextType::class, [
                'required' => true,
            ])
            ->add('paymentCondition', TextType::class, [
                'required' => false
            ])
            ->add('paymentFooter', TextType::class, [
                'required'    => false,
                'placeholder' => 'form.event_billing_configuration.children.footers.placeholder',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_billing_configuration_translations';
    }

}
