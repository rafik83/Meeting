<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\Update;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\MeetingParticipantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('participants', MeetingParticipantChoiceType::class, [
                'sheet'    => $options['data']->sheet,
                'meeting'  => $options['data']->meeting,
                'multiple' => true,
                'expanded' => true,
                'locale'   => $options['locale'],
            ])
            ->add('message', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method'     => 'POST',
            'data_class' => Update::class,
        ]);

        $resolver->setRequired(['locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'meeting_update';
    }
}
