<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\DataType;
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
            'data_class'    => 'Proximum\Vimeet\Application\Command\Package\UpdateStep',
            'csrf_token_id' => 'update_sheet_package_step',
        ]);

        $resolver->setRequired(['template', 'locale']);
        $resolver->setDefined(['sheet']);
    }
}
