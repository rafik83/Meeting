<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event;

use Symfony\Component\Form\AbstractType;
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
            ->add('title', 'text')
            ->add('locales', 'locale', [
                'multiple' => true,
            ])
            ->add('fallback', 'locale')
            ->add('translations', 'collection', [
                'type' => new UpdateTranslationType(),
                'label' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales']);
        $resolver->setDefaults([
            'data_class' => 'Proximum\Vimeet\Application\Command\Event\Update',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'event_update';
    }
}
