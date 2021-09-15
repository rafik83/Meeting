<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Comexposium\Webservice;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\PrepareImportSheetsHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComexposiumPrepareImportSheetsCommand extends Command
{
    public const NAME = 'vimeet:comexposium:prepare-import';

    /** @var PrepareImportSheetsHandler */
    private $prepareImportSheetsHandler;

    public function __construct(PrepareImportSheetsHandler $prepareImportSheetsHandler)
    {
        parent::__construct(self::NAME);

        $this->prepareImportSheetsHandler = $prepareImportSheetsHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Prepare import sheets from Comexposium')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareImportSheetsHandler->handle();
    }
}
