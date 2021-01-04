<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Template\Registration;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Repository\Filter\BooleanTemplateFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Filter\FilledTemplateFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateBooleanFilterIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateFilledFilter;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationTemplateUpdatedEventSubscriber implements EventSubscriberInterface
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var BooleanTemplateFilterRepositoryInterface */
    private $booleanTemplateFilterRepository;

    /** @var FilledTemplateFilterRepositoryInterface */
    private $filledTemplateFilterRepository;

    /** @var TaggedNomenclatureFilterRepositoryInterface */
    private $taggedNomenclatureFilterRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository,
        FilledTemplateFilterRepositoryInterface $filledTemplateFilterRepository,
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->booleanTemplateFilterRepository = $booleanTemplateFilterRepository;
        $this->filledTemplateFilterRepository  = $filledTemplateFilterRepository;
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param RegistrationTemplateUpdatedEvent $event
     */
    public function onRegistrationTemplateUpdated(RegistrationTemplateUpdatedEvent $event)
    {
        $templates     = $this->registrationTemplateRepository->getUsedTemplateForGivenEvent($event->getEvent());
        $templatesData = [];

        foreach ($templates as $template) {
            $templatesData[] = $this->templateDataFactory->createFromTemplate($template);
        }

        $this->generateBooleanFilter($event->getEvent(), $templatesData);
        $this->generateFilledFilter($event->getEvent(), $templatesData);

        $tags = array_merge([Tag::PARTICIPANT_POSITION], Tag::getSheetAndGenericTags());

        $this->taggedNomenclatureFilterRepository->deleteForEventAndTags($event->getEvent(), $tags);

        // The TaggedNomenclatureFilter can be used on any Sheet tags or the participant position tag
        foreach ($tags as $tag) {
            $this->saveNomenclaturesForGivenTag($event->getEvent(), $templatesData, $tag);
        }
    }

    /**
     * @param Event          $event
     * @param TemplateData[] $templatesData
     */
    private function generateBooleanFilter(Event $event, array &$templatesData)
    {
        $this->booleanTemplateFilterRepository->deleteForEvent($event);

        $filters = TemplateBooleanFilterIdentifier::getBooleanFilters($templatesData);
        $filtersAdded = [];

        foreach ($filters as $filter) {
            // Avoid duplicate filter
            if (!isset($filtersAdded[$filter['key']])) {
                $booleanFilter = new BooleanTemplateFilter(
                    $event,
                    $filter['key'],
                    $filter['value'],
                    $this->getInformationType($filter)
                );

                $this->booleanTemplateFilterRepository->add($booleanFilter);
                $filtersAdded[$booleanFilter->getTemplateKey()] = true;
            }
        }
    }

    private function generateFilledFilter(Event $event, array &$templatesData): void
    {
        $this->filledTemplateFilterRepository->deleteForEvent($event);
        $filters = TemplateFilledFilter::getFilledFilters($templatesData);
        $filtersAdded = [];

        foreach ($filters as $filter) {
            if (!isset($filtersAdded[$filter['key']])) {
                $filledFilter = new FilledTemplateFilter(
                    $event,
                    $filter['key'],
                    $filter['value'],
                    $this->getInformationType($filter)
                );

                $this->filledTemplateFilterRepository->add($filledFilter);
                $filtersAdded[$filledFilter->getTemplateKey()] = true;
            }
        }
    }

    private function getInformationType(array &$filter): ?string
    {
        $informationType = null;

        if (\in_array(Tag::SHEET_DATA, $filter['tags'], true)) {
            $informationType = Tag::SHEET_DATA;
        } elseif (\in_array(Tag::PARTICIPANT_DATA, $filter['tags'], true)) {
            $informationType = Tag::PARTICIPANT_DATA;
        }

        return $informationType;
    }

    /**
     * @param Event          $event
     * @param TemplateData[] $templatesData
     * @param string         $tag
     */
    private function saveNomenclaturesForGivenTag(Event $event, array &$templatesData, $tag): void
    {
        $nomenclaturesAdded = [];

        foreach ($templatesData as $templateData) {
            foreach ($templateData->getObjects() as $templateObject) {
                if ($templateObject instanceof Nomenclature
                    && $templateObject->hasTag($tag)
                ) {
                    $nomenclatureId = $templateObject->getNomenclatureId();

                    if (!isset($nomenclaturesAdded[$nomenclatureId])) {
                        $nomenclaturesAdded[$nomenclatureId] = $nomenclatureId;
                    }
                }
            }
        }

        if (\count($nomenclaturesAdded)) {
            $this->taggedNomenclatureFilterRepository->add(
                new TaggedNomenclatureFilter($event, $tag, $nomenclaturesAdded)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REGISTRATION_TEMPLATE_UPDATED => 'onRegistrationTemplateUpdated',
        ];
    }
}
