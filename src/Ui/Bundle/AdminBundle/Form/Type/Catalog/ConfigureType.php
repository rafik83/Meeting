<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\CategoryChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigureType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('catalogPublic', CheckboxType::class)
            ->add('types', TypeChoiceType::class, [
                'event' => $options['event'],
                'locale' => $options['locale'],
                'user' => $options['user'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('categories', CategoryChoiceType::class, [
                'event' => $options['event'],
                'locale' => $options['locale'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['user', 'event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('user', Admin::class);
    }

}
