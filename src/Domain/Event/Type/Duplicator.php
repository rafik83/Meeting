<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Type;

use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class Duplicator
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * Duplicator constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param Event $event
     * @param array $duplicationHelper
     *
     * @return array
     */
    public function duplicate(Event $event, array $duplicationHelper): array
    {
        $types = $this->typeRepository->getTypesByEvent($event->getDuplicatedFrom());

        foreach ($types as $type) {
            $newType = new Type($event);
            $newType->setPosition($type->getPosition());
            $newType->setHidden($type->isHidden());
            $newType->setPackage($type->getPackage());
            $newType
                ->getValidationCriteria()
                ->setSheetAccepted($type->getValidationCriteria()->isSheetAccepted());

            foreach ($type->getTranslations()->toArray() as $locale => $translation) {
                $newType->translate($locale, $translation->getTitle(), $translation->getDescription());
            }

            $newType->setSheetTemplate(
                $duplicationHelper['sheetTemplate'][$type->getSheetTemplate()->getId()]
            );

            $newType->setPackage(
                $duplicationHelper['packageTemplate'][$type->getPackage()->getId()]
            );

            $newType->setRegistrationTemplate(
                $duplicationHelper['registrationTemplate'][$type->getRegistrationTemplate()->getId()]
            );

            $this->typeRepository->add($newType);
            $duplicationHelper['type'][$type->getId()] = $newType;
        }

        return $duplicationHelper;
    }
}
