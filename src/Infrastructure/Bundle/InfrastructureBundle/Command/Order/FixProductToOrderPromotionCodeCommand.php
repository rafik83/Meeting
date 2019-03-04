<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Order;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Order\FixPromotionOrderProducts;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixProductToOrderPromotionCodeCommand extends Command
{
    public const NAME = 'vimeet:order:fix-order-promotion-code-product';

    /**
     * @var CommandBusInterface
     */
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
            ->setDescription('Set products for every order promotion code if missing one')
            ->setHelp('Warning! This command will be applied to every order of every events !')
            ->addOption('force', null, InputOption::VALUE_NONE);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryMode = false === $input->getOption('force');

        $this->commandBus->handle(new FixPromotionOrderProducts($dryMode));
    }
}
