<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Command\Event\Find\FindResult as EventFindResult;
use Proximum\Vimeet\Application\Exception\Invoice\InvoiceNotFoundException;
use Proximum\Vimeet\Domain\Model\Sheet;

class FindResult extends EventFindResult
{
    /**
     * @var Sheet[]
     */
    public $sheets;

    /**
     * @param array $sheets
     *
     * @throws InvoiceNotFoundException
     */
    public function __construct(array $sheets)
    {
        $firstSheet = reset($sheets);

        if (false === $firstSheet) {
            throw new InvoiceNotFoundException();
        }

        parent::__construct($firstSheet);

        $this->sheets = $sheets;
    }

    /**
     * @return bool
     */
    public function hasOnlyOneSheet()
    {
        return 1 === count($this->sheets);
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets;
    }
}
