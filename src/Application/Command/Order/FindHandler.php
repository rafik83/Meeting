<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\IsNotAllowedToFindOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;
use Proximum\Vimeet\Domain\Order\Finder;
use Proximum\Vimeet\Domain\Order\Numero\Exploder;
use Proximum\Vimeet\Domain\Order\Numero\Validator;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class FindHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Find $find
     *
     * @return FindResult
     *
     * @throws IsNotAllowedToFindOrderException
     * @throws InvalidNumeroOrderException
     * @throws OrderNotFoundException
     */
    public function handle(Find $find)
    {
        if (!Finder::isAllowedToFind($find->admin)) {
            throw new IsNotAllowedToFindOrderException(
                sprintf('This user of id %s is not allowed to find an order', $find->admin->getId())
            );
        }

        $numero = $find->numero;

        if (!Validator::isValid($numero)) {
            throw new InvalidNumeroOrderException(
                sprintf('The given numero %s is not valid', $numero)
            );
        }

        list($eventId, $sheetId, $orderId) = Exploder::explode($numero);

        $order = $this->orderRepository->findByNumero($eventId, $sheetId, $orderId);

        if ($order === null || !Finder::isAllowedToAccess($find->admin, $order)) {
            throw new OrderNotFoundException(
                sprintf('The order with numero %s does not exist', $numero)
            );
        }

        return new FindResult($order->getSheet());
    }
}
