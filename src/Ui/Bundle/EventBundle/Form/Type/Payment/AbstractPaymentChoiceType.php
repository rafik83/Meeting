<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment;

use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\PaymentConditionsView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractPaymentChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PaymentConditionsView $paymentConditionsView */
        $paymentConditionsView = $options['paymentConditionsView'];

        $builder
            ->add('mode', ChoiceType::class, [
                'choices'      => $paymentConditionsView->paymentModes,
                'choice_label' => function ($value) {
                    return sprintf('form.payment_choice.children.paymentMode.%s', $value);
                },
                'expanded'     => true,
                'multiple'     => false,
                'required'     => true,
                'choice_attr'  => function ($paymentMode) {
                    /* @var string paymentMode */
                    if (in_array($paymentMode, Mode::getModeThatRequiredPaymentInfo())) {
                        return ['data-payment-info' => 1];
                    }

                    return ['data-payment-info' => 0];
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('paymentConditionsView');
        $resolver->setAllowedTypes('paymentConditionsView', PaymentConditionsView::class);
    }
}
