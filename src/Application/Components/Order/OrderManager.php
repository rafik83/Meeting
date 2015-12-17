<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Order;

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
        foreach ($sheetData as $keyStep => $step) {
            if ($step === null) {
                continue;
            }

            foreach ($step as $keyProduct => $product) {
                if ($product === null) {
                    continue;
                }

                foreach ($product as $keyField => $field) {
                    if (isset($packageDataTwo[$keyStep][$keyProduct])) {
                        if ('quantity' === $keyField) {
                            // addition
                            $sheetData[$keyStep][$keyProduct][$keyField] = $field + $packageDataTwo[$keyStep][$keyProduct][$keyField];
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
}
