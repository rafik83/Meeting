<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantView;
use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class HappeningParticipantViewQueryHandler
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var QuestionRepositoryInterface
     */
    private $questionRepository;

    /**
     * HappeningParticipantViewQueryHandler constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     * @param QuestionRepositoryInterface  $questionRepository
     * @param ParticipantInfoGuesser       $participantInfoGuesser
     * @param SheetInfoGuesser             $sheetInfoGuesser
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        QuestionRepositoryInterface $questionRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->happeningRepository    = $happeningRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->questionRepository     = $questionRepository;
    }

    /**
     * @param HappeningParticipantViewQuery $query
     *
     * @return HappeningParticipantListView
     * @throws EmptyHappeningParticipationException
     */
    public function handle(HappeningParticipantViewQuery $query)
    {
        $happenings = $this->happeningRepository->findByEvent($query->event);

        $happeningParticipantViews = [];

        foreach ($happenings as $happening) {
            $participations = $happening->getParticipations();

            foreach ($participations as $participation) {
                $happeningParticipantViews[] = $this->buildView(
                    $happening,
                    $participation->getParticipant(),
                    $query->locale
                );
            }
        }

        if (count($happeningParticipantViews) === 0) {
            throw new EmptyHappeningParticipationException();
        }

        return new HappeningParticipantListView($happeningParticipantViews);
    }

    /**
     * @param Happening   $happening
     * @param Participant $participant
     * @param string      $locale
     *
     * @return HappeningParticipantView
     */
    public function buildView(Happening $happening, Participant $participant, $locale)
    {
        $infos = $this->participantInfoGuesser->guessParticipantInfos($participant, $locale);

        $firstname = $infos[Tag::PARTICIPANT_FIRSTNAME];
        $lastname  = $infos[Tag::PARTICIPANT_LASTNAME];
        $position  = $infos[Tag::PARTICIPANT_POSITION];
        $sheetName = $this->sheetInfoGuesser->guessSheetTitle($participant->getSheet(), $locale);

        $question = $this->questionRepository->findByHappeningAndSheet($happening, $participant->getSheet());
        $timezone = $participant->getSheet()->getEvent()->getTimeZone();

        $happeningParticipantView = new HappeningParticipantView(
            $happening->getId(),
            HappeningDateHelper::getHour($happening->getBegin(), $locale, $timezone),
            HappeningDateHelper::getHour($happening->getEnd(), $locale, $timezone),
            HappeningDateHelper::getDay($happening->getBegin(), $locale, $timezone),
            $happening->getTitle($locale),
            $participant->getSheet()->getId(),
            $participant->getId(),
            $question !== null ? $question->getContent() : '',
            $participant->getUser()->getEmail(),
            $firstname,
            $lastname,
            $position,
            $sheetName
        );

        return $happeningParticipantView;
    }
}
