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
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeManager
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var UserManager */
    private $userManager;

    /** @var SheetTemplateManager */
    private $sheetTemplateManager;

    /** @var RegistrationTemplateManager */
    private $registrationTemplateManager;

    /**
     * @param TypeRepositoryInterface     $typeRepository
     * @param UserManager                 $userManager
     * @param SheetTemplateManager        $sheetTemplateManager
     * @param RegistrationTemplateManager $registrationTemplateManager
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        UserManager $userManager,
        SheetTemplateManager $sheetTemplateManager,
        RegistrationTemplateManager $registrationTemplateManager
    ) {
        $this->typeRepository              = $typeRepository;
        $this->userManager                 = $userManager;
        $this->sheetTemplateManager        = $sheetTemplateManager;
        $this->registrationTemplateManager = $registrationTemplateManager;
    }

    /**
     * @param Event $event
     *
     * @return Type
     */
    public function create(Event $event)
    {
        $type = new Type($event);
        $type->setSheetTemplate($this->sheetTemplateManager->create($event));
        $type->setRegistrationTemplate($this->registrationTemplateManager->create($event));
        $package = new Package($event, 'Forfait', new \DateTime());
        $package->enable(false, false, false);
        $type->setPackage($package);
        $type->translate('fr', 'Type 1', 'Description du type');

        $this->typeRepository->add($type);

        return $type;
    }
}
