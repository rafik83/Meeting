<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\TechEvent;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler\PrepareImportSheetsHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareImportSheetsCommand extends Command
{
    private const NAME = 'vimeet:api:tech-event-import-sheets';

    /** @var PrepareImportSheetsHandler */
    private $importSheetsHandler;

    public function __construct(PrepareImportSheetsHandler $importSheetsHandler)
    {
        parent::__construct(self::NAME);

        $this->importSheetsHandler = $importSheetsHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Import sheets through TechEvent API');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->importSheetsHandler->handle();
    }
}
