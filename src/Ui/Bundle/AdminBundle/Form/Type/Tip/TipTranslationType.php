<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TipTranslationType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $preferred      = ['fr', 'en', 'es', 'de', 'it', 'zh'];

        $builder
            ->add('title', TextType::class)
            ->add('locale', LocaleType::class, [
                'preferred_choices' => $preferred,
            ])
            ->add('content', TextareaType::class);
    }
    
    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TipTranslation::class,
        ]);
    }
    
    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_translation';
    }
}
