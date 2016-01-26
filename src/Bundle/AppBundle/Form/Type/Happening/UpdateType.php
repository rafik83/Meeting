<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $options['event'];

        $builder
            ->add('category', CategoryType::class, [
                'event' => $options['event']
            ])
            ->add('begin', DateTimeType::class, [
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('end', DateTimeType::class, [
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('titleTranslations', CollectionType::class, [
                'entry_type' => TitleTranslationType::class,
                'label'      => false,
            ])
            ->add('descriptionTranslations', CollectionType::class, [
                'entry_type' => DescriptionTranslationType::class,
                'label'      => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => 'Proximum\Vimeet\Application\Command\Happening\Update',
            'csrf_token_id' => 'happening_update',
        ]);
        $resolver->setRequired(['event']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'happening_update';
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['titleTranslations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
        foreach ($view->children['descriptionTranslations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }
}
