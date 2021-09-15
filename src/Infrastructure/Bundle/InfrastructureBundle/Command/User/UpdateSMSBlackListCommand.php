<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\User;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Command\User\Phone\UpdateBlackList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSMSBlackListCommand extends Command
{
    const NAME = 'vimeet:user:update-phone-blacklist';

    /** @var CommandBus */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
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
            ->setDescription('Update the ViMeet SMS blacklist from the OVH SMS API')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandBus->handle(new UpdateBlackList());
    }
}
