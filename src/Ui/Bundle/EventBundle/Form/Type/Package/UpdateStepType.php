<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\UpdateStep;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\DataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateStepType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('packageData', DataType::class, [
                'template' => $options['template'],
                'locale'   => $options['locale'],
                'sheet'    => $options['sheet'],
                'cart'     => $options['cart'],
                'step'     => $options['step'],
                'label'    => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdateStep::class,
        ]);

        $resolver->setRequired(['template', 'locale', 'step', 'cart']);
        $resolver->setDefined(['sheet']);
    }
}
