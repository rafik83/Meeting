<?php

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;

class ExportQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        TemplateDataFactory $templateDataFactory,
        ProductRepositoryInterface $productRepository,
        HappeningRepositoryInterface $happeningRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->templateDataFactory = $templateDataFactory;
        $this->productRepository = $productRepository;
        $this->happeningRepository = $happeningRepository;
        $this->requestRepository = $requestRepository;
        $this->meetingRepository = $meetingRepository;
    }

    public function handle(ExportQuery $exportQuery): ParticipantListView
    {
        $participants = $this->participantRepository->findByIds($exportQuery->participantIds);

        // preload data to improve performance
        $this->requestRepository->loadParticipantRequestsCount($exportQuery->participantIds);
        $this->meetingRepository->loadParticipantMeetingsCount($exportQuery->participantIds);

        $registrationColumns = [];
        $typesHandled = [];
        $participantViews= [];

        $productColumns = $this->prepareProductColumns($exportQuery->event);
        $dayColumns = $this->prepareDayColumns($exportQuery->event);
        $happeningColumns = $this->prepareHappeningColumns(
            $exportQuery->event,
            $exportQuery->event->getAvailableLocale($exportQuery->locale)
        );

        foreach ($participants as $participant) {
            if (!isset($typesHandled[$participant->getSheet()->getType()->getId()])) {
                $this->prepareRegistrationColumns(
                    $registrationColumns,
                    $participant->getSheet()->getType(),
                    $exportQuery->locale,
                    $exportQuery->event->getLocaleFallback()
                );

                $typesHandled[$participant->getSheet()->getType()->getId()] = true;
            }

            $participantViews[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery(
                    $exportQuery->event,
                    $participant,
                    $exportQuery->event->getAvailableLocale($exportQuery->locale)
                )
            );
        }

        return new ParticipantListView(
            $exportQuery->locale,
            $participantViews,
            $dayColumns,
            $registrationColumns,
            $productColumns,
            $happeningColumns
        );
    }

    private function prepareRegistrationColumns(
        array &$registrationColumns,
        Type $type,
        string $locale,
        string $fallback
    ): void {
        $template = $this->templateDataFactory->createRegistrationFromType($type, $locale);

        foreach ($template->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $key = $registrationObject->getKey();

                if (!isset($registrationColumns[$key])) {
                    $registrationColumns[$key] =  $registrationObject->getExportableFieldname(
                        $locale,
                        $fallback
                    );
                }
            }
        }
    }

    private function prepareProductColumns(Event $event): array
    {
        $columns = [];

        $products = $this->productRepository->findParticipantAndAttributableByEvent($event);

        foreach ($products as $product) {
            $key = $product->isParticipant()
                ? sprintf('participant_%s', $product->getId())
                : sprintf('option_%s', $product->getId())
            ;

            $columns[$key] = $product->getName();
        }

        return $columns;
    }

    private function prepareDayColumns(Event $event): array
    {
        $days = [];

        foreach ($event->getDays() as $day) {
            $days[sprintf('day_%d', $day->getId())] = $day->getBegin()->format('d/m/Y');
        }

        return $days;
    }

    private function prepareHappeningColumns(Event $event, string $locale): array
    {
        $happenings = $this->happeningRepository->findListByEvent($event, $locale);
        $happeningColumns = [];

        foreach ($happenings as $happening) {
            $happeningColumns[sprintf('happening_%d', $happening->getId())] = $happening->getTitle($locale);
        }

        return $happeningColumns;
    }
}
