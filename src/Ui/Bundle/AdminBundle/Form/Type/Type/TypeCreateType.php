<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\SheetTemplateChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TemplateChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template', TemplateChoiceType::class, [
                'placeholder' => '',
            ])
            ->add('sheetTemplate', SheetTemplateChoiceType::class, [
                'events'      => $options['events'],
                'required'    => true,
                'expanded'    => false,
                'multiple'    => false,
                'placeholder' => '',
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TypeTranslationType::class,
                'label'      => false,
            ])
            ->add('position', NumberType::class)
            ->add('validationCriteria', TypeValidationCriteriaType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['events']);
        $resolver->setDefaults([
            'data_class'    => 'Proximum\Vimeet\Application\Command\Type\Create',
            'csrf_token_id' => 'type_create',
        ]);
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
}
