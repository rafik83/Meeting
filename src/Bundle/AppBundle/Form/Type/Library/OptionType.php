<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class OptionType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $quantity_allowed = 1;

        if (null !== $options['product'] && isset($options['sheet']) && null !== $options['sheet']) {
            $product = $options['product'];
            $sheet   = $options['sheet'];

            $includeds = $product->getIncludingFromPurchase($sheet->getPackageData());

            $view->vars['is_included']       = !empty($includeds);
            $view->vars['quantity_included'] = 0;

            foreach ($includeds as $included) {
                if (null === $included->getQuantity()) {
                    $view->vars['quantity_included'] = null;
                    break;
                } else {
                    $view->vars['quantity_included'] += $included->getQuantity();
                }
            }

            $quantity_allowed = $product->getRemainingQuantityMax($sheet->getPackageData());
        }

        $view->vars['quantity_allowed'] = $quantity_allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sheet    = isset($options['sheet']) && $options['sheet'] !== null ? $options['sheet'] : null;
        $product  = $options['product'];
        $template = $options['template'];
        $locale   = $options['locale'];
        $checked  = false;

        if ($sheet !== null
            && $product !== null
            && !empty($product->getIncludingFromPurchase($sheet->getPackageData()))
        ) {
            foreach ($product->getIncludingFromPurchase($sheet->getPackageData()) as $includedIn) {
                if (null === $includedIn->getQuantity()) {
                    $checked = true;
                }
            }
            if ($checked === false) {
                $checked = $product->getRemainingQuantityMax($sheet->getPackageData()) === 0 ? true : false;
            }
        }

        $builder->add('value', CheckboxType::class, [
            'label'    => $template['label'][$locale],
            'required' => $product !== null ? $product->getRequired() : false,
            'attr'     => [
                'disabled' => $checked,
                'readonly' => $checked,
                'checked'  => $checked,
            ],
        ]);

        if ($sheet !== null
            && false === $checked
            && $product !== null
            && $product->hasQuantity()
            && 0 !== $product->getRemainingQuantityMax($sheet->getPackageData())
        ) {
            $builder->add('quantity', QuantityType::class, [
                'min'   => $product->getQuantityMin(),
                'max'   => $product->getRemainingQuantityMax($sheet->getPackageData()),
                'range' => $product->getQuantityRange(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_option';
    }
}
