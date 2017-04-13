<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetManager
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var UserManager */
    private $userManager;

    /** @var TypeManager */
    private $typeManager;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param UserManager              $userManager
     * @param TypeManager              $typeManager
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        UserManager $userManager,
        TypeManager $typeManager
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->userManager     = $userManager;
        $this->typeManager     = $typeManager;
    }

    /**
     * @param Event     $event
     * @param User|null $user
     * @param Type|null $type
     *
     * @return Sheet
     */
    public function create(Event $event, User $user = null, Type $type = null)
    {
        if (null === $user) {
            $user = $this->userManager->create();
        }

        if (null === $type) {
            $type = $this->typeManager->create($event);
        }

        $sheet = SheetFactory::create($event, $user, new \DateTime(), $type);
        $sheet->setData([]);
        $sheet->setRegistrationData([]);
        $this->sheetRepository->add($sheet);

        return $sheet;
    }
}
