<?php

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class ParticipantViewQueryHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var HasRemainingToPay */
    private $hasRemainingToPay;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var array */
    private $analytics;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        TemplateDataFactory $templateDataFactory,
        HasRemainingToPay $hasRemainingToPay,
        TranslatorInterface $translator,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        ScanRepositoryInterface $scanRepository,
        HappeningRepositoryInterface $happeningRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->hasRemainingToPay = $hasRemainingToPay;
        $this->translator = $translator;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->scanRepository = $scanRepository;
        $this->happeningRepository = $happeningRepository;
        $this->sheetRepository = $sheetRepository;
        $this->requestRepository = $requestRepository;
        $this->meetingRepository = $meetingRepository;
    }

    public function handle(ParticipantViewQuery $query): ParticipantView
    {
        if (null === $this->analytics) {
            $this->analytics = $this->sheetRepository->getAnalyticsByUser($query->event);
        }

        $registrationData = $this->getRegistrationData($query->participant, $query->locale);

        $participantProductId = null !== $query->participant->getParticipantProduct()
            ? $query->participant->getParticipantProduct()->getId()
            : null
        ;

        $attributableProducts = $this->prepareAttributableProducts($query->participant);
        $daysChecking = $this->prepareDaysChecking($query->participant, $query->event);
        $happeningChecking = $this->prepareHappeningChecking(
            $query->participant,
            $query->event,
            $query->event->getAvailableLocale($query->locale)
        );

        $view = new ParticipantView(
            $query->participant->getSheet()->getId(),
            $query->participant->getSheet()->getType()->getTitle($query->locale),
            $query->participant->getSheet()->getTitle(),
            $query->participant->getSheet()->isEnabled(),
            $query->participant->getUser()->getId(),
            $query->participant->getId(),
            $query->participant->getEmail(),
            $query->participant->getSheet()->getCreatedAt()->format('Y-m-d'),
            $this->happeningParticipationRepository->hasParticipationForUserAndEvent(
                $query->participant->getUser(),
                $query->event
            ),
            !$this->hasRemainingToPay->isSatisfiedBy($query->participant->getSheet()),
            $participantProductId,
            $daysChecking,
            $attributableProducts,
            $registrationData,
            $happeningChecking,
            $this->analytics[$query->participant->getUser()->getId()]['viewedSheets']??0,
            $this->analytics[$query->participant->getUser()->getId()]['clickedElements']??0,
            $this->requestRepository->getParticipantRequestsCount($query->participant),
            $this->meetingRepository->getParticipantMeetingsCount($query->participant)
        );

        return $view;
    }

    private function prepareAttributableProducts(Participant $participant): array
    {
        $attributableProducts = $this->productAttributedToParticipantRepository->findByParticipant($participant);

        $attributableProductsIds = [];

        foreach ($attributableProducts as $attributableProduct) {
            $productId = $attributableProduct->getProduct()->getId();
            $attributableProductsIds[sprintf('option_%s', $productId)] = $productId;
        }

        return $attributableProductsIds;
    }

    private function getRegistrationData(Participant $participant, string $locale): array
    {
        $registrationData = [];
        $template = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        foreach ($template->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $fieldContent = $registrationObject->getExportableContent([], $locale);

                if ($registrationObject instanceof Gender && !empty($fieldContent)) {
                    $fieldContent = $this->translator->trans(sprintf('gender.%s', $fieldContent), [], null, $locale);
                }

                if ($registrationObject instanceof BooleanObject) {
                    $fieldContent = $this->translator->trans(
                        sprintf('boolean.%s', $fieldContent ? 'yes' : 'no'),
                        [],
                        null,
                        $locale
                    );
                }

                $registrationData[$registrationObject->getKey()] = $fieldContent;
            }
        }

        return $registrationData;
    }

    private function prepareDaysChecking(Participant $participant, Event $event): array
    {
        $days = [];

        foreach ($event->getDays() as $day) {
            $check = $this->scanRepository->getUserFirstCheckinTodayByEvent($participant->getUser(), $event, $day->getBegin());

            $days[sprintf('day_%d', $day->getId())] = $check !== null ? $this->getDateFormattedByTimezone($check->getScannedAt(), $event->getTimeZone()) : null;
        }

        return $days;
    }

    private function prepareHappeningChecking(Participant $participant, Event $event, string $locale): array
    {
        $happenings = $this->happeningRepository->findListByEvent($event, $locale);
        $happeningColumns = [];

        foreach ($happenings as $happening) {
            $result = null;

            $scan = $this->scanRepository->getScanForUserEventTypeAndObjectId(
                $participant->getUser(),
                $event,
                Type::TYPE_HAPPENING_ENTRANCE,
                $happening->getId()
            );

            if ($scan instanceof Scan) {
                $result = $this->getDateFormattedByTimezone($scan->getScannedAt(), $event->getTimeZone());
            } else {
                $hasHappening = $this->happeningParticipationRepository->findByHappeningAndUser(
                    $happening,
                    $participant->getUser()
                ) instanceof HappeningParticipation;

                $result = true === $hasHappening ? $this->translator->trans('admin.participant.export.fields.happening.participate', [], null, $locale) : null;
            }

            $happeningColumns[sprintf('happening_%d', $happening->getId())] = $result;
        }

        return $happeningColumns;
    }

    private function getDateFormattedByTimezone(\DateTimeInterface $dateTime, string $timezone): string
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTimezone(new \DateTimeZone($timezone))
            ->format('d/m/Y H:i');
    }
}
