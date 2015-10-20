<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = is_string($options['template']) ? json_decode($options['template'], true) : $options['template'];
        $locale   = $options['locale'];

        foreach ($template as $i => $field) {
            $builder->add($i, $field['type'], ['label' => $field['label'][$locale]]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'participant';
    }
}
