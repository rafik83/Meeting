<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractEventTipType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('onMeetingManagement', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onCatalog', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onPrintPlanning', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onSheet', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onAgenda', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onProgram', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onConfirmationPhone', CheckboxType::class, [
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'  => TipEventTranslationType::class,
                'label'       => false,
            ])
            ->add('types', TypeChoiceType::class, [
                'event'    => $options['event'],
                'expanded' => true,
                'locale'   => $options['locale'],
                'multiple' => true,
                'user'     => $options['admin'],
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'event',
            'locale',
            'admin',
        ]);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('locale', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(
                Intl::getLocaleBundle()->getLocaleName($translation->vars['name'])
            );
        }
    }
}
