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
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class CreateHandler
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;

    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param TypeRepositoryInterface                 $typeRepository
     * @param SheetTemplateRepositoryInterface        $sheetTemplateRepository
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param \DateTimeInterface                      $dateTime
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->typeRepository                 = $typeRepository;
        $this->sheetTemplateRepository        = $sheetTemplateRepository;
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->dateTime                       = $dateTime;
    }

    /**
     * @param Create $create
     *
     * @throws TypeAlreadyExistsException
     */
    public function handle(Create $create)
    {
        $type = new Type($create->event);
        $type->setPosition($create->rank);

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

            $this->sheetTemplateRepository->add($sheetTemplate);
        }

        if ($create->registrationTemplate->getEvent() === $create->event) {
            $registrationTemplate = $create->registrationTemplate;
        } else {
            $registrationTemplate = new RegistrationTemplate(
                $type->getTitle($create->event->getAvailableLocale($create->locale)),
                $create->registrationTemplate->getValue(),
                $create->registrationTemplate->getLocales(),
                $create->registrationTemplate->getFallback(),
                $this->dateTime
            );
            $registrationTemplate->setEvent($create->event);

            $this->registrationTemplateRepository->add($registrationTemplate);
        }

        $type->setSheetTemplate($sheetTemplate);
        $type->setRegistrationTemplate($registrationTemplate);
        $type->setPackage($create->package);

        $this->typeRepository->add($type);

        $create->type = $type;
    }
}
