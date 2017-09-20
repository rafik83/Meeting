<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\User;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class UserInfoGuesser
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param TranslatorInterface    $translator
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser, TranslatorInterface $translator)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->translator = $translator;
    }

    /**
     * @param User    $user
     * @param string  $locale
     * @param Sheet[] $userSheets
     *
     * @return array
     */
    public function getUserInfoFromParticipant(User $user, string $locale, array $userSheets): array
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
