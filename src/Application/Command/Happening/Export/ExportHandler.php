<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\File\PersistContent;

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

        $file = $this->commandBus->handle(
            new PersistContent($command->event, $content, 'export_happening_participants_%s_%s.csv')
        );

        $this->commandBus->handle(new Notify($command->event, $command->admin, $command->locale, $file));
    }
}
