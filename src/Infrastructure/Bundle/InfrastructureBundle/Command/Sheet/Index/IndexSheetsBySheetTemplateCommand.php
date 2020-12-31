<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index;

use Proximum\Vimeet\Application\Command\Sheet\Template\Index;
use Proximum\Vimeet\Application\Command\Sheet\Template\IndexHandler;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSheetsBySheetTemplateCommand extends Command
{
    const NAME = 'vimeet:sheet-template:index-sheets';

    /** @var IndexHandler */
    private $indexHandler;

    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    /**
     * @param IndexHandler                     $indexHandler
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     */
    public function __construct(IndexHandler $indexHandler, SheetTemplateRepositoryInterface $sheetTemplateRepository)
    {
        parent::__construct(self::NAME);

        $this->indexHandler = $indexHandler;
        $this->sheetTemplateRepository = $sheetTemplateRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index Sheets by SheetTemplate')
            ->addArgument('sheetTemplateId', InputArgument::REQUIRED, 'SheetTemplate id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetTemplate = $this->sheetTemplateRepository->findById($input->getArgument('sheetTemplateId'));

        if (null === $sheetTemplate) {
            throw new \Exception('SheetTemplate not found.');
        }

        $this->indexHandler->handle(new Index($sheetTemplate));
    }
}
