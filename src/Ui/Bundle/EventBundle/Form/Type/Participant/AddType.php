<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required'    => true,
                'label'       => false,
                'placeholder' => 'form.add_participant.children.firstName.placeholder'
            ])
            ->add('lastName', TextType::class, [
                'required'    => true,
                'label'       => false,
                'placeholder' => 'form.add_participant.children.lastName.placeholder'
            ])
            ->add('email', EmailType::class, [
                'required'    => true,
                'label'       => false,
                'placeholder' => 'form.add_participant.children.email.placeholder'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Add::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'add_participant';
    }
}
