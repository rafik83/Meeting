<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Order\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class OrderMergeFactory
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var VatApplicable
     */
    private $vatApplicable;

    /**
     * OrderViewFactory constructor.
     *
     * @param GroupFactory  $groupFactory
     * @param VatApplicable $vatApplicable
     */
    public function __construct(GroupFactory $groupFactory, VatApplicable $vatApplicable)
    {
        $this->groupFactory  = $groupFactory;
        $this->vatApplicable = $vatApplicable;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return OrderView
     */
    public function createFromSheet(Sheet $sheet, $locale)
    {
        return $this->createFromOrders($sheet->getOrders()->toArray(), $locale);
    }


    /**
     * @param Order[] $orders
     * @param string  $locale
     *
     * @return OrderView
     */
    public function createFromOrders(array $orders, $locale)
    {
        $template = [];
        $data     = [];
        $vats     = [];

        foreach ($orders as $order) {
            $this->mergeTemplate($template, $order->getPackageTemplate());
            $this->mergeData($data, $order->getPackageData());

            $groups = new Groups(
                $this->groupFactory->createGroupsFromArray($order->getPackageTemplate(), $order->getPackageData(), $locale),
                $this->vatApplicable->onOrder($order),
                $order->getVatRate()
            );

            if (isset($vats[(string) $groups->vat])) {
                $vats[(string) $groups->vat] += $groups->getTaxes();
            } else {
                $vats[(string) $groups->vat] = $groups->getTaxes();
            }
        }

        return new OrderMerge($this->groupFactory->createGroupsFromArray($template, $data, $locale), $vats);
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
