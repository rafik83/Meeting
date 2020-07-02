<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCompleteness;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;

class SheetCompletenessManager
{
    /** @var SheetCompletenessRepositoryInterface */
    private $sheetCompletenessRepository;

    public function __construct(SheetCompletenessRepositoryInterface $sheetCompletenessRepository)
    {
        $this->sheetCompletenessRepository = $sheetCompletenessRepository;
    }

    public function setCompleteness(Sheet $sheet, string $locale, int $completeness): SheetCompleteness
    {
        $sheetCompleteness = new SheetCompleteness(
            $sheet,
            $locale,
            $completeness
        );
        $this->sheetCompletenessRepository->add($sheetCompleteness);

        return $sheetCompleteness;
    }
}
