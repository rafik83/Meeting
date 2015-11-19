<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\TypeTemplateField;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceLabelTranslationsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['locales'] as $locale) {
            $builder->add($locale, 'text', [
                'label'  => Intl::getLocaleBundle()->getLocaleName($locale),
                //'mapped' => true,
            ]);
        }

        /*
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            dump($data);

            $form = $event->getForm();


            foreach ($options['locales'] as $locale) {
                $form->add($locale, 'text', [
                    'label' => Intl::getLocaleBundle()->getLocaleName($locale),
                    'data' => $data->label[$locale],
                ]);
            }
        });
        */
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
    public function getName()
    {
        return 'type_template_field_update_choice_label_translations';
    }
}
