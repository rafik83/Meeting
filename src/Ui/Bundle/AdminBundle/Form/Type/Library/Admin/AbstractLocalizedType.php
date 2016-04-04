<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Library\Admin;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;

class AbstractLocalizedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', CollectionType::class, [
                'entry_type' => TextType::class,
                'label'      => 'form.admin_lib.children.label.label',
            ])
            ->add('tags', ChoiceType::class, [
                'required'     => false,
                'label'        => 'form.admin_lib.children.tags.label',
                'multiple'     => true,
                'choices'      => Tag::getAll(),
                'choice_label' => function ($value) {
                    return $value;
                },
            ])
            ->add('required', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.admin_lib.children.required.label',
            ])
            ->add('private', CheckboxType::class, [
                'required' => false,
                'label'    => 'form.admin_lib.children.private.label',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['label'] as $labelTranslation) {
            $localeLabel                     = Intl::getLocaleBundle()->getLocaleName($labelTranslation->vars['name']);
            $labelTranslation->vars['label'] = ucfirst($localeLabel);
        }
    }
}
