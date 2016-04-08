<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Library;

use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType as CoreCheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class OptionType extends AbstractLocalizedType
{
    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @param OrderManager $orderManager
     */
    public function __construct(OrderManager $orderManager)
    {
        $this->orderManager = $orderManager;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $view->vars['display_price']    = true;
        $view->vars['is_included']      = false;

        if (null !== $options['product']
            && isset($options['sheet'])
            && null !== $options['sheet']
            && null !== $options['cart']
        ) {
            $product = $options['product'];
            $cart    = $options['cart'];

            $includeds = $product->getIncludingFromPurchase($cart->getData());

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

            if ($view->vars['is_included'] && $view->vars['quantity_included'] === null) {
                $view->vars['display_price'] = false;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sheet    = isset($options['sheet']) && $options['sheet'] !== null ? $options['sheet'] : null;
        $cart     = $options['cart'];
        $product  = $options['product'];
        $template = $options['template'];
        $locale   = $options['locale'];
        $checked  = false;

        if ($sheet !== null
            && $product !== null
            && $cart !== null
            && (!empty($product->getIncludingFromPurchase($sheet->getPackageData()))
                || !empty($product->getIncludingFromPurchase($cart->getData()))
            )
        ) {
            $packageData = $this->orderManager->mergeTwoPackageData($sheet->getPackageData(), $cart->getData());

            foreach ($product->getIncludingFromPurchase($packageData) as $includedIn) {
                if (null === $includedIn->getQuantity()) {
                    $checked = true;
                }
            }

            if ($checked === false) {
                $checked = $product->getRemainingQuantityMax($sheet->getPackageData()) === 0;
            }
        }

        $builder->add('value', CoreCheckboxType::class, [
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
            && ($product->allowQuantity($sheet->getPackageData())
                || (empty($sheet->getPackageData()) && $product->allowQuantity($cart->getData()))
            )
        ) {
            $builder->add('quantity', QuantityType::class, [
                'min'   => $product->getQuantityMin(),
                'max'   => $product->getRemainingQuantityMax($sheet->getPackageData()) - $product->getQuantityIncludedWithPurchase($cart->getData()),
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
