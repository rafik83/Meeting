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
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
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
     * @param Update $update
     *
     * @throws TypeAlreadyExistsException
     */
    public function handle(Update $update)
    {
        $type = $update->type;
        $type->setPosition($update->position);

        $localesTitleAlreadyExists = [];

        foreach ($update->translations as $locale => $translation) {
            if ($this->typeRepository->typeExists($update->type->getEvent(), $locale, $translation['title'], $update->type)) {
                $localesTitleAlreadyExists[] = $locale;
            } else {
                $type->getTranslations()->get($locale)->update($translation['title']);
            }
        }

        if (!empty($localesTitleAlreadyExists)) {
            throw new TypeAlreadyExistsException($localesTitleAlreadyExists);
        }

        if (isset($update->validationCriteria['sheetAccepted'])) {
            $type->getValidationCriteria()->setSheetAccepted($update->validationCriteria['sheetAccepted']);
        }

        $this->typeRepository->set($type);
    }
}
