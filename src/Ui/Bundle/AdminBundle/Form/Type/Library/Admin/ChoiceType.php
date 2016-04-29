<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Library\Admin;

use Symfony\Component\Form\Extension\Core\Type\CollectionType as CoreCollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType as CoreTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('placeholder', CoreCollectionType::class, [
                'entry_type' => CoreTextType::class,
                'help' => 'form.admin_lib_choice.children.placeholder.help',
            ])
            ->add(
                'choices',
                CoreCollectionType::class,
                [
                    'entry_type'    => ChoiceItemType::class,
                    'entry_options' => ['locales' => $options['locales']],
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        foreach ($view->children['placeholder'] as $labelTranslation) {
            $localeLabel = Intl::getLocaleBundle()->getLocaleName($labelTranslation->vars['name']);
            $labelTranslation->vars['label'] = ucfirst($localeLabel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_lib_choice';
    }
}
