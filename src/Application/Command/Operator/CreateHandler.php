<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Application\Command\Admin\AbstractCreateHandler;
use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\Admin\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateHandler extends AbstractCreateHandler
{
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
     * @param PasswordEncoderInterface                $encoder
     * @param SaltGeneratorInterface                  $saltGenerator
     * @param ActivateAccountTokenGenerator           $activateAccountTokenGenerator
     * @param ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository
     * @param EventDispatcherInterface                $eventDispatcher
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($adminRepository, $encoder, $saltGenerator);

        $this->activateAccountTokenGenerator  = $activateAccountTokenGenerator;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
        $this->eventDispatcher                = $eventDispatcher;
    }

    public function handle(Create $create)
    {
        if ($this->adminRepository->emailExists($create->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $create->email));
        }

        $salt = $this->saltGenerator->generate();

        $admin = new Admin(
            $create->email,
            $salt,
            null,
            $create->organizer->getLocale(),
            $create->firstname,
            $create->lastname,
            Admin::ROLE_OPERATOR
        );

        $password = $this->encoder->encode($admin, $create->password);
        $admin->updatePassword($salt, $password);

        foreach ($create->organizer->getEvents() as $event) {
            $admin->addEvent($event);
        }

        $this->adminRepository->add($admin);

        $this->sendActivationEvent($create, $admin);
    }

    /**
     * @param Create $create
     * @param Admin  $admin
     */
    private function sendActivationEvent(Create $create, Admin $admin)
    {
        $activateAccountToken = $this->activateAccountTokenGenerator->generate($admin);

        $this->activateAccountTokenRepository->deleteAllForUser($admin);
        $this->activateAccountTokenRepository->create($activateAccountToken);

        $activateAccountEvent = new ActivateAccountEvent(
            $admin,
            $activateAccountToken,
            $create->organizer->getLocale()
        );

        $this->eventDispatcher->dispatch('admin_activate_account', $activateAccountEvent);
    }
}
