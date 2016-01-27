<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Package\EditProduct;
use Proximum\Vimeet\Application\Command\Package\EditProductHandler;
use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Proximum\Vimeet\Application\Components\Payment\PaymentMode;
use Proximum\Vimeet\Application\Components\Product\ProductBuilder;
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class EditProductHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateNegativeOrder()
    {
        $cartRepository  = $this->prophesize(CartRepositoryInterface::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $packageTemplate = [
            'group1' => [
                'label' => 'group1-label',
                'template' => [
                    'row1' => [
                        'label' => ['fr' => 'row1-label'],
                        'description' => ['fr' => 'row1-description'],
                        'type' => 'lib_option',
                        'unitPrice' => 100,
                        'quantity' => [
                            'min' => 0,
                            'max' => 4
                        ],
                        'updatableUntil' => (new \DateTime("+1 week"))->format('Y-m-d H:i:s'),
                    ]
                ]
            ]
        ];

        $event = new Event();
        $event->setBillingTemplate([]);
        $type = new Type($event);
        $type->setPackageTemplate($packageTemplate);

        $sheet = new Sheet($event, $type, [], [
            'group1' => [
                'row1' => [
                    'value'    => true,
                    'quantity' => 2,
                ]
            ]
        ]);
        $sheet->setBillingData([]);
        $sheet->setPackageData([]);

        $createdAt = new \DateTime();
        $cart      = new Cart([], $packageTemplate, $sheet, $createdAt);

        $productBuilder = new ProductBuilder();
        $template = $productBuilder->createFromType($type);
        $step = $template->getStep('group1');
        $product = $step->getProduct('row1');

        $expectedOrder = new Order(
            $sheet,
            Order::STATE_PAID,
            [
                'group1' => [
                    'row1' => [
                        'value' => true,
                        'quantity' => -1,
                    ]
                ]
            ],
            [
                'group1' => [
                    'label' => 'group1-label',
                    'template' => [
                        'row1' => [
                            'label' => 'row1-label',
                            'description' => 'row1-description',
                            'type' => 'lib_option',
                            'unitPrice' => 100,
                        ],
                    ],
                ],
            ],
            [],
            [],
            $createdAt,
            PaymentMode::NOPAYMENT
        );

        // we have 2 quantities
        $editProduct = new EditProduct($sheet, $cart, $product, $createdAt, 'fr', 2);
        // we select 1, we expect to have a negative order with -1 (2 - 3)
        $editProduct->productItem['quantity'] = 1;

        $orderRepository->add($expectedOrder)->shouldBeCalled();

        $handler = new EditProductHandler(
            $cartRepository->reveal(),
            $orderRepository->reveal(),
            $sheetRepository->reveal(),
            new OrderManager()
        );
        $handler->handle($editProduct);
    }

    public function testAddUpdatedProductToCart()
    {
        $cartRepository  = $this->prophesize(CartRepositoryInterface::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $packageTemplate = [
            'group1' => [
                'label' => 'group1-label',
                'template' => [
                    'row1' => [
                        'label' => ['fr' => 'row1-label'],
                        'description' => ['fr' => 'row1-description'],
                        'type' => 'lib_option',
                        'unitPrice' => 100,
                        'quantity' => [
                            'min' => 0,
                            'max' => 4
                        ],
                        'updatableUntil' => (new \DateTime("+1 week"))->format('Y-m-d H:i:s'),
                    ]
                ]
            ]
        ];

        $event = new Event();
        $event->setBillingTemplate([]);
        $type = new Type($event);
        $type->setPackageTemplate($packageTemplate);

        $sheet = new Sheet($event, $type, [], [
            'group1' => [
                'row1' => [
                    'value'    => true,
                    'quantity' => 2,
                ]
            ]
        ]);
        $sheet->setBillingData([]);
        $sheet->setPackageData([]);

        $createdAt = new \DateTime();
        $cart      = new Cart([], $packageTemplate, $sheet, $createdAt);

        $productBuilder = new ProductBuilder();
        $template = $productBuilder->createFromType($type);
        $step = $template->getStep('group1');
        $product = $step->getProduct('row1');

        $expectedCart =  new Cart(
            [
                'group1' => [
                    'row1' => [
                        'value' => true,
                        'quantity' => 1,
                    ]
                ]
            ],
            $packageTemplate,
            $sheet,
            $createdAt
        );

        // we have 2 quantities
        $editProduct = new EditProduct($sheet, $cart, $product, $createdAt, 'fr', 2);
        // we select 3, we expect to have 1 (3 - 2) in cart
        $editProduct->productItem['quantity'] = 3;

        $cartRepository->set($expectedCart)->shouldBeCalled();

        $handler = new EditProductHandler(
            $cartRepository->reveal(),
            $orderRepository->reveal(),
            $sheetRepository->reveal(),
            new OrderManager()
        );
        $handler->handle($editProduct);
    }
}
