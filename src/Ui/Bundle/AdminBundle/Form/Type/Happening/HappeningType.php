<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class HappeningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $options['event'];

        $builder
            ->add('category', CategoryType::class, ['event' => $event, 'locale' => $options['locale']])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label'      => false,
            ])
            ->add('begin', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'display_date'  => false,
                'view_timezone' => $event->getTimeZone(),
                'attr'  => [
                    'class' => 'datetimepicker-range-element'
                ],
            ])
            ->add('end', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'display_date'  => false,
                'view_timezone' => $event->getTimeZone(),
                'attr'  => [
                    'class' => 'datetimepicker-range-element'
                ],
            ])
            ->add('questionAllowed', ChoiceType::class, [
                'choices'  => [
                    'form.happening_create.children.questionAllowed.answer.true'  => true,
                    'form.happening_create.children.questionAllowed.answer.false' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('limitParticipant', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
                'help' => 'form.happening_create.children.limitParticipant.help'
            ])
            ->add('talkings', CollectionType::class, [
                'entry_type'     => TalkingType::class,
                'entry_options'  => ['label' => false, 'event' => $event],
                'prototype_data' => ['speaker' => null, 'position' => 0],
                'allow_add'      => true,
                'allow_delete'   => true,
            ])
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
        $resolver->setRequired(['event', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'happening';
    }
}
