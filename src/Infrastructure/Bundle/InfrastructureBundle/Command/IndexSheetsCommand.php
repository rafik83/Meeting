<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSheetsCommand extends Command
{
    const NAME = 'vimeet:sheets:index';

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var SheetIndexerInterface
     */
    private $sheetIndexer;

    /**
     * IndexSheetsCommand constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SheetIndexerInterface $sheetIndexer)
    {
        parent::__construct(self::NAME);

        $this->sheetRepository = $sheetRepository;
        $this->sheetIndexer    = $sheetIndexer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index sheets in elasticsearch')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetIds = explode(',', $input->getArgument('sheetIds'));

        $sheets = $this->sheetRepository->findByIds($sheetIds);

        $this->sheetIndexer->updateSheets($sheets);
    }
}
