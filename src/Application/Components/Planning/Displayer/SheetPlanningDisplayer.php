<?php

namespace Proximum\Vimeet\Application\Components\Planning\Displayer;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\SheetPlanningFormatter;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPlanningDisplayer
{
    /**
     * @var SheetPlanningFormatter
     */
    private $sheetPlanningFormatter;

    /**
     * @var MarkdownAdapterInterface
     */
    private $markdown;

    /**
     * @param SheetPlanningFormatter   $sheetPlanningFormatter
     * @param MarkdownAdapterInterface $markdown
     */
    public function __construct(SheetPlanningFormatter $sheetPlanningFormatter, MarkdownAdapterInterface $markdown)
    {
        $this->sheetPlanningFormatter = $sheetPlanningFormatter;
        $this->markdown               = $markdown;
    }

    /**
     * @param Sheet            $sheet
     * @param string           $locale
     * @param Participant|null $currentParticipant
     *
     * @return string
     */
    public function display(Sheet $sheet, $locale, Participant $currentParticipant = null)
    {
        $planningMarkdown = $this->sheetPlanningFormatter->formatWithUnallocated($sheet, $locale, $currentParticipant);

        return $this->markdown->toHtml($planningMarkdown);
    }
}
