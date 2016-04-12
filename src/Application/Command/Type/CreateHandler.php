<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $type = new Type($create->event);
        $type->setTemplate($create->template);

        foreach ($create->translations as $locale => $translation) {
            $type->getTranslations()->set($locale, new TypeTranslation($type, $locale, $translation['title']));
        }

        if (isset($create->validationCriteria['sheetAccepted'])) {
            $type->getValidationCriteria()->setSheetAccepted($create->validationCriteria['sheetAccepted']);
        }

        if ($create->sheetTemplate->getEvent() === $create->event) {
            $sheetTemplate = $create->sheetTemplate;
        } else {
            $sheetTemplate = $create->sheetTemplate->duplicate(
                $type->getTitle($create->event->getAvailableLocale($create->locale)),
                $create->createdAt
            );
            $sheetTemplate->setEvent($create->event);
        }

        $type->setSheetTemplate($sheetTemplate);

        $this->typeRepository->add($type);

        $create->type = $type;
    }
}
