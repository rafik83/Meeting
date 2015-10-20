<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('data', new ParticipantDataType(), ['template' => $options['template'], 'locale' => $options['locale']])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setDefaults([
            'data_class' => 'Proximum\Vimeet\Application\Command\Participant\Create',
            'intention'  => 'participant_create',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'participant_create';
    }
}
