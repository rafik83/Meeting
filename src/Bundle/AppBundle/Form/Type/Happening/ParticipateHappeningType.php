<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipateHappeningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('participants', 'participant_choice', [
                'sheet'      => $options['sheet'],
                'multiple'   => true,
                'expanded'   => true,
            ])
        ;
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'sheet'
        ]);

        $resolver->setDefaults([
            'data_class' => Participate::class,
            'intention'  => 'participate_happening',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'participate_happening';
    }
}
