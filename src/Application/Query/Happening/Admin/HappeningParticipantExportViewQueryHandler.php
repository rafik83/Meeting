<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantView;
use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;

class HappeningParticipantExportViewQueryHandler
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var QuestionRepositoryInterface
     */
    private $questionRepository;

    /**
     * @var GroupNameResolver
     */
    private $groupNameResolver;
    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    /**
     * HappeningParticipantViewQueryHandler constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     * @param QuestionRepositoryInterface  $questionRepository
     * @param GroupNameResolver            $groupNameResolver
     * @param SheetGuesser                 $sheetGuesser
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        QuestionRepositoryInterface $questionRepository,
        GroupNameResolver $groupNameResolver,
        SheetGuesser $sheetGuesser
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->questionRepository  = $questionRepository;
        $this->groupNameResolver   = $groupNameResolver;
        $this->sheetGuesser        = $sheetGuesser;
    }

    /**
     * @param HappeningParticipantExportViewQuery $query
     *
     * @throws EmptyHappeningParticipationException
     *
     * @return HappeningParticipantListView
     */
    public function handle(HappeningParticipantExportViewQuery $query)
    {
        // preload happenings with participations and questions
        $happenings = $this->happeningRepository->findHappeningParticipant($query->event);

        $happeningParticipantViews = [];

        foreach ($happenings as $happening) {
            $participations = $happening->getParticipations();

            foreach ($participations as $participation) {
                if (!$participation->isDisabled()) {
                    $happeningParticipantViews[] = $this->buildView(
                        $happening,
                        $participation->getUser(),
                        $query->event,
                        $query->locale
                    );
                }
            }
        }

        if (0 === count($happeningParticipantViews)) {
            throw new EmptyHappeningParticipationException();
        }

        return new HappeningParticipantListView($happeningParticipantViews);
    }

    /**
     * @param Happening $happening
     * @param User      $user
     * @param Event     $event
     * @param string    $locale
     *
     * @return HappeningParticipantView
     */
    public function buildView(Happening $happening, User $user, Event $event, $locale)
    {
        try {
            $sheetName = $this->groupNameResolver->resolve($event, $user);
        } catch (\Exception $exception) {
            $sheetName = '';
        }

        try {
            $sheet = $this->sheetGuesser->getUserSheet($user, $event, $locale);
        } catch (\Exception $exception) {
            $sheet = null;
        }

        $questions = null !== $sheet ? $this->questionRepository->findByHappeningAndSheet($happening, $sheet) : [];

        $timezone = $event->getTimeZone();

        $happeningParticipantView = new HappeningParticipantView(
            $happening->getId(),
            HappeningDateHelper::getHour($happening->getBegin(), $locale, $timezone),
            HappeningDateHelper::getHour($happening->getEnd(), $locale, $timezone),
            HappeningDateHelper::getDay($happening->getBegin(), $locale, $timezone),
            $happening->getTitle($locale),
            null !== $sheet ? $sheet->getId() : '',
            $user->getId(),
            implode("\n", array_map(static function (Happening\Question $question) {
                return $question->getContent();
            }, $questions)),
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPosition(),
            $sheetName
        );

        return $happeningParticipantView;
    }
}
