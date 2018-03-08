<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Partner;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Admin\AbstractCreateHandler;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class CreateHandler extends AbstractCreateHandler
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param AdminRepositoryInterface      $adminRepository
     * @param PasswordEncoderInterface      $encoder
     * @param SaltGeneratorInterface        $saltGenerator
     * @param \DateTimeInterface            $dateTime
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        \DateTimeInterface $dateTime
    ) {
        parent::__construct($adminRepository, $encoder, $saltGenerator);

        $this->dateTime                      = $dateTime;
    }

    /**
     * @param Create $create
     *
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
            Admin::ROLE_PARTNER,
            $this->dateTime
        );

        $password = $this->encoder->encode($admin, $create->password);
        $admin->updatePassword($salt, $password);

        // add event and type
        foreach ($create->types as $type) {
            if (!$admin->hasEvent($type->getEvent())) {
                $admin->addEvent($type->getEvent());
            }

            $admin->addType($type);
        }

        $this->adminRepository->add($admin);
    }
}
