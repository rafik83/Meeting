<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class DeleteHandler
{
    /** @var LinkedSheetsRepositoryInterface */
    private $linkedSheetsRepository;

    public function __construct(LinkedSheetsRepositoryInterface $linkedSheetsRepository)
    {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
    }

    public function handle(Delete $command)
    {
        $this->linkedSheetsRepository->remove($command->linkedSheets);
    }
}
