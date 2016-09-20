<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Partner;

use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class UpdateHandler
{
    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param Update $update
     *
     * @throws EmailAlreadyExistsException
     */
    public function handle(Update $update)
    {
        $newMail = $update->email !== $update->partner->getEmail();

        if ($newMail && $this->adminRepository->emailExists($update->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $update->email));
        }

        $update->partner
            ->setEmail($update->email)
            ->setFirstname($update->firstname)
            ->setLastname($update->lastname)
            ->setTypes($update->types);

        // remove unused event
        foreach ($update->partner->getEvents() as $event) {
            $find = false;
            foreach ($update->types as $type) {
                if ($type->getEvent() === $event) {
                    $find = true;
                }
            }

            if (false === $find) {
                $update->partner->removeEvent($event);
            }
        }

        $this->adminRepository->set($update->partner);
    }
}
