<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetSheetsByEventQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(GetSheetsByEventQuery $getSheetsByEventQuery): array
    {
        return $this->sheetRepository->findEnabledByEvent($getSheetsByEventQuery->event);
    }
}
