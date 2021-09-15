<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction;

use Proximum\Vimeet\Domain\Model\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractTransactionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class)
            ->add('date', DateTimeType::class)
            ->add('reference', TextType::class, ['required' => false])
            ->add('state', ChoiceType::class, [
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    Transaction::STATE_PENDING   => Transaction::STATE_PENDING,
                    Transaction::STATE_PAID      => Transaction::STATE_PAID,
                    Transaction::STATE_CANCELLED => Transaction::STATE_CANCELLED,
                ],
                'choice_label' => function ($value) {
                    return sprintf('form.transaction.children.state.%s', $value);
                },
            ])
        ;
    }
}
