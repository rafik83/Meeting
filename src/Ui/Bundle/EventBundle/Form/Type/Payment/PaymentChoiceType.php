<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment;

use Proximum\Vimeet\Application\Command\Payment\Choice;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentChoiceType extends AbstractPaymentChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Choice::class,
        ]);
    }
}
