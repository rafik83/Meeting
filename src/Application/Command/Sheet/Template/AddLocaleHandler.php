<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\TemplateException;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class AddLocaleHandler
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * AddLocaleHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $templateRepository
     */
    public function __construct(SheetTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param AddLocale $command
     *
     * @throws TemplateException
     */
    public function handle(AddLocale $command)
    {
        if ($command->template->getEvent()) {
            throw new TemplateException('Adding locale to a event template is not allowed.');
        }

        if ($command->template->hasLocale($command->locale)) {
            throw new TemplateException(sprintf('This template already has the locale "%s"', $command->locale));
        }

        $this->templateRepository->set($command->template->addLocale($command->locale));
    }
}
