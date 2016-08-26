<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['templateObjects', 'locale']);
        $resolver->setDefaults([
            'choices'      => function (Options $options) {
                return $options['templateObjects'];
            },
            'choice_label' => function (Options $options) {
                return function (TemplateObject $object) use ($options) {
                    return $object->getLabel($options['locale']);
                };
            },
            'choice_value' => function (TemplateObject $object = null) {
                return null !== $object ? $object->getKey() : null;
            },
            'choice_translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
