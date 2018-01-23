<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ChangeMailActivationHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ChangeMailTokenRepositoryInterface
     */
    private $changeMailTokenRepository;

    /**
     * @param UserRepositoryInterface            $userRepository
     * @param ChangeMailTokenRepositoryInterface $changeMailTokenRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ChangeMailTokenRepositoryInterface $changeMailTokenRepository
    ) {
        $this->userRepository            = $userRepository;
        $this->changeMailTokenRepository = $changeMailTokenRepository;
    }

    /**
     * @param ChangeMailActivation $changeMailActivation
     */
    public function handle(ChangeMailActivation $changeMailActivation)
    {
        $mail = StringHelper::trimSpacesAndNonBreakSpaces($changeMailActivation->mail);
        $user = $changeMailActivation->user;

        $user->updateEmail($mail);
        $this->userRepository->set($user);

        $this->changeMailTokenRepository->deleteAllForUser($user);
    }
}
