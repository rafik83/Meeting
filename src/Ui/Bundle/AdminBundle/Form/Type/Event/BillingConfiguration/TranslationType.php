<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bankInfo', TextareaType::class, [
                'required'    => false,
                'placeholder' => 'form.event_billing_configuration.children.translations.prototype.children.bankInfo.placeholder',
            ])
            ->add('billingAddress', TextareaType::class, [
                'required' => true,
            ])
            ->add('paymentCondition', TextareaType::class, [
                'required' => false,
            ])
            ->add('paymentFooter', TextareaType::class, [
                'required'    => false,
                'placeholder' => 'form.event_billing_configuration.children.translations.prototype.children.paymentFooter.placeholder',
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
