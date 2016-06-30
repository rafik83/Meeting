<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderDataType extends AbstractType
{
    const MAN   = 'man';
    const WOMAN = 'woman';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gender    = $options['object'];
        $locale    = $options['locale'];
        $label     = $options['label'];
        
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => array(
                    self::MAN   => true,
                    self::WOMAN => false,
                ),
                'expanded' => true,
                'multiple' => false,
                'label'       => $label ? $gender->getOption('label')[$locale] : false,
                'required'    => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', Object\Gender::class);
        $resolver->setDefaults([
            'label'      => true,
            'data_class' => Object\Gender::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'gender_data';
    }
}
