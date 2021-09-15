<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;

class DuplicateHandler
{
    /**
     * @var SheetTemplateCloner
     */
    private $sheetTemplateCloner;

    /**
     * DuplicateHandler constructor.
     *
     * @param SheetTemplateCloner $sheetTemplateCloner
     */
    public function __construct(SheetTemplateCloner $sheetTemplateCloner)
    {
        $this->sheetTemplateCloner = $sheetTemplateCloner;
    }

    /**
     * @param Duplicate $duplicate
     *
     * @return DuplicateResult
     */
    public function handle(Duplicate $duplicate)
    {
        $template = $this->sheetTemplateCloner->duplicate($duplicate->template, $duplicate->event, $duplicate->title);

        return new DuplicateResult($template);
    }
}
