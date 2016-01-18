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

class OrderMerger
{
    /**
     * @var OrderViewFactory
     */
    private $orderViewFactory;

    /**
     * OrderMergeFactory constructor.
     *
     * @param OrderViewFactory $orderViewFactory
     */
    public function __construct(OrderViewFactory $orderViewFactory)
    {
        $this->orderViewFactory = $orderViewFactory;
    }

    /**
     * @param Order[] $orders
     * @param float   $vat
     * @param string  $locale
     *
     * @return OrderView
     */
    public function merge(array $orders, $vat, $locale)
    {
        $template = [];
        $data     = [];

        foreach ($orders as $order) {
            $this->mergeTemplate($template, $order->getPackageTemplate());
            $this->mergeData($data, $order->getPackageData());
        }

        return $this->orderViewFactory->createFromData($template, $data, $vat, $locale);
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
