<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Query;

class QueryBus implements QueryBusInterface
{
    /** @var CommandBus */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param Query $query
     *
     * @return mixed
     */
    public function handle(Query $query)
    {
        return $this->commandBus->handle($query);
    }
}
