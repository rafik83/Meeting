<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;

use Proximum\Vimeet\Domain\Payment\Mode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentModeChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => function ($value) {
                return sprintf('form.payment_mode_choice.children.paymentMode.%s', $value);
            },
            'choices'      => Mode::getPaymentModes(),
            'expanded'     => true,
            'multiple'     => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'payment_mode_choice';
    }
}
