<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Catalog;

use Proximum\Vimeet\Application\Command\Catalog\Export\ExportProducts;
use Proximum\Vimeet\Application\Command\Catalog\Export\ExportProductsHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportProductsCommand extends Command
{
    const NAME = 'vimeet:products:export';

    /** @var ExportProductsHandler */
    private $exportProductsHandler;

    /**
     * @param ExportProductsHandler $exportProductsHandler
     */
    public function __construct(ExportProductsHandler $exportProductsHandler)
    {
        parent::__construct(self::NAME);

        $this->exportProductsHandler = $exportProductsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate csv for the list of products')
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
        $this->exportProductsHandler->handle(
            new ExportProducts(
                $input->getArgument('event'),
                $input->getArgument('emailToNotify'),
                $input->getArgument('locale')
            )
        );
    }
}
