<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class CreateHandler
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Create $create): CreateResult
    {
        $template = new RegistrationTemplate(
            $create->title,
            [],
            $create->event->getLocales(),
            $create->event->getFallback(),
            $this->dateTime,
            $create->event
        );

        $this->registrationTemplateRepository->add($template);

        return new CreateResult($template);
    }
}
