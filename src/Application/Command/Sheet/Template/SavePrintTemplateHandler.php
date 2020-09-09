<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SavePrintTemplateHandler
{
    /** @var SheetTemplateRepositoryInterface */
    private $templateRepository;

    public function __construct(SheetTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    public function handle(SavePrintTemplate $save): void
    {
        $save->template->setPrintValue($save->value);
        $this->templateRepository->set($save->template);
    }
}
