<?php

namespace Proximum\Vimeet\Application\Command\User\Phone;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Code\DigitCodeGenerator;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Participant\ParticipantInfoSetter;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ConfirmationCode;
use Proximum\Vimeet\Domain\User\Phone\PhoneSanitizer;

class SendCodeHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var DigitCodeGenerator */
    private $digitCodeGenerator;

    /** @var PhoneSanitizer */
    private $phoneSanitizer;

    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ParticipantInfoSetter */
    private $participantInfoSetter;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var DateTimeInterface */
    private $dateTime;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param DigitCodeGenerator                $digitCodeGenerator
     * @param SMSSenderInterface                $SMSSender
     * @param PhoneSanitizer                    $phoneSanitizer
     * @param TranslatorInterface               $translator
     * @param ParticipantInfoSetter             $participantInfoSetter
     * @param ParticipantRepositoryInterface    $participantRepository
     * @param UserRepositoryInterface           $userRepository
     * @param DateTimeInterface                 $dateTime
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        DigitCodeGenerator $digitCodeGenerator,
        SMSSenderInterface $SMSSender,
        PhoneSanitizer $phoneSanitizer,
        TranslatorInterface $translator,
        ParticipantInfoSetter $participantInfoSetter,
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        DateTimeInterface $dateTime
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->digitCodeGenerator = $digitCodeGenerator;
        $this->SMSSender = $SMSSender;
        $this->phoneSanitizer = $phoneSanitizer;
        $this->translator = $translator;
        $this->participantInfoSetter = $participantInfoSetter;
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param SendCode $sendCode
     *
     * @throws FailToSendSMSException
     * @throws InvalidReceiverException
     */
    public function handle(SendCode $sendCode)
    {
        $code = $this->digitCodeGenerator->generateCode(ConfirmationCode::CODE_LENGTH);

        $phone = $this->phoneSanitizer->handle($sendCode->phone);

        $this->sendSms($phone, $code, $sendCode->locale);

        // SMS is successfully sent, persist the code
        $this->save($sendCode->user, $sendCode->event, $phone, $code, $sendCode->locale);
    }

    /**
     * @param string $phone
     * @param string $code
     * @param string $locale
     *
     * @throws FailToSendSMSException
     * @throws InvalidReceiverException
     */
    private function sendSms($phone, $code, $locale)
    {
        $message = $this->translator->trans(
            ConfirmationCode::MESSAGE_TRANSLATION_KEY,
            ['%code%' => $code],
            'messages',
            $locale
        );

        $this->SMSSender->send(new SMS($phone, $message, false));
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $phone
     * @param string $code
     * @param string $locale
     */
    private function save(User $user, Event $event, $phone, $code, $locale)
    {
        $this->saveCode($user, $event, $phone, $code);
        $this->saveUserPhone($user, $event, $phone, $locale);
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $phone
     * @param string $code
     */
    private function saveCode(User $user, Event $event, $phone, $code)
    {
        // Remove eventual previous code for this (user, event)
        $this->userEventPhoneRepository->remove($user, $event);

        $this->userEventPhoneRepository->add(
            new UserEventPhone($user, $event, $code, $phone, $this->dateTime)
        );
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $phone
     * @param string $locale
     */
    private function saveUserPhone(User $user, Event $event, $phone, $locale)
    {
        $this->setPhoneToUserSheetsParticipation($user, $event, $phone, $locale);
        $this->setPhoneToUserAccount($user, $phone);
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $phone
     * @param string $locale
     */
    private function setPhoneToUserSheetsParticipation(User $user, Event $event, $phone, $locale)
    {
        foreach ($this->participantRepository->getAllParticipantForUser($event, $user) as $participant) {
            $this->participantInfoSetter->setMobile($participant, $phone, $locale);
        }
    }

    /**
     * @param User   $user
     * @param string $phone
     */
    private function setPhoneToUserAccount(User $user, $phone)
    {
        $user->getAccount()->setMobile($phone);
        $this->userRepository->set($user);
    }
}
