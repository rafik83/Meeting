<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
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

class HappeningParticipantViewQueryHandler
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
     * @param HappeningParticipantViewQuery $query
     *
     * @return HappeningParticipantListView
     * @throws EmptyHappeningParticipationException
     */
    public function handle(HappeningParticipantViewQuery $query)
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

        if (count($happeningParticipantViews) === 0) {
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
        $question = null;

        try {
            $sheetName = $this->groupNameResolver->resolve($event, $user);
            $sheet     = $this->sheetGuesser->getUserSheet($user, $event, $locale);
        } catch (\Exception $exception) {
            $sheetName = '';
            $sheet     = null;
        }

        if ($sheet !== null) {
            $question = $this->questionRepository->findByHappeningAndSheet($happening, $sheet);
        }

        $timezone = $event->getTimeZone();

        $happeningParticipantView = new HappeningParticipantView(
            $happening->getId(),
            HappeningDateHelper::getHour($happening->getBegin(), $locale, $timezone),
            HappeningDateHelper::getHour($happening->getEnd(), $locale, $timezone),
            HappeningDateHelper::getDay($happening->getBegin(), $locale, $timezone),
            $happening->getTitle($locale),
            $sheet !== null ? $sheet->getId() : '',
            $user->getId(),
            null !== $question ? $question->getContent() : '',
            $user->getEmail(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getPosition(),
            $sheetName
        );

        return $happeningParticipantView;
    }
}
