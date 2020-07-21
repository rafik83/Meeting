<?php

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->adminRepository = $adminRepository;
        $this->activateAccountTokenGenerator  = $activateAccountTokenGenerator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Update $update
     *
     * @throws EmailAlreadyExistsException
     */
    public function handle(Update $update): void
    {
        $newMail = $update->email !== $update->operator->getEmail();
        $update->email = StringHelper::trimSpacesAndNonBreakSpaces($update->email);

        if ($newMail && $this->adminRepository->emailExists($update->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $update->email));
        }

        $operator = $update->operator;
        $operator->setFirstName($update->firstname)
            ->setLastname($update->lastname)
            ->setEmail($update->email);

        $newEventOfOperator = $update->events;

        // Reintroduce previous event if user doing the action has no right to "see" them
        /** @var Event $event */
        foreach ($operator->getEvents()->toArray() as $event) {
            if (!in_array($event, $update->allowedEventsByAdmin)) {
                $newEventOfOperator[] = $event;
            }
        }

        $operator->setEvents($newEventOfOperator);

        $this->adminRepository->set($operator);

        // If the mail of the operator has changed
        // Send a new activation Event
        if ($newMail) {
            $this->sendActivationEvent($operator);
        }
    }

    private function sendActivationEvent(Admin $operator): void
    {
        $token = $this->activateAccountTokenGenerator->generate($operator);
        $event = new ActivateAccountEvent($operator, $token, $operator->getLocale());
        $this->eventDispatcher->dispatch(Events::ADMIN_ACCOUNT_ACTIVATED, $event);
    }
}
