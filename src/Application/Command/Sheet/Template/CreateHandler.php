<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class CreateHandler
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $templateRepository
     * @param \DateTimeInterface               $dateTime
     */
    public function __construct(SheetTemplateRepositoryInterface $templateRepository, \DateTimeInterface $dateTime)
    {
        $this->templateRepository = $templateRepository;
        $this->dateTime           = $dateTime;
    }

    /**
     * @param Create $create
     *
     * @return CreateResult
     */
    public function handle(Create $create)
    {
        $template = new SheetTemplate($create->title, [], [$create->locale], $create->locale, $this->dateTime);
        $this->templateRepository->add($template);

        return new CreateResult($template);
    }
}
