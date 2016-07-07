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

abstract class AbstractPaymentChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modes = Mode::getPaymentModes();

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
}
