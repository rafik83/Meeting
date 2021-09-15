<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Application\Command\User\Phone\SendCodeHandler;
use Proximum\Vimeet\Domain\Code\DigitCodeGenerator;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\ParticipantInfoSetter;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ConfirmationCode;
use Proximum\Vimeet\Domain\User\Phone\PhoneSanitizer;

class SendCodeHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();

        $userAccount = $this->prophesize(User\Account::class);
        $user = $this->prophesize(User::class);
        $user->getAccount()->willReturn($userAccount);

        $event = $this->prophesize(Event::class);
        $participant = $this->prophesize(Participant::class);

        $phone = '+33611223344';
        $code = '1234';

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $SMSSender = $this->prophesize(SMSSenderInterface::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $digitCodeGenerator = $this->prophesize(DigitCodeGenerator::class);
        $phoneSanitizer = $this->prophesize(PhoneSanitizer::class);
        $participantInfoSetter = $this->prophesize(ParticipantInfoSetter::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);

        $phoneSanitizer->handle($phone)->shouldBeCalled()->willReturn($phone);

        $userEventPhoneRepository
            ->remove($user->reveal(), $event->reveal())
            ->shouldBeCalled()
        ;

        $userEventPhoneRepository
            ->add(new User\UserEventPhone($user->reveal(), $event->reveal(), $code, $phone, $dateTime))
            ->shouldBeCalled()
        ;

        $digitCodeGenerator
            ->generateCode(ConfirmationCode::CODE_LENGTH)
            ->shouldBeCalled()
            ->willReturn($code)
        ;

        $translator
            ->trans(ConfirmationCode::MESSAGE_TRANSLATION_KEY, ['%code%' => $code], 'messages', 'fr')
            ->shouldBeCalled()
            ->willReturn('Your code confirmation is 1234')
        ;

        $SMSSender
            ->send(new SMS($phone, 'Your code confirmation is 1234', false))
            ->shouldBeCalled()
        ;

        $participantRepository
            ->getAllParticipantForUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant])
        ;

        $participantInfoSetter->setMobile($participant->reveal(), $phone, 'fr')->shouldBeCalled();

        $userAccount->setMobile($phone)->shouldBeCalled();
        $userRepository->set($user)->shouldBeCalled();

        $sendCodeHandler = new SendCodeHandler(
            $userEventPhoneRepository->reveal(),
            $digitCodeGenerator->reveal(),
            $SMSSender->reveal(),
            $phoneSanitizer->reveal(),
            $translator->reveal(),
            $participantInfoSetter->reveal(),
            $participantRepository->reveal(),
            $userRepository->reveal(),
            $dateTime
        );

        $sendCodeHandler->handle(new SendCode($user->reveal(), $event->reveal(), $phone, 'fr'));
    }
}
