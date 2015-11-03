<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceWithDescription extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];

        // Choices must be an array of objects, ChoiceType don't work as we expect with array of array
        $choices = array_map(function ($value) {
            return new \ArrayObject($value);
        }, $template['choices']);

        $builder
            ->add('value', 'choice', [
                'choices'           => $choices,
                'expanded'          => true,
                'choices_as_values' => true,
                'choice_label'      => function ($value) use ($locale) {
                    return $value['label'][$locale];
                },
                'required'          => isset($template['required']) ? $template['required'] : true,
            ]);

        if (isset($template['quantity']['min'])
            && isset($template['quantity']['max'])
            && $template['quantity']['min'] < $template['quantity']['max']
        ) {
            $builder->add('quantity', new QuantityType(), [
                'min'   => $template['quantity']['min'],
                'max'   => $template['quantity']['max'],
                'range' => isset($template['quantity']['range']) ? $template['quantity']['range'] : 1
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['template'] = $options['template'];
        $view->vars['locale']   = $options['locale'];
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
        return 'choice_with_description';
    }
}

