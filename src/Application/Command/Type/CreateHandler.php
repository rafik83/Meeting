<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
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
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(TypeRepositoryInterface $typeRepository, \DateTimeInterface $dateTime)
    {
        $this->typeRepository = $typeRepository;
        $this->dateTime       = $dateTime;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $type = new Type($create->event);
        $type->setTemplate($create->template);
        $type->setPosition($create->position);

        foreach ($create->translations as $locale => $translation) {
            $type->getTranslations()->set($locale, new TypeTranslation($type, $locale, $translation['title']));
        }

        if (isset($create->validationCriteria['sheetAccepted'])) {
            $type->getValidationCriteria()->setSheetAccepted($create->validationCriteria['sheetAccepted']);
        }

        if ($create->sheetTemplate->getEvent() === $create->event) {
            $sheetTemplate = $create->sheetTemplate;
        } else {
            $sheetTemplate = new SheetTemplate(
                $type->getTitle($create->event->getAvailableLocale($create->locale)),
                $create->sheetTemplate->getValue(),
                $create->sheetTemplate->getLocales(),
                $create->sheetTemplate->getFallback(),
                $this->dateTime
            );
            $sheetTemplate->setEvent($create->event);
        }

        $type->setSheetTemplate($sheetTemplate);

        $this->typeRepository->add($type);

        $create->type = $type;
    }
}
