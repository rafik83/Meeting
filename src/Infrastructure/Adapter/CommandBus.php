<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use League\Tactician\CommandBus as TacticianCommandBus;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Command;

class CommandBus implements CommandBusInterface
{
    /** @var TacticianCommandBus */
    private $commandBus;

    /**
     * @param TacticianCommandBus $commandBus
     */
    public function __construct(TacticianCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param Command $command
     *
     * @return mixed
     */
    public function handle(Command $command)
    {
        return $this->commandBus->handle($command);
    }
}
