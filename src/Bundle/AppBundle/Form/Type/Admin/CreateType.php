<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin;

use Proximum\Vimeet\Application\Command\Admin\Create;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\EventEntityType;
use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('password', TextType::class, [
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'form.create_admin.role.organizer'   => Admin::ROLE_ORGANIZER,
                    'form.create_admin.role.super_admin' => Admin::ROLE_SUPER_ADMIN,
                ],
                'choices_as_values' => true,
                'required'          => true,
            ])
            ->add('events', EventEntityType::class, [
                'required'    => false,
                'expanded'    => true,
                'multiple'    => true,
                'placeholder' => '',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'create_admin';
    }
}
