<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Components\Registration\RegistrationTemplateValidatorTranslated;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateHandler
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var RegistrationTemplateValidatorTranslated
     */
    private $registrationTemplateValidatorTranslated;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param RegistrationTemplateValidatorTranslated $registrationTemplateValidatorTranslated
     * @param DelayedEventDispatcher                  $eventDispatcher
     * @param JobQueueInterface                       $jobQueue
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        RegistrationTemplateValidatorTranslated $registrationTemplateValidatorTranslated,
        DelayedEventDispatcher $eventDispatcher,
        JobQueueInterface $jobQueue
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->registrationTemplateValidatorTranslated = $registrationTemplateValidatorTranslated;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param Update $update
     *
     * @throws RegistrationTemplateException
     */
    public function handle(Update $update)
    {
        $registrationTemplate = $update->registrationTemplate;

        $registrationTemplate->setTitle($update->title);
        $registrationTemplate->setValue($update->value);

        $templateData = $this->templateDataFactory->createRegistrationFromTemplate(
            $registrationTemplate,
            $registrationTemplate->getFallback()
        );
        $this->registrationTemplateValidatorTranslated->validate($templateData);

        $this->registrationTemplateRepository->set($registrationTemplate);

        $this->jobQueue->indexSheetsByRegistrationTemplate($registrationTemplate);

        if (null !== $update->registrationTemplate->getEvent()) {
            $this->eventDispatcher->dispatch(
                Events::REGISTRATION_TEMPLATE_UPDATED,
                new RegistrationTemplateUpdatedEvent($registrationTemplate->getEvent())
            );
        }
    }
}
