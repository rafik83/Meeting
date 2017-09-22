<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\OMZ\OmzUserListView;
use Proximum\Vimeet\Application\View\OMZ\OmzUserView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ExportHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /**
     * ExportHandler constructor.
     *
     * @param UserRepositoryInterface      $userRepository
     * @param SheetRepositoryInterface     $sheetRepository
     * @param GroupNameResolver            $groupNameResolver
     * @param TypeNameResolver             $typeNameResolver
     * @param ParticipantInfoGuesser       $participantInfoGuesser
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param SerializerAdapterInterface   $serializer
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        GroupNameResolver $groupNameResolver,
        TypeNameResolver $typeNameResolver,
        ParticipantInfoGuesser $participantInfoGuesser,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        SerializerAdapterInterface $serializer,
        TranslatorInterface $translator
    ) {
        $this->userRepository               = $userRepository;
        $this->sheetRepository              = $sheetRepository;
        $this->groupNameResolver            = $groupNameResolver;
        $this->typeNameResolver             = $typeNameResolver;
        $this->participantInfoGuesser       = $participantInfoGuesser;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->serializer                   = $serializer;
        $this->translator                   = $translator;
    }

    /**
     * @param Export $export
     *
     * @return string normalized data for the OMZ import
     */
    public function handle(Export $export)
    {
        $usersViews = [];
        $event      = $export->event;

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);

        foreach ($this->userRepository->findByEvent($event) as $user) {
            $userLocale = $event->getAvailableLocale($user->getLocale());
            $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

            $planning = $this->participantPlanningFormatter->formatPlanningFromUserAndEventWithUnallocated(
                $user,
                $event,
                $userLocale
            );

            $userInfo = $this->getUserInfo($user, $userLocale, $userSheets);

            $usersViews[] = new OmzUserView(
                $user->getId(),
                $this->groupNameResolver->resolve($event, $user, $userSheets),
                null,
                $this->typeNameResolver->resolveWithPreloadedSheets($userSheets, $event->getFallback()),
                $userInfo['gender'],
                $userInfo['firstName'],
                $userInfo['lastName'],
                $userInfo['position'],
                null,
                $userInfo['phone'],
                $user->getEmail(),
                null,
                $userInfo['mobile'],
                $planning
            );
        }

       return $this->serializer->serialize(new OmzUserListView($usersViews), 'csv', [
           'charset' => Charset::WINDOWS_1252,
           'csv_delimiter' => ';',
       ]);
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
