<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\Type;

use InvalidArgumentException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeNameResolver
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * TypeNameResolver constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $locale
     *
     * @return string
     */
    public function resolve(User $user, Event $event, string $locale): string
    {
        $type = $this->typeRepository->getFirstPositionTypeByEventAndUser($event, $user);

        if ($type === null) {
            throw new InvalidArgumentException('Type cannot be null');
        }

        return $type->getTitle($locale);
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return string
     */
    public function resolveWithPreloadedSheets(array &$sheets, string $locale): string
    {
        $types = $this->getSheetsTypes($sheets, $locale);

        ksort($types);

        // get first Type
        $type = reset($types);

        if (false === $type) {
            throw new InvalidArgumentException('Type cannot be null');
        }

        return $type;
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return Type[]
     */
    private function getSheetsTypes(array &$sheets, string $locale): array
    {
        if ($this->atLeastOneTypeHasNoPosition($sheets)) {
            return $this->getTypesIndexedById($sheets, $locale);
        }

        return $this->getTypesIndexedByPosition($sheets, $locale);
    }

    /**
     * @param Sheet[] $sheets
     *
     * @return bool
     */
    private function atLeastOneTypeHasNoPosition(array &$sheets): bool
    {
        foreach ($sheets as $sheet) {
            $type = $sheet->getType();

            if (null === $type->getPosition()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return Type[] indexed by Type position
     */
    private function getTypesIndexedByPosition(array &$sheets, string $locale): array
    {
        $types = [];

        foreach ($sheets as $sheet) {
            $type = $sheet->getType();

            if (null === $type->getPosition()) {
                throw new \BadFunctionCallException('This method can not be used when a Type has no position');
            }

            $types[$type->getPosition()] = $type->getTitle($locale);
        }

        return $types;
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return Type[] indexed by Type position
     */
    private function getTypesIndexedById(array &$sheets, string $locale): array
    {
        $types = [];

        foreach ($sheets as $sheet) {
            $type = $sheet->getType();
            $types[$type->getId()] = $type->getTitle($locale);
        }

        return $types;
    }
}
