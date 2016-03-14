<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownGroupException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownTypeException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\WrongTypeException;
use Proximum\Vimeet\Application\Components\Template\Row\AbstractProduct;
use Proximum\Vimeet\Application\Components\Template\Row\ProductAddedRow;
use Proximum\Vimeet\Application\Components\Template\Row\ProductRadioRow;
use Proximum\Vimeet\Application\Components\Template\Templates;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;

class GroupFactory
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * GroupFactory constructor.
     *
     * @param TemplateFactory $templateFactory
     */
    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param array  $template
     * @param array  $data
     * @param string $locale
     *
     * @return array
     */
    public function createGroupsFromArray(array $template, array $data, $locale)
    {
        // Create template
        $templates = $this->templateFactory->createTemplatesFromArray($template);

        // Create groups
        $groups = [];
        foreach ($data as $groupName => $groupData) {
            $group = $this->createGroupViewFromArray($templates, $groupName, $groupData, $locale);

            // Filter empty groups
            if (!$group->hasRows()) {
                continue;
            }

            $groups[$groupName] = $group;
        }

        // Included in
        $this->includedPass($templates, $groups, $data, $locale);

        return $groups;
    }

    /**
     * @param Templates $template
     * @param string    $groupName
     * @param array     $groupData
     * @param string    $locale
     *
     * @return GroupView
     */
    private function createGroupViewFromArray(Templates $template, $groupName, array $groupData, $locale)
    {
        $label = $template->getTemplate($groupName)->getLabel($locale);
        $group = new GroupView($label);

        foreach ($groupData as $rowName => $rowData) {

            // Filter empty row (to improve)
            if (
                isset($rowData['value']) && $rowData['value'] === false ||
                isset($rowData['participant']) && $rowData['participant'] === false ||
                isset($rowData['planning']) && $rowData['planning'] === false ||
                isset($rowData['quantity']) && $rowData['quantity'] === 0
            ) {
                continue;
            }

            $row = $this->createRowViewFromArray($template, $groupName, $rowName, $rowData, $locale);

            $group->addRowView($rowName, $row);
        }

        return $group;
    }

    /**
     * @param Templates $templates
     * @param string    $groupName
     * @param string    $rowName
     * @param array     $rowData
     * @param string    $locale
     *
     * @throws WrongTypeException
     * @throws UnknownGroupException
     * @throws UnknownTypeException
     * @return RowView
     *
     */
    private function createRowViewFromArray(Templates $templates, $groupName, $rowName, array $rowData, $locale)
    {
        $product = $templates->getTemplate($groupName)->getRow($rowName);

        if (!$product instanceof AbstractProduct) {
            throw new WrongTypeException($product, AbstractProduct::class);
        }

        if ($product instanceof ProductRadioRow) {
            $label = $product->getChoiceLabel($rowData['value'], $locale);
            $price = $product->getChoiceUnitPrice($rowData['value']);
        } else {
            $label = $product->getLabel($locale);
            $price = $product->getUnitPrice();
        }

        $quantity = isset($rowData['quantity']) ? $rowData['quantity'] : 1;

        $row           = new RowView($label, $price, $quantity, $product->getUpdatableUntil(), $product->isUpdatable());
        $row->editable = $product instanceof ProductAddedRow;

        return $row;
    }

    /**
     * @param Templates   $templates
     * @param GroupView[] $groups
     * @param array       $data
     * @param string      $locale
     *
     * @throws WrongTypeException
     */
    private function includedPass(Templates $templates, array &$groups, array $data, $locale)
    {
        foreach ($templates->getTemplates() as $groupName => $group) {
            foreach ($group->getRows() as $typeName => $type) {
                if (!$type instanceof AbstractProduct) {
                    continue;
                }

                $includedIn = $type->getIncludedIn();

                foreach ($includedIn as $path => $quantity) {

                    // get parts
                    $parts = explode('.', $path);

                    if (!isset($data[$parts[0]]) || !isset($data[$parts[0]][$parts[1]]) || !isset($data[$parts[0]][$parts[1]]['value'])) {
                        continue;
                    }

                    // get value
                    $value = $data[$parts[0]][$parts[1]]['value'];

                    if (count($parts) === 2 && $value === true || count($parts) === 3 && $value === $parts[2]) {
                        $row            = $this->createRowViewFromArray($templates, $groupName, $typeName, ['value' => $value, 'quantity' => $quantity], $locale);
                        $row->unitPrice = 0;
                        $groups[$parts[0]]->getRow($parts[1])->addIncluded($row);
                    }
                }
            }
        }
    }
}
