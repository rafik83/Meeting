<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Billing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Proximum\Vimeet\Application\Command\Billing\UpdateInfo;

class UpdateInfoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('firstname', TextType::class, ['required' => true])
            ->add('function', TextType::class, ['required' => false])
            ->add('phone', TextType::class, ['required' => false])
            ->add('mobile', TextType::class, ['required' => false])
            ->add('email', TextType::class, ['required' => true])
            ->add('company', TextType::class, ['required' => true])
            ->add('street', TextType::class, ['required' => true])
            ->add('zipcode', TextType::class, ['required' => true])
            ->add('city', TextType::class, ['required' => true])
            ->add('country', TextType::class, ['required' => true])
            ->add('vatNumber', TextType::class, ['required' => false])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdateInfo::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'billing_info_update';
    }
}
