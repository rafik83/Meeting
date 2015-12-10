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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['template'] = $options['template'];
        $view->vars['locale'] = $options['locale'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setDefaults([
            'placeholder' => function (Options $options) {
                return $options['template']['placeholder'][$options['locale']];
            },
            'translation_domain' => false,
        ]);
        $resolver->setDefined(['sheet']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\CountryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_country';
    }
}
