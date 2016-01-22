<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class OrderMergeFactory
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * OrderMergeFactory constructor.
     *
     * @param GroupFactory $groupFactory
     */
    public function __construct(GroupFactory $groupFactory)
    {
        $this->groupFactory = $groupFactory;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return OrderView
     */
    public function createFromSheet(Sheet $sheet, $locale)
    {
        return $this->createFromOrders(
            $sheet->getOrders()->toArray(),
            $sheet->getEvent()->getMode(),
            $sheet->getEvent()->getVat(),
            $locale
        );
    }


    /**
     * @param Order[] $orders
     * @param string  $mode
     * @param float   $vat
     * @param string  $locale
     *
     * @return OrderView
     */
    public function createFromOrders(array $orders, $mode, $vat, $locale)
    {
        $template = [];
        $data     = [];

        foreach ($orders as $order) {
            $this->mergeTemplate($template, $order->getPackageTemplate());
            $this->mergeData($data, $order->getPackageData());
        }

        return new OrderMerge(
            $this->groupFactory->createGroupsFromArray($template, $data, $locale),
            $mode,
            $vat
        );
    }

    /**
     * @param array $merge
     * @param array $template
     */
    private function mergeTemplate(array &$merge, array $template)
    {
        foreach ($template as $groupName => $group) {
            if (isset($merge[$groupName])) {

                foreach ($group['template'] as $typeName => $type) {
                    if (!isset($merge[$groupName]['template'][$typeName])) {
                        $merge[$groupName]['template'][$typeName] = $type;
                    }
                }

            } else {
                $merge[$groupName] = $group;
            }
        }
    }

    /**
     * @param array $merge
     * @param array $data
     */
    private function mergeData(array &$merge, array $data)
    {
        foreach ($data as $groupName => $group) {
            if (isset($merge[$groupName])) {

                foreach ($group as $typeName => $type) {

                    if (isset($merge[$groupName][$typeName])) {

                        if (is_array($type) && is_bool($type['value'])) {
                            $merge[$groupName][$typeName]['value'] |= $type['value'];
                        }

                        if (is_array($type) && isset($type['quantity'])) {
                            $merge[$groupName][$typeName]['quantity'] += $type['quantity'];
                        }

                    } else {
                        $merge[$groupName][$typeName] = $type;
                    }

                }

            } else {
                $merge[$groupName] = $group;
            }
        }
    }
}
