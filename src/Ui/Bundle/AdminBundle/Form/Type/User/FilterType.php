<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    const FILTER_NAME  = 'name';
    const FILTER_EMAIL = 'email';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextType::class, [
                'label'       => false,
                'placeholder' => '',
                'required'    => false,
            ])
            ->add('type', TypeChoiceType::class, [
                'label'       => 'form.user_filter.children.type.label',
                'placeholder' => '',
                'event'       => $options['event'],
                'locale'      => $options['locale'],
                'user'        => $options['user'],
            ])
            ->add('predefined', ChoiceType::class, [
                'label'       => 'form.filter.label',
                'placeholder' => '',
                'choices'     => [
                    'admin.users.lastName' => self::FILTER_NAME,
                    'admin.users.email'    => self::FILTER_EMAIL,
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('user', Admin::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_filter';
    }
}
