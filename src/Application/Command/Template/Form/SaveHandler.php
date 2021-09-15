<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Components\Template\Form\FormTemplateValidatorTranslated;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Form\FormTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var FormTemplateValidatorTranslated */
    private $templateValidatorTranslated;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        FormTemplateRepositoryInterface $formTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        FormTemplateValidatorTranslated $templateValidatorTranslated,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->formTemplateRepository = $formTemplateRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->templateValidatorTranslated = $templateValidatorTranslated;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Save $save
     *
     * @throws TemplateException
     */
    public function handle(Save $save): void
    {
        $save->template->setValue($save->value);

        try {
            $templateData = $this->templateDataFactory->createFormTemplateFromTemplate(
                $save->template,
                $save->template->getFallback()
            );

            $this->templateValidatorTranslated->validate($templateData);
        } catch (\Exception $exception) {
            throw new TemplateException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->formTemplateRepository->update($save->template);

        $this->delayedEventDispatcher->dispatch(
            Events::FORM_TEMPLATE_UPDATED,
            new FormTemplateUpdatedEvent($save->template->getEvent())
        );
    }
}
