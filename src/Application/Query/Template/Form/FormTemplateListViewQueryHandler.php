<?php

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\View\Template\Form\FormTemplateListView;
use Proximum\Vimeet\Application\View\Template\Form\FormTemplateView;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class FormTemplateListViewQueryHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        FormTemplateRepositoryInterface $formTemplateRepository,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->formTemplateRepository = $formTemplateRepository;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function handle(FormTemplateListViewQuery $query): FormTemplateListView
    {
        $formTemplates = $this->formTemplateRepository->findByEvent($query->event);
        $formTemplateViews = [];

        foreach ($formTemplates as $formTemplate) {
            $translatedTitles = [];
            foreach ($query->event->getLocales() as $locale) {
                $translatedTitles[$locale] = $formTemplate->getLocalizedTitle($locale);
            }

            $formTemplateViews[] = new FormTemplateView(
                $formTemplate->getId(),
                $formTemplate->getTitle(),
                $formTemplate->isPublished(),
                $translatedTitles,
                $formTemplate->getTypes(),
                $this->eventUrlGenerator->generateEventAbsoluteUrl(
                    $query->event,
                    'event_show_form_template',
                    ['formTemplate' => $formTemplate->getId()]
                ),
                $formTemplate->getFallback(),
                $formTemplate->getCreatedAt()
            );
        }

        return new FormTemplateListView($formTemplateViews);
    }
}
