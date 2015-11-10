<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart;

use Proximum\Vimeet\Application\Components\Cart\Carts\LibCartInterface;

class Cart
{
    /**
     * @var array
     */
    private $cartLibs = [];

    public function registerCart($name, LibCartInterface $cart)
    {
        $this->cartLibs[$name] = $cart;
    }

    /**
     * @param array $packageTemplate
     * @param array $packageData
     * @param $locale
     *
     * @return array
     */
    public function cartBuilder(array $packageTemplate, array $packageData, $locale)
    {
        $cart  = [];
        $total = 0;

        foreach ($packageTemplate as $blockKey => $block) {
            $data            = [];
            $data['title']   = $block['title'][$locale];
            $data['options'] = [];
            $subTotal        = 0;

            foreach ($block['template'] as $templateKey => $template) {
                if (isset($template['type'])) {
                    $options   = [];
                    $dataValue = isset($packageData[$blockKey][$templateKey]) ? $packageData[$blockKey][$templateKey] : null;

                    if ($template['type'] === 'choice_with_description') {
                        $options = $this->cartLibs['libChoiceWithDescription']->prepare($template, $dataValue, $locale);
                    } elseif ($template['type'] === 'lib_participant') {
                        $options = $this->cartLibs['libParticipant']->prepare($template, $dataValue, $locale);
                    } elseif ($template['type'] === 'lib_planning') {
                        $options = $this->cartLibs['libPlanning']->prepare($template, $dataValue, $locale);
                    } elseif ($template['type'] === 'upload_with_choices') {
                        $options = $this->cartLibs['libUploadWithChoices']->prepare($template, $dataValue, $locale);
                    } elseif ($template['type'] === 'lib_option') {
                        $options = $this->cartLibs['libOption']->prepare($template, $dataValue, $locale);
                    }
                }

                if(isset($options['total'])) {
                    $total += $options['total'];
                    $subTotal += $options['total'];
                }

                if ($options !== []) {
                    array_push($data['options'], $options);
                }
            }

            $data['subTotal'] = $subTotal;
            array_push($cart, $data);
        }

        $cart['total'] = $total;

        return $cart;
    }
}
