<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffectType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tip', TipSelectType::class, [
                'tipViews' => $options['tipViews'],
                'attr' => ['data-tip-preview' => 1],
            ])
            ->add('types', TypeCheckboxType::class, [
                'typeViews' => $options['typeViews'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class);
    }

    /**{@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['typeViews', 'tipViews']);
        $resolver->setAllowedTypes('typeViews', 'array');
        $resolver->setAllowedTypes('tipViews', 'array');
    }

    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_event_affect';
    }
}
