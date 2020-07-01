<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Components\Token\ChangeMailTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Exception\Field\EmptyFieldException;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Exception\User\InvalidPasswordException;
use Proximum\Vimeet\Application\Exception\User\SameEmailException;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChangeMailHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ChangeMailTokenRepositoryInterface */
    private $changeMailTokenRepository;

    /** @var ChangeMailTokenGenerator */
    private $changeMailTokenGenerator;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var PasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ChangeMailTokenRepositoryInterface $changeMailTokenRepository,
        ChangeMailTokenGenerator $changeMailTokenGenerator,
        EventDispatcherInterface $eventDispatcher,
        PasswordEncoderInterface $passwordEncoder
    ) {
        $this->userRepository = $userRepository;
        $this->changeMailTokenRepository = $changeMailTokenRepository;
        $this->changeMailTokenGenerator = $changeMailTokenGenerator;
        $this->eventDispatcher = $eventDispatcher;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @throws InvalidPasswordException
     * @throws EmailAlreadyExistsException
     * @throws EmptyFieldException
     * @throws SameEmailException
     */
    public function handle(ChangeMail $changeMail)
    {
        $user = $changeMail->user;

        if ($user->getPassword() !== $this->passwordEncoder->encode($user, $changeMail->password)) {
            throw new InvalidPasswordException();
        }

        if (null === $changeMail->mail) {
            throw new EmptyFieldException();
        }

        $changeMail->mail = StringHelper::trimSpacesAndNonBreakSpaces($changeMail->mail);

        if ($user->getEmail() === $changeMail->mail) {
            throw new SameEmailException();
        }

        if (null !== $this->userRepository->findByEmail($changeMail->mail)) {
            throw new EmailAlreadyExistsException();
        }

        $changeMailToken = $this->changeMailTokenGenerator->generate($user, $changeMail->mail);

        $this->changeMailTokenRepository->deleteAllForUser($user);
        $this->changeMailTokenRepository->create($changeMailToken);

        $changeMailEvent = new ChangeMailAddressEvent(
            $user,
            $changeMail->event,
            $changeMailToken
        );

        $this->eventDispatcher->dispatch(Events::USER_MAIL_CHANGED, $changeMailEvent);
    }
}
