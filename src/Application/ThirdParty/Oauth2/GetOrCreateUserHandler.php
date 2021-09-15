<?php

namespace Proximum\Vimeet\Application\ThirdParty\Oauth2;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class GetOrCreateUserHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var SessionInterface */
    private $session;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    /** @var SheetGuesser */
    private $sheetGuesser;

    public function __construct(
        ConvertToParticipantHandler $convertToParticipantHandler,
        TemplateDataFactory $templateDataFactory,
        SessionInterface $session,
        SheetGuesser $sheetGuesser,
        UserRepositoryInterface $userRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->convertToParticipantHandler = $convertToParticipantHandler;
        $this->templateDataFactory = $templateDataFactory;
        $this->session = $session;
        $this->sheetGuesser = $sheetGuesser;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
    }

    public function handle(GetOrCreateUser $getOrCreateUser): User
    {
        $user = $this->userRepository->findByEmail($getOrCreateUser->getEmail());
        $registerType = $this->session->getFromFlashBag('register_type');
        $typeId = array_shift($registerType);

        if (null === $typeId && !$user instanceof User) {
            throw new AuthenticationException('Email not found');
        }

        if (null === $typeId) {
            return $user;
        }

        $type = $this->typeRepository->getById($typeId);

        if (!$type instanceof Type) {
            throw new \InvalidArgumentException('Type not found');
        }

        if ($user instanceof User
            && $this->hasSheet($getOrCreateUser->getEvent(), $user, $getOrCreateUser->getLocale())
        ) {
            return $user;
        }

        $participant = $this->convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $getOrCreateUser->getEvent(),
                $type,
                $getOrCreateUser->getEmail(),
                $getOrCreateUser->getLocale(),
                [
                    Tag::PARTICIPANT_FIRSTNAME => $getOrCreateUser->getFirstName(),
                    Tag::PARTICIPANT_LASTNAME => $getOrCreateUser->getLastName(),
                ],
                $this->templateDataFactory->createRegistrationFromType($type, null),
                $this->templateDataFactory->createSheetTemplateFromType($type)
            )
        );

        if (null === $participant) {
            throw new \DomainException('Can not create participant');
        }

        return $participant->getUser();
    }

    private function hasSheet(Event $event, User $user, string $locale)
    {
        try {
            $this->sheetGuesser->getUserSheet(
                $user,
                $event,
                $locale
            );

            return true;
        } catch (SheetNotFoundException $sheetNotFoundException) {
        }

        return false;
    }
}
