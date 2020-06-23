<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;

class ExportHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(QueryBusInterface $queryBus, CommandBusInterface $commandBus)
    {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function handle(Export $command): void
    {
        $content = $this->queryBus->handle(new PrepareContent($command->event, $command->locale));
        $file = $this->commandBus->handle(new PersistContent($command->event, $content));
        $this->commandBus->handle(new Notify($command->event, $command->admin, $command->locale, $file));
    }
}
