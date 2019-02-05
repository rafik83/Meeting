<?php

namespace Proximum\Vimeet\Domain\ConditionRules;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;

class GetSheetIdsByFilters
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    public function __invoke(Event $event, string $locale, array $filters): array
    {
        return $this->sheetSearchAdapter->getSheetIds($event, $filters, $locale);
    }
}
