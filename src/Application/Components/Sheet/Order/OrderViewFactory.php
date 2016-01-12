<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Template\Template;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
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
     * @param string   $groupName
     * @param string   $rowName
     * @param array    $rowData
     * @param string   $locale
     *
     * @return RowView
     */
    private function createRowViewFromArray(Template $template, $groupName, $rowName, array $rowData, $locale)
    {
        $label = $template->getGroup($groupName)->getType($rowName)->getLabel($locale);

        $row = new RowView($label, 2000, 1);

        return $row;
    }
}
