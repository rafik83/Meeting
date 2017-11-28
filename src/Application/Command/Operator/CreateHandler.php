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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CreateHandler extends AbstractCreateHandler
{
    /**
     * @var ActivateAccountTokenGenerator
     */
    private $activateAccountTokenGenerator;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param AdminRepositoryInterface                $adminRepository
     * @param PasswordEncoderInterface                $encoder
     * @param SaltGeneratorInterface                  $saltGenerator
     * @param ActivateAccountTokenGenerator           $activateAccountTokenGenerator
     * @param DelayedEventDispatcher                $eventDispatcher
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        DelayedEventDispatcher $eventDispatcher
    ) {
        parent::__construct($adminRepository, $encoder, $saltGenerator);

        $this->activateAccountTokenGenerator  = $activateAccountTokenGenerator;
        $this->eventDispatcher                = $eventDispatcher;
    }

    /**
     * @param Create $create
     * @throws EmailAlreadyExistsException
     */
    public function handle(Create $create)
    {
        $create->email = StringHelper::trimSpacesAndNonBreakSpaces($create->email);

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
            Admin::ROLE_OPERATOR,
            $create->date
        );

        $password = $this->encoder->encode($admin, $create->password);
        $admin->updatePassword($salt, $password);

        foreach ($create->events as $event) {
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
        $token = $this->activateAccountTokenGenerator->generate($admin);
        $event = new ActivateAccountEvent($admin, $token, $create->organizer->getLocale());
        $this->eventDispatcher->dispatch(Events::ADMIN_ACCOUNT_ACTIVATED, $event);
    }
}
