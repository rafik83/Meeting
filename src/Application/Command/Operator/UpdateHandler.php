<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\Admin\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandler
{
    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var ActivateAccountTokenGenerator
     */
    private $activateAccountTokenGenerator;

    /**
     * @var ActivateAccountTokenRepositoryInterface
     */
    private $activateAccountTokenRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param AdminRepositoryInterface                $adminRepository
     * @param ActivateAccountTokenGenerator           $activateAccountTokenGenerator
     * @param ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository
     * @param EventDispatcherInterface                $eventDispatcher
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->adminRepository                = $adminRepository;
        $this->activateAccountTokenGenerator  = $activateAccountTokenGenerator;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
        $this->eventDispatcher                = $eventDispatcher;
    }

    /**
     * @param Update $update
     *
     * @throws EmailAlreadyExistsException
     */
    public function handle(Update $update)
    {
        $newMail = $update->email !== $update->operator->getEmail();

        if ($newMail && $this->adminRepository->emailExists($update->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $update->email));
        }

        $operator = $update->operator;
        $operator->setFirstName($update->firstname)
            ->setLastname($update->lastname)
            ->setEmail($update->email);

        $operator->setEvents($update->events);

        $this->adminRepository->set($operator);

        if ($newMail) {
            $this->sendActivationEvent($update, $update->operator);
        }
    }

    /**
     * @param Update $update
     * @param Admin  $operator
     */
    private function sendActivationEvent(Update $update, Admin $operator)
    {
        $activateAccountToken = $this->activateAccountTokenGenerator->generate($operator);

        $this->activateAccountTokenRepository->deleteAllForUser($operator);
        $this->activateAccountTokenRepository->create($activateAccountToken);

        $activateAccountEvent = new ActivateAccountEvent(
            $operator,
            $activateAccountToken,
            $update->organizer->getLocale()
        );

        $this->eventDispatcher->dispatch('admin_activate_account', $activateAccountEvent);
    }
}
