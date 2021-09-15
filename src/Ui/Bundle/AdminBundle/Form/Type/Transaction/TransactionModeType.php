<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction;

use Proximum\Vimeet\Domain\Payment\Mode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionModeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices'      => Mode::getTransactionModes(),
            'choice_label' => function ($value) {
                return sprintf('form.transaction.children.transaction_mode.%s', $value);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
