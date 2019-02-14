<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Product;

use Proximum\Vimeet\Application\Command\Product\Export\ExportProducts;
use Proximum\Vimeet\Application\Command\Product\Export\ExportProductsHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    public const NAME = 'vimeet:products:export';

    /** @var ExportProductsHandler */
    private $exportProductsHandler;

    public function __construct(ExportProductsHandler $exportProductsHandler)
    {
        parent::__construct(self::NAME);

        $this->exportProductsHandler = $exportProductsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate csv for the list of products')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument(
                'emailToNotify',
                InputArgument::REQUIRED,
                'Email of the admin to notify for completion of the task'
            )
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale for the email')
        ;
    }
    
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Proximum\Vimeet\Application\Exception\Order\Export\InvalidArgumentForExportException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
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
