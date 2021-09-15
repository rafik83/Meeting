<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class CreateForEventHandler
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
     * OrganizerCreateHandler constructor.
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
     * @param CreateForEvent $create
     *
     * @return CreateResult
     */
    public function handle(CreateForEvent $create)
    {
        $template = new SheetTemplate(
            $create->title,
            [],
            $create->event->getLocales(),
            $create->event->getFallback(),
            $this->dateTime
        );

        $template->setEvent($create->event);

        $this->templateRepository->add($template);

        return new CreateResult($template);
    }
}
