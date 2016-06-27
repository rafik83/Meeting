<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Application\Command\Event\BillingConfiguration;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration\AddressTranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration\FootersTranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration\IbanTranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration\PaymentConditionTranslationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ibanTranslations', CollectionType::class, [
                'entry_type' => IbanTranslationType::class,
                'label'      => false,
            ])
            ->add('billingAddressTranslations', CollectionType::class, [
                'entry_type' => AddressTranslationType::class,
                'label'      => false,
            ])
            ->add('paymentConditionTranslations', CollectionType::class, [
                'entry_type' => PaymentConditionTranslationType::class,
                'label'      => false,
            ])
            ->add('footerTranslations', CollectionType::class, [
                'entry_type' => FootersTranslationType::class,
                'label'      => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['billingAddressTranslations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }

        foreach ($view->children['paymentConditionTranslations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }

        foreach ($view->children['footerTranslations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }

        foreach ($view->children['ibanTranslations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingConfiguration::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_billing_configuration';
    }
}
