<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Application\Query\User\Event\Contact\TypeAndCategoriesTranslated;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

trait TypeAndCategoriesTrait
{
    /**
     * @return TypeAndCategoriesTranslated[] indexed by typeId
     */
    private function getTypeAndCategoriesTranslatedIndexedByTypeId(TypeRepositoryInterface $typeRepository, Event $event, string $locale): array
    {
        $types = $typeRepository->getTypesAndCategoriesTranslationsByEvent($event, $locale);
        $typeAndCategoriesTranslatedIndexedByTypeId = [];

        foreach ($types as $type) {
            $typeAndCategoriesTranslatedIndexedByTypeId[$type->getId()] = new TypeAndCategoriesTranslated(
                $type->getTitle($locale),
                $type->getCategoriesTitles($locale)
            );
        }

        return $typeAndCategoriesTranslatedIndexedByTypeId;
    }

}
