<?php

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
     * @var SheetTemplateCloner
     */
    private $sheetTemplateCloner;

    /**
     * @var RegistrationTemplateCloner
     */
    private $registrationTemplateCloner;

    /**
     * Duplicator constructor.
     *
     * @param TypeRepositoryInterface    $typeRepository
     * @param SheetTemplateCloner        $sheetTemplateCloner
     * @param RegistrationTemplateCloner $registrationTemplateCloner
     */
    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetTemplateCloner $sheetTemplateCloner,
        RegistrationTemplateCloner $registrationTemplateCloner
    ) {
        $this->typeRepository             = $typeRepository;
        $this->sheetTemplateCloner        = $sheetTemplateCloner;
        $this->registrationTemplateCloner = $registrationTemplateCloner;
    }

    /**
     * @param Event  $event
     */
    public function duplicate(Event $event)
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
                $this->sheetTemplateCloner->duplicate(
                    $type->getSheetTemplate(),
                    $event,
                    $type->getSheetTemplate()->getTitle()
                )
            );

            $newType->setRegistrationTemplate(
                $this->registrationTemplateCloner->duplicate(
                    $type->getRegistrationTemplate(),
                    $event,
                    $type->getRegistrationTemplate()->getTitle()
                )
            );

            $this->typeRepository->add($newType);
        }
    }
}
