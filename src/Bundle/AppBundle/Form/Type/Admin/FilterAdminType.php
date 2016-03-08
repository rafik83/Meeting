<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\EventEntityType;
use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label'             => false,
                'choices_as_values' => true,
                'choices'           => [
                    'form.filter_admin.role.all'         => null,
                    'form.filter_admin.role.organizer'   => Admin::ROLE_ORGANIZER,
                    'form.filter_admin.role.super_admin' => Admin::ROLE_SUPER_ADMIN,
                ],
            ])
            ->add('event', EventEntityType::class, [
                'label'       => false,
                'required'    => false,
                'expanded'    => false,
                'multiple'    => false,
                'placeholder' => 'form.filter_admin.event.all',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.admin.meeting_request.list.filter.children.submit.label',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
