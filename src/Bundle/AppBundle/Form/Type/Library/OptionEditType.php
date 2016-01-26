<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType as CoreCheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class OptionEditType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sheet    = isset($options['sheet']) && $options['sheet'] !== null ? $options['sheet'] : null;
        $product  = $options['product'];
        $template = $options['template'];
        $locale   = $options['locale'];

        $builder->add('value', CoreCheckboxType::class, [
            'label'    => $template['label'][$locale],
            'required' => $product !== null ? $product->getRequired() : false,
        ]);

        if ($sheet !== null
            && $product !== null
            && $product->getQuantityMaxWithoutPurchased($sheet->getPackageData()) > 0
        ) {
            $builder->add('quantity', QuantityType::class, [
                'min'   => $product->getQuantityMin(),
                'max'   => $product->getQuantityMaxWithoutPurchased($sheet->getPackageData()),
                'range' => $product->getQuantityRange(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $view->vars['display_price']    = true;
        $view->vars['is_included']      = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_option';
    }
}
