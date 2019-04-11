<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

use Proximum\Vimeet\Application\Criteria\LinkedSheets\AreRemovableLinkedSheetsCriteria;
use Proximum\Vimeet\Domain\Exception\DomainException;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class DeleteHandler
{
    /** @var LinkedSheetsRepositoryInterface */
    private $linkedSheetsRepository;

    /** @var AreRemovableLinkedSheetsCriteria */
    private $areRemovableLinkedSheetsCriteria;

    public function __construct(
        LinkedSheetsRepositoryInterface $linkedSheetsRepository,
        AreRemovableLinkedSheetsCriteria $areRemovableLinkedSheetsCriteria
    ) {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
        $this->areRemovableLinkedSheetsCriteria = $areRemovableLinkedSheetsCriteria;
    }

    public function handle(Delete $command)
    {
        if (count($this->areRemovableLinkedSheetsCriteria->meetCriteria([$command->linkedSheets])) === 0) {
            throw new DomainException('Linked sheets can\'t be removed');
        }

        $this->linkedSheetsRepository->remove($command->linkedSheets);
    }
}
