<?php

namespace Proximum\Vimeet\Application\Components\User;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Helper\NameCleaner;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class UserInfoGuesser
{
    const TAG_PARTICIPANT_CORRELATION = [
        Tag::PARTICIPANT_GENDER    => 'gender',
        Tag::PARTICIPANT_FIRSTNAME => 'firstName',
        Tag::PARTICIPANT_LASTNAME  => 'lastName',
        Tag::PARTICIPANT_POSITION  => 'position',
        Tag::PARTICIPANT_PHONE     => 'phone',
        Tag::PARTICIPANT_MOBILE    => 'mobile',
        Tag::PARTICIPANT_COUNTRY   => 'country',
    ];

    const TAG_TRANSLATABLE_WITH_KEY = [
        Tag::PARTICIPANT_GENDER => 'gender.%s',
    ];

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
     * @param bool    $translateInfo
     *
     * @return array
     */
    public function getUserInfoFromParticipant(
        User $user,
        string $locale,
        array $userSheets,
        bool $translateInfo = true
    ): array {
        $userInfo = [
            'gender'    => '',
            'firstName' => '',
            'lastName'  => '',
            'position'  => '',
            'phone'     => '',
            'mobile'    => '',
            'country'   => '',
        ];

        if (!empty($userSheets)) {
            $participant = null;

            foreach ($userSheets as $sheet) {
                $participant = $sheet->getUserParticipant($user);

                if (null !== $participant) {
                    break;
                }
            }

            if (null !== $participant) {
                $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $locale);

                foreach (self::TAG_PARTICIPANT_CORRELATION as $tag => $arrayValue) {
                    if (!empty($participantInfo[$tag])) {
                        if ($translateInfo && array_key_exists($tag, self::TAG_TRANSLATABLE_WITH_KEY)) {
                            $userInfo[$arrayValue] = $this->translator->trans(
                                sprintf(
                                    self::TAG_TRANSLATABLE_WITH_KEY[$tag],
                                    $participantInfo[$tag]
                                )
                            );
                        } else {
                            $userInfo[$arrayValue] = $participantInfo[$tag];
                        }

                        if (Tag::PARTICIPANT_FIRSTNAME === $tag) {
                            $userInfo[$arrayValue] = NameCleaner::cleanFirstName($userInfo[$arrayValue]);
                        }

                        if (Tag::PARTICIPANT_LASTNAME === $tag) {
                            $userInfo[$arrayValue] = NameCleaner::cleanLastName($userInfo[$arrayValue]);
                        }
                    }
                }
            }
        }

        return $userInfo;
    }
}
