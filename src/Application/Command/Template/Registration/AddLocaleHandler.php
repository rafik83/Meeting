<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Exception\Template\TemplateException;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class AddLocaleHandler
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * AddLocaleHandler constructor.
     *
     * @param RegistrationTemplateRepositoryInterface $templateRepository
     */
    public function __construct(RegistrationTemplateRepositoryInterface $templateRepository)
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
            throw new TemplateException('Adding locale to an event template is not allowed.');
        }

        if ($command->template->hasLocale($command->locale)) {
            throw new TemplateException(sprintf('This template already has the locale "%s"', $command->locale));
        }

        $this->templateRepository->set($command->template->addLocale($command->locale));
    }
}
