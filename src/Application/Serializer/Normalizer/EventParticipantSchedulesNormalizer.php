<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Nomenclature\Charset;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AbstractTimeEntityView;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantSchedulesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use IntlDateFormatter;

class EventParticipantSchedulesNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_PARTICIPANT_ID      = 'participantId';
    const COL_TITLE               = 'title';
    const COL_FIRSTNAME           = 'firstName';
    const COL_LASTNAME            = 'lastName';
    const COL_COMPANY             = 'companyName';
    const COL_PARTICIPATION_TYPE  = 'type';
    const COL_DESCRIPTION         = 'description';
    const COL_POSITION            = 'position';
    const COL_PHONE_PREFIX        = 'phonePrefix';
    const COL_PHONE_NUMBER        = 'phoneNumber';
    const COL_EMAIL               = 'email';
    const COL_MOBILE_PHONE_PREFIX = 'mobilePhonePrefix';
    const COL_MOBILE_PHONE        = 'mobilePhone';
    const COL_SCHEDULE            = 'schedule';

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var AgendaViewQueryHandler
     */
    private $agendaViewQueryHandler;

    /**
     * @param TranslatorInterface            $translator
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param SheetInfoGuesser               $sheetInfoGuesser
     * @param RequestRepositoryInterface     $requestRepository
     * @param AgendaViewQueryHandler         $agendaViewQueryHandler
     */
    public function __construct(
        TranslatorInterface $translator,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        AgendaViewQueryHandler $agendaViewQueryHandler
    ) {
        parent::__construct($translator);

        $this->participantRepository  = $participantRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->requestRepository      = $requestRepository;
        $this->agendaViewQueryHandler = $agendaViewQueryHandler;
    }

    /**
     * Normalizes participants' schedules for a given event for serialization.
     *
     * {@inheritdoc}
     *
     * @param EventParticipantSchedulesNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawParticipants = [];
        $locale          = $context['locale'];

        foreach ($this->participantRepository->getParticipantsByEvent($object->event, $locale) as $participant) {
            $rawParticipants[] = $this->getParticipantRawData($participant, $object->user, $locale);
        }

        $charset                = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;
        $normalizedParticipants = [];

        foreach ($rawParticipants as $rawParticipant) {
            $normalizedParticipants[] = $this->normalizeParticipantRawData($rawParticipant, $charset);
        }

        return $normalizedParticipants;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'csv' === $format && $data instanceof EventParticipantSchedulesNormalizerView;
    }

    /**
     * @param Participant $participant
     * @param Admin       $user
     * @param string      $locale
     *
     * @return array Raw data about participant's schedule
     */
    private function getParticipantRawData(Participant $participant, Admin $user, $locale)
    {
        $sheet           = $participant->getSheet();
        $event           = $sheet->getEvent();
        $participantLocale = $event->getAvailableLocale($participant->getUser()->getLocale());
        $adminLocale = $event->getAvailableLocale($user->getLocale());

        $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $locale);

        $gender = isset($participantInfo[Tag::PARTICIPANT_GENDER]) ? $participantInfo[Tag::PARTICIPANT_GENDER] : null;
        if ($gender) {
            $gender = $this->translator->trans(sprintf('gender.%s', $gender));
        }

        $agenda = $this->agendaViewQueryHandler->handle(new AgendaViewQuery(
            $event,
            $sheet,
            $participant,
            $participantLocale
        ));

        $planning = $this->formatPlanning($agenda->days, $participantLocale);

        $planning .= $this->formatUnallocated(
            $sheet,
            $this->requestRepository->getUnassignedRequestsBySheetAndEvent($sheet, Request::STATE_APPROVED),
            $participantLocale
        );

        return [
            self::COL_PARTICIPANT_ID      => sprintf("%s-%s", $sheet->getId(), $participant->getId()),
            self::COL_TITLE               => $gender,
            self::COL_FIRSTNAME           => isset($participantInfo[Tag::PARTICIPANT_FIRSTNAME]) ? $participantInfo[Tag::PARTICIPANT_FIRSTNAME] : null,
            self::COL_LASTNAME            => isset($participantInfo[Tag::PARTICIPANT_LASTNAME]) ? $participantInfo[Tag::PARTICIPANT_LASTNAME] : null,
            self::COL_COMPANY             => $this->sheetInfoGuesser->guessSheetTitle($sheet, $adminLocale),
            self::COL_PARTICIPATION_TYPE  => $sheet->getType()->getTitle($adminLocale),
            self::COL_DESCRIPTION         => null,
            self::COL_POSITION            => isset($participantInfo[Tag::PARTICIPANT_POSITION]) ? $participantInfo[Tag::PARTICIPANT_POSITION] : null,
            self::COL_PHONE_PREFIX        => null,
            self::COL_PHONE_NUMBER        => isset($participantInfo[Tag::PARTICIPANT_PHONE]) ? $participantInfo[Tag::PARTICIPANT_PHONE] : null,
            self::COL_EMAIL               => $participant->getUser()->getEmail(),
            self::COL_MOBILE_PHONE_PREFIX => null,
            self::COL_MOBILE_PHONE        => isset($participantInfo[Tag::PARTICIPANT_MOBILE]) ? $participantInfo[Tag::PARTICIPANT_MOBILE] : null,
            self::COL_SCHEDULE            => $planning,
        ];
    }

    /**
     * Returns an array of normalized data from a participant's schedule raw data
     * (normalizing includes charset encoding, string substitution for boolean values, etc.)
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeParticipantRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach ($rawData as $fieldKey => $input) {
            $normalizedData[$fieldKey] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        return $normalizedData;
    }

    /**
     * @param DayView[] $days
     * @param string    $locale
     *
     * @return string
     */
    private function formatPlanning(array $days, $locale)
    {
        $formatted = $this->translator->trans(
            'admin.participant.export.fields.planning.warning',
            [],
            'messages',
            $locale
        ) . PHP_EOL;

        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE
        );

        foreach ($days as $day) {
            $timeEntities = $this->sortChronologicalOrder($day->getTimeEntities());

            $formatted .= '**' . ucfirst($formatter->format($day->getDay())) . '**' . PHP_EOL;
            $formatted .= $this->formatTimeEntities($timeEntities, $locale). PHP_EOL . PHP_EOL;
        }

        return $formatted;
    }

    /**
     * @param Sheet     $sheet
     * @param Request[] $requests
     * @param string    $locale
     *
     * @return string
     */
    private function formatUnallocated(Sheet $sheet, array $requests, $locale)
    {
        $translation = $this->translator->trans(
            'admin.participant.export.fields.planning.unallocated_meetings',
            [],
            'messages',
            $locale
        );

        $formatted = (count($requests) > 0) ? $translation . PHP_EOL : '';

        foreach ($requests as $request) {
            $formatted .= $this->sheetInfoGuesser->guessSheetTitle($request->getSheetMet($sheet)) . ', ';
        }

        // Remove last comma and space
        return !empty($formatted) ? substr($formatted, 0, -2) : $formatted;
    }

    /**
     * @param AbstractTimeEntityView[] $timeEntities
     * @param string                   $locale
     *
     * @return string
     */
    private function formatTimeEntities(array $timeEntities, $locale)
    {
        $formatted = '';
        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT
        );
        $formatter->setPattern('hh:mm');

        foreach ($timeEntities as $timeEntity) {
            $formatted .= $formatter->format($timeEntity->begin) . ' - ' . $formatter->format($timeEntity->end) . ' : ';
            if ($timeEntity instanceof MeetingView) {
                $formatted .= $timeEntity->spotRef . ' ' . $timeEntity->sheetMetTitle;
            }
            elseif ($timeEntity instanceof MassUnavailabilityView) {
                $formatted .= $timeEntity->title;
            }
            elseif ($timeEntity instanceof HappeningView) {
                $formatted .= $timeEntity->title;
            }
            elseif ($timeEntity instanceof UnavailabilityView) {
                $formatted .= $this
                    ->translator
                    ->trans(
                        'admin.participant.export.fields.planning.unavailability',
                        [],
                        'messages',
                        $locale
                    )
                ;
            }
            $formatted .= PHP_EOL;
        }

        return $formatted;
    }

    /**
     * @param array $timeEntities
     *
     * @return array
     */
    private function sortChronologicalOrder(array $timeEntities)
    {
        usort($timeEntities, function(AbstractTimeEntityView $first, AbstractTimeEntityView $second) {
            return $first->begin > $second->begin;
        });

        return $timeEntities;
    }
}
