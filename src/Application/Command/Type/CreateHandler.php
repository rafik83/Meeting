<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Exception\Type\TypeAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
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
     *
     * @throws TypeAlreadyExistsException
     */
    public function handle(Create $create)
    {
        $type = new Type($create->event);
        $type->setTemplate($create->template);
        $type->setPosition($create->position);

        $localesTitleAlreadyExists = [];

        foreach ($create->translations as $locale => $translation) {
            if ($this->typeRepository->typeExists($create->event, $locale, $translation['title'])) {
                $localesTitleAlreadyExists[] = $locale;
            } else {
                $type->getTranslations()->set(
                    $locale,
                    new TypeTranslation($type, $locale, $translation['title'], $translation['description'])
                );
            }
        }

        if (!empty($localesTitleAlreadyExists)) {
            throw new TypeAlreadyExistsException($localesTitleAlreadyExists);
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

        if ($create->registrationTemplate->getEvent() === $create->event) {
            $registrationTemplate = $create->registrationTemplate;
        } else {
            $registrationTemplate = new RegistrationTemplate(
                $type->getTitle($create->event->getAvailableLocale($create->locale)),
                $create->sheetTemplate->getValue(),
                $create->sheetTemplate->getLocales(),
                $create->sheetTemplate->getFallback(),
                $this->dateTime
            );
            $registrationTemplate->setEvent($create->event);
        }

        $type->setSheetTemplate($sheetTemplate);
        $type->setRegistrationTemplate($registrationTemplate);

        $this->typeRepository->add($type);

        $create->type = $type;
    }
}
