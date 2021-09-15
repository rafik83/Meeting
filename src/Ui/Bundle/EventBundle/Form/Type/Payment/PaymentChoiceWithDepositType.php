<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment;

use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDeposit;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentChoiceWithDepositType extends AbstractPaymentChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('deposit', ChoiceType::class, [
                'choices'  => [
                    'form.payment_choice_with_deposit.children.depositMode.deposit' => true,
                    'form.payment_choice_with_deposit.children.depositMode.total'   => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ChoiceWithDeposit::class,
        ]);
    }
}
