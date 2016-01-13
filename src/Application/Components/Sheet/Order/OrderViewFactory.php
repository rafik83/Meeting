<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\WrongTypeException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownGroupException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownTypeException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Template;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
use Proximum\Vimeet\Application\Components\Sheet\Template\Type\AbstractProductType;
use Proximum\Vimeet\Application\Components\Sheet\Template\Type\LibRadioType;
use Proximum\Vimeet\Domain\Model\Order;

class OrderViewFactory
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * OrderViewFactory constructor.
     *
     * @param TemplateFactory $templateFactory
     */
    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return OrderView
     */
    public function createFromOrder(Order $order, $locale)
    {
        $template = $this->templateFactory->createTemplateFromArray($order->getPackageTemplate());
        $data     = $order->getPackageData();
        $view     = new OrderView();

        // Validate data
        $template->validateData($data);

        // Set vat rate
        $view->setVat($order->getSheet()->getType()->getEvent()->getVat());

        // Set groups and types
        foreach ($data as $groupName => $groupData) {
            $group = $this->createGroupViewFromArray($template, $groupName, $groupData, $locale);
            $view->addGroupView($groupName, $group);
        }

        // Included in
        foreach ($template->getGroups() as $groupName => $group) {
            foreach ($group->getTypes() as $typeName => $type) {
                if ($type instanceof AbstractProductType) {
                    $includedIn = $type->getIncludedIn();

                    foreach ($includedIn as $path => $quantity) {

                        // get parts
                        $parts = explode('.', $path);

                        // get value
                        $value = $data[$parts[0]][$parts[1]]['value'];

                        if (count($parts) === 2 && $value === true || count($parts) === 3 && $value === $parts[2]) {
                            $row = $this->createRowViewFromArray($template, $groupName, $typeName, ['value' => $value, 'quantity' => $quantity], $locale);
                            $row->unitPrice = 0;
                            $view->getGroup($parts[0])->getRow($parts[1])->addIncluded($row);
                        }
                    }
                }
            }
        }

        return $view;
    }

    /**
     * @param Template $template
     * @param string   $groupName
     * @param array    $groupData
     * @param string   $locale
     *
     * @return GroupView
     */
    private function createGroupViewFromArray(Template $template, $groupName, array $groupData, $locale)
    {
        $label = $template->getGroup($groupName)->getLabel($locale);
        $group = new GroupView($label);

        foreach ($groupData as $rowName => $rowData) {
            $row = $this->createRowViewFromArray($template, $groupName, $rowName, $rowData, $locale);

            $group->addRowView($rowName, $row);
        }

        return $group;
    }

    /**
     * @param Template $template
     * @param          $groupName
     * @param          $rowName
     * @param array    $rowData
     * @param          $locale
     *
     * @return RowView
     * @throws WrongTypeException
     * @throws UnknownGroupException
     * @throws UnknownTypeException
     */
    private function createRowViewFromArray(Template $template, $groupName, $rowName, array $rowData, $locale)
    {
        $product = $template->getGroup($groupName)->getType($rowName);

        if (!$product instanceof AbstractProductType) {
            throw new WrongTypeException($product, AbstractProductType::class);
        }

        if ($product instanceof LibRadioType) {
            $label = $product->getChoiceLabel($rowData['value'], $locale);
            $price = $product->getChoiceUnitPrice($rowData['value']);
        } else {
            $label = $product->getLabel($locale);
            $price = $product->getUnitPrice();
        }

        $quantity = isset($rowData['quantity']) ? $rowData['quantity'] : 1;

        $row = new RowView($label, $price, $quantity);

        return $row;
    }
}
