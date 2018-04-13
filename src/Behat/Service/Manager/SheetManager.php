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
use Proximum\Vimeet\Domain\Sheet\SheetInfoSetter;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetManager
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var UserManager */
    private $userManager;

    /** @var TypeManager */
    private $typeManager;

    /** @var SheetInfoSetter */
    private $sheetInfoSetter;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoSetter          $sheetInfoSetter
     * @param UserManager              $userManager
     * @param TypeManager              $typeManager
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoSetter $sheetInfoSetter,
        UserManager $userManager,
        TypeManager $typeManager
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->userManager     = $userManager;
        $this->typeManager     = $typeManager;
        $this->sheetInfoSetter = $sheetInfoSetter;
    }

    /**
     * @param Event            $event
     * @param User|null        $user
     * @param Type|null        $type
     * @param string|null      $title
     * @param Sheet\Group|null $group
     *
     * @return Sheet
     */
    public function create(Event $event, User $user = null, Type $type = null, $title = null, Sheet\Group $group = null)
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
        $sheet->setTitle($title);

        if (null !== $group) {
            $sheet->setGroup($group);
        }

        if (null !== $title) {
            $this->sheetInfoSetter->setSheetTitle($sheet, $title);
        }

        $this->sheetRepository->add($sheet);

        return $sheet;
    }

    /**
     * @param Event  $event
     * @param string $sheetTitle
     *
     * @return null|Sheet
     */
    public function getSheetByEventAndTitle(Event $event, string $sheetTitle):? Sheet
    {
        return $this->sheetRepository->getSheetByEventAndTitle($event, $sheetTitle);
    }

    /**
     * @param Sheet $sheet
     */
    public function setInCatalog(Sheet $sheet)
    {
        $sheet->setInCatalog(true);
        $sheet->setInCatalogAt(new \DateTime());

        $this->sheetRepository->set($sheet);
    }

    /**
     * @param Sheet $sheet
     */
    public function setValidated(Sheet $sheet)
    {
        $sheet->markAsValidated();

        $this->sheetRepository->set($sheet);
    }

    /**
     * @param Sheet $sheet
     */
    public function setEnabled(Sheet $sheet)
    {
        $sheet->setEnable(true);

        $this->sheetRepository->set($sheet);
    }

    public function updateCompletness(Sheet $sheet, int $completeness): void
    {
        $sheet->setCompleteness($completeness);

        $this->sheetRepository->set($sheet);
    }
}
