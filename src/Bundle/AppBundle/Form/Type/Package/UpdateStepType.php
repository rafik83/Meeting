<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package;

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
            ->add('packageData', new DataPackageType(), [
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
            'data_class' => 'Proximum\Vimeet\Application\Command\Package\UpdateStep',
            'intention'  => 'update_sheet_package_step',
        ]);

        $resolver->setRequired(['template', 'locale']);
        $resolver->setDefined(['sheet']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'update_sheet_package_step';
    }
}
