<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setDefaults([
           'placeholder' => 'choice.placeholder',
           'choices' => function (Options $options) {
               return $this->getChoices($options);
           }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lib_choice';
    }

    /**
     * @param $options
     *
     * @return array
     */
    private function getChoices($options)
    {
        $template  = $options['template'];
        $locale    = $options['locale'];
        $choices   = [];
        $optgroups = false;

        foreach ($template['choices'] as $key => $choice) {
            // choice with optgroups
            if (isset($choice['items']) && is_array($choice['items'])) {
                $optgroups = true;
                $items = [];

                foreach ($choice['items'] as $keyItem => $item) {
                    $items[$keyItem] = $item['label'][$locale];
                }

                asort($items);

                // label is the optgroup
                $choices[$choice['label'][$locale]] = $items;
            } else {
                $choices[$key] = $choice['label'][$locale];
            }
        }

        if ($optgroups) {
            ksort($choices);
        } else {
            asort($choices);
        }

        return $choices;
    }
}
