<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Form\AbstractType;

abstract class PartnerType extends AbstractType
{
    /** @var TypeRepositoryInterface */
    protected $typeRepository;

    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function buildChoices(array $events, string $locale): array
    {
        $choices = [];

        /** @var Event $event */
        foreach ($events as $event) {
            $types = $this->typeRepository->getTypesByEvent($event);

            $localeToUse = $event->getAvailableLocale($locale);

            /** @var Type $type */
            foreach ($types as $type) {
                $choices[$event->getTitle()][$type->getTitle($localeToUse)] = $type;
            }
        }

        return $choices;
    }
}
