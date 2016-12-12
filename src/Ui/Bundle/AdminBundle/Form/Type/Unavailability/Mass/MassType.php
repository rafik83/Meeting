<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\ChoiceType as CategoryChoiceType;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as BaseChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class MassType extends BaseAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('category', CategoryChoiceType::class, [
                'event'    => $options['event'],
                'required' => true,
            ])
            ->add('begin', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'display_date'  => false,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('end', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'display_date'  => false,
                'required'      => true,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('blocking', BaseChoiceType::class, [
                'choices'  => [
                    'form.unavailability.mass.children.blocking.answer.true'  => true,
                    'form.unavailability.mass.children.blocking.answer.false' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label'      => false,
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
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
    }
}
