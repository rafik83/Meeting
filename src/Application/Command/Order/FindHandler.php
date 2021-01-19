<?php

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
     * @throws IsNotAllowedToFindOrderException
     * @throws InvalidNumeroOrderException
     * @throws OrderNotFoundException
     *
     * @return FindResult
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

        $orderNumeroView = Exploder::explode($numero);

        $order = $this->orderRepository->findByNumero($orderNumeroView);

        if (null === $order || !Finder::isAllowedToAccess($find->admin, $order)) {
            throw new OrderNotFoundException(
                sprintf('The order with numero %s does not exist', $numero)
            );
        }

        return new FindResult($order->getSheet());
    }
}
