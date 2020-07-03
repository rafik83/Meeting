<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Components\Token\ChangeMailTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Exception\Field\EmptyFieldException;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
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

    public function __construct(
        UserRepositoryInterface $userRepository,
        ChangeMailTokenRepositoryInterface $changeMailTokenRepository,
        ChangeMailTokenGenerator $changeMailTokenGenerator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userRepository            = $userRepository;
        $this->changeMailTokenRepository = $changeMailTokenRepository;
        $this->changeMailTokenGenerator  = $changeMailTokenGenerator;
        $this->eventDispatcher           = $eventDispatcher;
    }

    /**
     * @param ChangeMail $changeMail
     *
     * @throws EmailAlreadyExistsException
     * @throws EmptyFieldException
     * @throws SameEmailException
     */
    public function handle(ChangeMail $changeMail): void
    {
        $user = $changeMail->user;

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
