<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\User\Agenda\Version;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\CleanOldVersionsCommand as CleanOldVersions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanOldVersionsCommand extends Command
{
    public const NAME = 'vimeet:user:clean-old-agenda-version';

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(CommandBusInterface $commandBus)
    {
        parent::__construct(self::NAME);

        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Clean old User Agenda Version')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandBus->handle(new CleanOldVersions());
    }
}
