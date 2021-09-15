<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Vianeo;

use Proximum\Vimeet\Application\ThirdParty\Vianeo\Command\VianeoApiCall;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Command\VianeoApiCallHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VianeoApiCallCommand extends Command
{
    const NAME = 'vimeet:api:vianeo-call';
    const SHEET_ID = 'sheetId';

    /** @var VianeoApiCallHandler */
    private $vianeoApiCallHandler;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(
        VianeoApiCallHandler $vianeoApiCallHandler,
        SheetRepositoryInterface $sheetRepository
    ) {
        parent::__construct(self::NAME);

        $this->sheetRepository = $sheetRepository;
        $this->vianeoApiCallHandler = $vianeoApiCallHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Call the Vianeo API with the sheet data')
            ->addArgument(self::SHEET_ID, InputArgument::REQUIRED, 'Sheet id');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetId = $input->getArgument(self::SHEET_ID);
        $sheet = $this->sheetRepository->getSheetById($sheetId);

        if (!$sheet instanceof Sheet) {
            throw new \InvalidArgumentException(sprintf('Sheet not found for id %s', $sheetId));
        }

        $this->vianeoApiCallHandler->handle(new VianeoApiCall($sheet));
    }
}
