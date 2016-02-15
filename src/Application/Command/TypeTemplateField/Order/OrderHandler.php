<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Order;

use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class OrderHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param Order $order
     */
    public function handle(Order $order)
    {
        $rows  = [];
        $types = $order->group->getTypes();
        asort($order->fieldsOrder);

        foreach ($order->fieldsOrder as $fieldName => $value) {
            $rows[$fieldName] = $types[$fieldName]->getOptions();
        }

        $order->type->setTemplateRows($order->templateName, $order->group->getName(), $rows);
        $this->typeRepository->set($order->type);
    }
}
