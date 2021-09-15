<?php

namespace Proximum\Vimeet\Domain\View\Sheet;

use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class PdfPrintView
{
    /** @var Sheet */
    public $sheet;

    /** @var Nomenclature[] */
    public $nomenclatures;

    /** @var CardListView[] */
    public $participants;

    /** @var array */
    public $taggedData;

    /**
     * @param Sheet          $sheet
     * @param Nomenclature[] $nomenclatures
     * @param CardListView[] $participants
     * @param array          $taggedData
     */
    public function __construct(Sheet $sheet, array $nomenclatures, array $participants, array $taggedData)
    {
        $this->sheet         = $sheet;
        $this->nomenclatures = $nomenclatures;
        $this->participants  = $participants;
        $this->taggedData    = $taggedData;
    }
}
