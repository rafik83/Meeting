<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Api\Leni;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Api\Leni\LeniPlanningDayView;
use Proximum\Vimeet\Application\View\Api\Leni\LeniPlanningView;
use Proximum\Vimeet\Application\View\Api\Leni\LeniUserView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class LeniUserViewQueryHandler
{
    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param ParticipantInfoGuesser       $participantInfoGuesser
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param TypeNameResolver             $typeNameResolver
     * @param GroupNameResolver            $groupNameResolver
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        TypeNameResolver $typeNameResolver,
        GroupNameResolver $groupNameResolver,
        TranslatorInterface $translator
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->typeNameResolver = $typeNameResolver;
        $this->groupNameResolver = $groupNameResolver;
        $this->translator = $translator;
    }

    /**
     * @param LeniUserViewQuery $query
     *
     * @return LeniUserView
     */
    public function handle(LeniUserViewQuery $query): LeniUserView
    {
        $userLocale = $query->event->getAvailableLocale($query->user);

        $planning = $this->participantPlanningFormatter->formatPlanningByDayFromUserAndEventWithUnallocated(
            $query->user,
            $query->event,
            $userLocale
        );

        $userInfo = $this->getUserInfo($query->user, $userLocale, $query->sheets);

        $days = [];
        foreach ($planning->days as $day) {
            $days[] = new LeniPlanningDayView($day);
        }

        $leniPlanning = new LeniPlanningView($days, $planning->unallocated);

        return new LeniUserView(
            $query->user->getId(),
            $this->groupNameResolver->resolve($query->event, $query->user, $query->sheets),
            $this->typeNameResolver->resolveWithPreloadedSheets($query->sheets, $query->event->getFallback()),
            $query->user->getEmail(),
            $userInfo['gender'],
            $userInfo['firstName'],
            $userInfo['lastName'],
            $userInfo['position'],
            $userInfo['phone'],
            $userInfo['mobile'],
            $leniPlanning
        );
    }


    /**
     * @param User    $user
     * @param string  $locale
     * @param Sheet[] $userSheets
     *
     * @return array
     */
    private function getUserInfo(User $user, string $locale, array $userSheets): array
    {
        $userInfo = [
            'gender'    => '',
            'firstName' => '',
            'lastName'  => '',
            'position'  => '',
            'phone'     => '',
            'mobile'    => '',
        ];


        if (!empty($userSheets)) {
            $participant = null;

            foreach ($userSheets as $sheet) {
                $participant = $sheet->getUserParticipant($user);

                if ($participant !== null) {
                    break;
                }
            }

            if ($participant !== null) {
                $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $locale);

                if (!empty($participantInfo[Tag::PARTICIPANT_GENDER])) {
                    $userInfo['gender'] = $this->translator->trans(
                        sprintf('gender.%s', $participantInfo[Tag::PARTICIPANT_GENDER])
                    );
                }

                if (!empty($participantInfo[Tag::PARTICIPANT_FIRSTNAME])) {
                    $userInfo['firstName'] = $participantInfo[Tag::PARTICIPANT_FIRSTNAME];
                }

                if (!empty($participantInfo[Tag::PARTICIPANT_LASTNAME])) {
                    $userInfo['lastName'] = $participantInfo[Tag::PARTICIPANT_LASTNAME];
                }

                if (!empty($participantInfo[Tag::PARTICIPANT_POSITION])) {
                    $userInfo['position'] = $participantInfo[Tag::PARTICIPANT_POSITION];
                }

                if (!empty($participantInfo[Tag::PARTICIPANT_PHONE])) {
                    $userInfo['phone'] = $participantInfo[Tag::PARTICIPANT_PHONE];
                }

                if (!empty($participantInfo[Tag::PARTICIPANT_MOBILE])) {
                    $userInfo['mobile'] = $participantInfo[Tag::PARTICIPANT_MOBILE];
                }
            }
        }

        return $userInfo;
    }
}
