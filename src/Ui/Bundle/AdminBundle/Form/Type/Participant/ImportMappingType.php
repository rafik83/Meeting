<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\ImportMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportMappingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mappings', MappingType::class, [
                'csvHeaders'          => $options['csvHeaders'],
                'registrationHeaders' => $options['registrationHeaders'],
                'label'               => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'registrationHeaders', 'csvHeaders']);
        $resolver->setDefaults([
            'data_class' => ImportMapping::class,
        ]);
    }
}
