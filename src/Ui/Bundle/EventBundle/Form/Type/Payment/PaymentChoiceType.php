<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment;

use Proximum\Vimeet\Application\Command\Payment\Choice;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Domain\Payment\Mode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $options['event'];
        $total = $options['total'];

        $depositApplicable = DepositApplicable::isApplicable($event, new \DateTime(), $total);
        $modes = !$depositApplicable ? Mode::getPaymentModes() : Mode::getAllPaymentModes();

        $builder
            ->add('mode', ChoiceType::class, [
                'choices'      => $modes,
                'choice_label' => function ($value) {
                    return sprintf('form.payment_choice.children.paymentMode.%s', $value);
                },
                'expanded'     => true,
                'multiple'     => false,
                'required'     => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'total']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Choice::class,
        ]);
    }
}
