<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('translations', CollectionType::class, [
                'entry_type'    => MessageTranslationType::class,
                'entry_options' => [
                    'event'  => $options['event'],
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('csrf_token_id', 'messaging_message_create');
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
    }

    public function getBlockPrefix()
    {
        return 'messaging_create_message';
    }
}
