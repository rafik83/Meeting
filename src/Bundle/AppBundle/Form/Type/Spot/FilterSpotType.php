<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSpotType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'form.filter_spot_type.children.reference.label'
            ])
            ->add('meetingCapacity', NumberType::class, [
                'label' => 'form.filter_spot_type.children.meetingCapacity.label'
            ])
            ->add('seatCapacity', NumberType::class, [
                'label' => 'form.filter_spot_type.children.seatCapacity.label'
            ])
            ->add('size', NumberType::class, [
                'label' => 'form.filter_spot_type.children.size.label'
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'form.filter_spot_type.children.active.label'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.filter_spot_type.children.submit.label'
            ])
        ;
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
