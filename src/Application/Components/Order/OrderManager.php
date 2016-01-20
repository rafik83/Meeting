<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Order;

use Proximum\Vimeet\Domain\Model\Order;

class OrderManager
{
    /**
     * @param array $sheetData
     * @param array $packageDataTwo
     *
     * @return array
     */
    public function mergeTwoPackageData(array $sheetData, array $packageDataTwo)
    {
        // merge products data
        $packageDataTwo = $this->cleanFalseOption($packageDataTwo);

        foreach ($sheetData as $keyStep => $step) {
            if ($step === null) {
                continue;
            }

            foreach ($step as $keyProduct => $product) {
                if ($product === null) {
                    continue;
                }

                foreach ($product as $keyField => $field) {
                    if (isset($packageDataTwo[$keyStep][$keyProduct][$keyField])) {
                        if ('quantity' === $keyField) {
                            // addition
                            $sheetData[$keyStep][$keyProduct][$keyField] = $field + $packageDataTwo[$keyStep][$keyProduct][$keyField];
                        } elseif (is_bool($field)) {
                            if (true === $packageDataTwo[$keyStep][$keyProduct][$keyField]) {
                                // assign if true
                                $sheetData[$keyStep][$keyProduct][$keyField] = $packageDataTwo[$keyStep][$keyProduct][$keyField];
                            }
                        } else {
                            // replace
                            $sheetData[$keyStep][$keyProduct][$keyField] = $packageDataTwo[$keyStep][$keyProduct][$keyField];
                        }
                    }
                }
            }
        }

        // add missing product data
        foreach ($packageDataTwo as $keyStep => $step) {
            if (!isset($sheetData[$keyStep])) {
                $sheetData[$keyStep] = $packageDataTwo[$keyStep];
            } else {
                foreach ($step as $keyProduct => $product) {
                    if (!isset($sheetData[$keyStep][$keyProduct])) {
                        $sheetData[$keyStep][$keyProduct] = $packageDataTwo[$keyStep][$keyProduct];
                    }
                }
            }
        }

        return $sheetData;
    }

    /**
     * @param Order $order
     * @return int
     */
    public function getParticipantBoughtForOrder(Order $order)
    {
        if (empty($order->getPackageTemplate()) || empty($order->getPackageData())) {
            return 0;
        }

        foreach ($order->getPackageTemplate() as $blockKey => $block) {
            foreach ($block['template'] as $productKey => $product) {
                if (isset($product['type']) && 'lib_participant' === $product['type']) {
                    if (isset($order->getPackageData()[$blockKey][$productKey]['participant'])
                    && true === $order->getPackageData()[$blockKey][$productKey]['participant']
                    && isset($order->getPackageData()[$blockKey][$productKey]['quantity'])
                    ) {
                        return $order->getPackageData()[$blockKey][$productKey]['quantity'];
                    } else {
                        return 0;
                    }
                }
            }
        }

        return 0;
    }

    public function cleanFalseOption(array $packageData)
    {
        foreach ($packageData as $stepKey => $step) {
            $packageData[$stepKey] = array_filter($step, function ($product) {
                if ($product === null) {
                    return false;
                }

                foreach ($product as $keyField => $field) {
                    if (is_bool($field) && false === $field) {
                        return false;
                    }
                }

                return true;
            });
        }

        return array_filter($packageData, function ($step) {
            return !($step === null || empty($step));
        });
    }
}
