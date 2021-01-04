<?php

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

        if (null === $type) {
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
        $type = $this->resolveTypeWithPreloadedSheets($sheets);

        return $type->getTitle($locale);
    }

    /**
     * @param array $sheets
     *
     * @return Type
     */
    public function resolveTypeWithPreloadedSheets(array &$sheets): Type
    {
        $types = $this->getSheetsTypes($sheets);

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
     *
     * @return Type[]
     */
    private function getSheetsTypes(array &$sheets): array
    {
        if ($this->atLeastOneTypeHasNoPosition($sheets)) {
            return $this->getTypesIndexedById($sheets);
        }

        return $this->getTypesIndexedByPosition($sheets);
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
     *
     * @return Type[] indexed by Type position
     */
    private function getTypesIndexedByPosition(array &$sheets): array
    {
        $types = [];

        foreach ($sheets as $sheet) {
            $type = $sheet->getType();

            if (null === $type->getPosition()) {
                throw new \BadFunctionCallException('This method can not be used when a Type has no position');
            }

            $types[$type->getPosition()] = $type;
        }

        return $types;
    }

    /**
     * @param Sheet[] $sheets
     *
     * @return Type[] indexed by Type position
     */
    private function getTypesIndexedById(array &$sheets): array
    {
        $types = [];

        foreach ($sheets as $sheet) {
            $type = $sheet->getType();
            $types[$type->getId()] = $type;
        }

        return $types;
    }
}
