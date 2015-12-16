<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository, SheetRepositoryInterface $sheetRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(Create $create)
    {
        $this->orderRepository->add(new Order(
            $create->sheet,
            $create->state,
            $create->proFormaTemplate,
            $create->packageData,
            $create->packageTemplate,
            $create->billingData,
            $create->createdAt,
            $create->paymentMode
        ));

        $sheetData = $create->sheet->getPackageData();

        // merge products data
        foreach ($sheetData as $keyStep => $step) {
            if ($step !== null) {
                foreach ($step as $keyProduct => $product) {
                    if ($product !== null) {
                        foreach ($product as $keyField => $field) {
                            if (isset($create->packageData[$keyStep][$keyProduct])) {
                                if ('quantity' === $keyField) {
                                    // addition
                                    $sheetData[$keyStep][$keyProduct][$keyField] = $field + $create->packageData[$keyStep][$keyProduct][$keyField];
                                } else {
                                    // replace
                                    $sheetData[$keyStep][$keyProduct][$keyField] = $create->packageData[$keyStep][$keyProduct][$keyField];
                                }
                            }
                        }
                    }
                }
            }
        }

        // add missing product data
        foreach ($create->packageData as $keyStep => $step) {
            if (!isset($sheetData[$keyStep])) {
                $sheetData[$keyStep] = $create->packageData[$keyStep];
            } else {
                foreach ($step as $keyProduct => $product) {
                    if (!isset($sheetData[$keyStep][$keyProduct])) {
                        $sheetData[$keyStep][$keyProduct] = $create->packageData[$keyStep][$keyProduct];
                    }
                }
            }
        }

        $create->sheet->setPackageData($sheetData);
        $this->sheetRepository->set($create->sheet);
    }
}
