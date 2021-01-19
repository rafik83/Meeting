<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Order;

use Proximum\Vimeet\Application\Command\Order\Export\ExportOrders;
use Proximum\Vimeet\Application\Command\Order\Export\ExportOrdersHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportOrderCommand extends Command
{
    const NAME = 'vimeet:order:export';

    /** @var ExportOrdersHandler */
    private $exportOrdersHandler;

    /**
     * @param ExportOrdersHandler $exportOrdersHandler
     */
    public function __construct(ExportOrdersHandler $exportOrdersHandler)
    {
        parent::__construct(self::NAME);

        $this->exportOrdersHandler = $exportOrdersHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate html for the participants plannings')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument('emailToNotify', InputArgument::REQUIRED, 'Email of the admin to notify for completion of the task')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale for the email')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->exportOrdersHandler->handle(
            new ExportOrders(
                $input->getArgument('event'),
                $input->getArgument('emailToNotify'),
                $input->getArgument('locale')
            )
        );
    }
}
