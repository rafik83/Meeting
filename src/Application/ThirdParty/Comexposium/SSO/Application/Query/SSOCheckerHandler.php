<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\EmailToUserConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\CanNotCreateUserException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\NoRegistrationTypeIsAvailableException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotOnEventException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\TokenChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class SSOCheckerHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TokenChecker */
    private $tokenChecker;

    /** @var EmailToUserConverter */
    private $emailToUserConverter;

    /** @var SSORegistrationTypeResolver */
    private $SSORegistrationTypeResolver;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        TokenChecker $tokenChecker,
        EmailToUserConverter $emailToUserConverter,
        SSORegistrationTypeResolver $SSORegistrationTypeResolver,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->tokenChecker = $tokenChecker;
        $this->emailToUserConverter = $emailToUserConverter;
        $this->SSORegistrationTypeResolver = $SSORegistrationTypeResolver;
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param SSOChecker $query
     *
     * @throws UserNotFoundException
     * @throws UserNotOnEventException
     * @throws ComboEmailUserNotValidException
     * @throws CanNotCreateUserException
     *
     * @return User
     */
    public function handle(SSOChecker $query): User
    {
        $user = $this->userRepository->findByEmail($query->email);

        if ($user instanceof User) {
            return $this->handleKnownUserLogin($query->event, $user, $query->token, $query->isExhibitor);
        }

        if ($query->isExhibitor) {
            throw new UserNotFoundException(sprintf('User with mail %s not found', $query->email));
        }

        return $this->handleNotKnownVisitorLogin($query->event, $query->email, $query->token, $query->locale);
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $token
     * @param bool   $isExhibitor
     *
     * @throws ComboEmailUserNotValidException
     * @throws UserNotOnEventException
     * @throws NoRegistrationTypeIsAvailableException
     *
     * @return User
     */
    private function handleKnownUserLogin(Event $event, User $user, string $token, bool $isExhibitor): User
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        if (empty($sheets)) {
            if ($isExhibitor) {
                throw new UserNotOnEventException(
                    sprintf(
                        'User with mail %s not found on Event %d',
                        $user->getEmail(),
                        $event->getId()
                    )
                );
            }

            $visitorType = $this->SSORegistrationTypeResolver->handle($event);
            $hasVisibleTypeByEvent = $this->typeRepository->hasVisibleTypeByEvent($event);

            if (null === $visitorType && false === $hasVisibleTypeByEvent) {
                throw new NoRegistrationTypeIsAvailableException('No registration type is available');
            }
        }

        $this->checkEmailAndToken($user->getEmail(), $token);

        return $user;
    }

    /**
     * @param Event  $event
     * @param string $email
     * @param string $token
     * @param string $locale
     *
     * @throws CanNotCreateUserException
     * @throws ComboEmailUserNotValidException
     *
     * @return User
     */
    private function handleNotKnownVisitorLogin(Event $event, string $email, string $token, string $locale): User
    {
        $this->checkEmailAndToken($email, $token);

        $user = $this->emailToUserConverter->handle($event, $email, $locale);

        if (!$user instanceof User) {
            throw new CanNotCreateUserException(sprintf('Can not create user for email "%s"', $email));
        }

        return $user;
    }

    /**
     * @param string $email
     * @param string $token
     *
     * @throws ComboEmailUserNotValidException
     */
    private function checkEmailAndToken(string $email, string $token): void
    {
        if (!$this->tokenChecker->isMailTokenComboValid($email, $token)) {
            throw new ComboEmailUserNotValidException(
                sprintf(
                    'Combo email %s and token %s not valid',
                    $email,
                    $token
                )
            );
        }
    }
}
