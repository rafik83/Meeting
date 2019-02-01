<?php

namespace Proximum\Vimeet\Domain\ConditionRules;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetSheetIdsByParticipationTypeIds
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function __invoke(array $sheetIds): array
    {
        return array_map(function(Sheet $sheet) {
            return $sheet->getId();
        }, $this->sheetRepository->getByTypes($sheetIds));
    }
}
