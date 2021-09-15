<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Components\Registration\RegistrationTemplateValidatorTranslated;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandler
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var RegistrationTemplateValidatorTranslated */
    private $registrationTemplateValidatorTranslated;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        JobQueueInterface $jobQueue,
        TemplateDataFactory $templateDataFactory,
        RegistrationTemplateValidatorTranslated $registrationTemplateValidatorTranslated,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->jobQueue = $jobQueue;
        $this->templateDataFactory = $templateDataFactory;
        $this->registrationTemplateValidatorTranslated = $registrationTemplateValidatorTranslated;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Save $save
     *
     * @throws RegistrationTemplateException
     */
    public function handle(Save $save)
    {
        $save->registrationTemplate->setValue($save->value);

        try {
            $templateData = $this->templateDataFactory->createRegistrationFromTemplate(
                $save->registrationTemplate,
                $save->registrationTemplate->getFallback()
            );

            $this->registrationTemplateValidatorTranslated->validate($templateData);
        } catch (\Exception $exception) {
            throw new RegistrationTemplateException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->registrationTemplateRepository->set($save->registrationTemplate);
        $this->jobQueue->indexSheetsByRegistrationTemplate($save->registrationTemplate);

        if (null !== $save->registrationTemplate->getEvent()) {
            $this->delayedEventDispatcher->dispatch(
                Events::REGISTRATION_TEMPLATE_UPDATED,
                new RegistrationTemplateUpdatedEvent($save->registrationTemplate->getEvent())
            );
        }
    }
}
