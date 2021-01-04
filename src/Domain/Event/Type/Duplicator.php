<?php

namespace Proximum\Vimeet\Domain\Event\Type;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class Duplicator
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $types = $this->typeRepository->getTypesByEvent($event->getDuplicatedFrom());

        foreach ($types as $type) {
            $newType = new Type($event);
            $newType->setPosition($type->getPosition());
            $newType->setHidden($type->isHidden());
            $newType
                ->getValidationCriteria()
                ->setSheetAccepted($type->getValidationCriteria()->isSheetAccepted());

            foreach ($type->getTranslations()->toArray() as $locale => $translation) {
                $newType->translate($locale, $translation->getTitle(), $translation->getDescription());
            }

            $newType->setSheetTemplate(
                $duplicatorDataStorage->sheetTemplates[$type->getSheetTemplate()->getId()]
            );

            $newType->setPackage(
                $duplicatorDataStorage->packageTemplates[$type->getPackage()->getId()]
            );

            $newType->setRegistrationTemplate(
                $duplicatorDataStorage->registrationTemplates[$type->getRegistrationTemplate()->getId()]
            );

            $this->typeRepository->add($newType);
            $duplicatorDataStorage->types[$type->getId()] = $newType;
        }

        return $duplicatorDataStorage;
    }
}
