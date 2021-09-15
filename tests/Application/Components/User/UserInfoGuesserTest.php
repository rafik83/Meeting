<?php

namespace Proximum\Vimeet\Tests\Application\Components\User;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class UserInfoGuesserTest extends TestCase
{
    public function testGetUserInfoOfParticipants()
    {
        $locale = 'fr';
        $user = $this->prophesize(User::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $sheet1->getUserParticipant($user->reveal())->willReturn($participant1->reveal());
        $sheet2->getUserParticipant($user->reveal())->willReturn($participant2->reveal());

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $participantInfoGuesser
            ->guessParticipantInfos($participant1->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn([
                Tag::PARTICIPANT_GENDER => 'woman',
                Tag::PARTICIPANT_FIRSTNAME => 'john-paul',
                Tag::PARTICIPANT_LASTNAME => 'DOE',
                // The participant position is not set on purpose
                Tag::PARTICIPANT_PHONE => 'phone',
                Tag::PARTICIPANT_MOBILE => 'mobile',
                Tag::PARTICIPANT_COUNTRY => 'country',
            ])
        ;

        $translator->trans('gender.woman')->shouldBeCalled()->willReturn('Woman');

        $expected = [
            'gender' => 'Woman',
            'firstName' => 'John-Paul',
            'lastName' => 'DOE',
            'position' => '',
            'phone' => 'phone',
            'mobile' => 'mobile',
            'country' => 'country',
        ];

        $userInfoGuesser = new UserInfoGuesser($participantInfoGuesser->reveal(), $translator->reveal());
        $result = $userInfoGuesser->getUserInfoFromParticipant(
            $user->reveal(),
            $locale,
            [$sheet1->reveal(), $sheet2->reveal()],
            true
        );

        $this->assertEquals($expected, $result);
    }

    public function testGetUserInfoOfParticipantsWithoutTranslation()
    {
        $locale = 'fr';
        $user = $this->prophesize(User::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);

        $sheet1->getUserParticipant($user->reveal())->willReturn($participant1->reveal());

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $participantInfoGuesser
            ->guessParticipantInfos($participant1->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn([
                Tag::PARTICIPANT_GENDER => 'woman',
            ])
        ;

        $translator->trans('gender.woman')->shouldNotBeCalled();

        $expected = [
            'gender' => 'woman',
            'firstName' => '',
            'lastName' => '',
            'position' => '',
            'phone' => '',
            'mobile' => '',
            'country' => '',
        ];

        $userInfoGuesser = new UserInfoGuesser($participantInfoGuesser->reveal(), $translator->reveal());
        $result = $userInfoGuesser->getUserInfoFromParticipant(
            $user->reveal(),
            $locale,
            [$sheet1->reveal()],
            false
        );

        $this->assertEquals($expected, $result);
    }
}
