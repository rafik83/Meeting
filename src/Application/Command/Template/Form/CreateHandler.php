<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class CreateHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        FormTemplateRepositoryInterface $formTemplateRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->formTemplateRepository = $formTemplateRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Create $command): FormTemplate
    {
        $template = new FormTemplate(
            $command->event,
            $command->title,
            [],
            $command->event->getLocales(),
            $command->event->getFallback(),
            $this->dateTime
        );
        $template->translateTitles($command->translations);

        $this->formTemplateRepository->add($template);

        return $template;
    }
}
