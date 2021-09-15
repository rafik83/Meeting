<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\PaymentConditions;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bankInfo', TextareaType::class)
            ->add('billingAddress', TextareaType::class)
            ->add('paymentCondition', TextareaType::class)
            ->add('paymentFooter', TextareaType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'event_payment_conditions_update_translations';
    }
}
