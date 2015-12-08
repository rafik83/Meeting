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
            'choices'     => function (Options $options) {
                return $this->getChoices($options);
            },
            'translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_choice';
    }

    /**
     * @param array $options
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
            if (!isset($choice['label'][$locale])) {
                continue;
            }

            // choice with optgroups
            if (isset($choice['choices']) && is_array($choice['choices'])) {
                $optgroups = true;
                $items     = [];

                foreach ($choice['choices'] as $keyItem => $item) {
                    if (isset($item['label'][$locale])) {
                        $items[$keyItem] = $item['label'][$locale];
                    }
                }

                asort($items);

                // label is the optgroup
                $choices[$choice['label'][$locale]] = $items;

                continue;
            }

            $choices[$key] = $choice['label'][$locale];
        }

        if ($optgroups) {
            ksort($choices);
        } else {
            asort($choices);
        }

        return $choices;
    }
}
