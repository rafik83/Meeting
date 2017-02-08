<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Payment\Mode;
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
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('mode', ChoiceType::class, [
                'choices'      => $event->getConfiguration()->getPaymentModes(),
                'choice_label' => function ($value) {
                    return sprintf('form.payment_choice.children.paymentMode.%s', $value);
                },
                'expanded'     => true,
                'multiple'     => false,
                'required'     => true,
                'choice_attr'  => function ($paymentMode) {
                    /** @var string paymentMode */
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
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
    }
}
