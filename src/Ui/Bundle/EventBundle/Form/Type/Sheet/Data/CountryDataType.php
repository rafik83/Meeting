<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $options['object'];
        $locale  = $options['locale'];
        $label   = $options['label'];

        $builder
            ->add('country', CountryType::class, [
                'label'       => $label ? $country->getOption('label')[$locale] : false,
                'required'    => $country->getOption('required'),
                'placeholder' => $country->getOption('placeholder')[$locale],
                'attr'        => [
                    'class'            => 'form-control select2',
                    'data-placeholder' => $country->getOption('label')[$locale],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', Object\Country::class);
        $resolver->setDefaults([
            'label'      => false,
            'data_class' => Object\Country::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'country_data';
    }
}
