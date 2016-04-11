<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventUpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prefered = ['fr', 'en', 'es', 'de', 'it', 'zh'];

        $builder
            ->add('title', TextType::class)
            ->add('locales', LocaleType::class, [
                'multiple'          => true,
                'preferred_choices' => $prefered,
            ])
            ->add('fallback', LocaleType::class, [
                'preferred_choices' => $prefered,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => EventUpdateTranslationType::class,
                'label'      => false,
            ])
            ->add('mode', VatModeType::class, [
                'expanded' => true,
            ])
            ->add('vat', NumberType::class)
            ->add('leftColor', TextType::class)
            ->add('rightColor', TextType::class)
            ->add('textColor', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales']);
        $resolver->setDefaults([
            'data_class' => 'Proximum\Vimeet\Application\Command\Event\Update',
        ]);
    }
}
