<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TemplateObject\BooleanObject $booleanObject */
        $booleanObject = $options['object'];
        $locale        = $options['locale'];

        $builder
            ->add('boolean', ChoiceType::class, [
                'choices'      => TemplateObject\BooleanObject::getBooleanValues(),
                'choice_label' => function ($key, $value) {
                    return sprintf('boolean.%s', $value);
                },
                'expanded'     => true,
                'multiple'     => false,
                'label'        => $booleanObject->getOption('label', $locale),
                'required'     => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\BooleanObject::class);
        $resolver->setDefaults([
            'label'      => true,
            'data_class' => TemplateObject\BooleanObject::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'boolean_object_data';
    }
}
