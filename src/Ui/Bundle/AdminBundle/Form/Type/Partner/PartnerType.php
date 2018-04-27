<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Form\AbstractType;

abstract class PartnerType extends AbstractType
{
    /**
     * @var TypeRepositoryInterface
     */
    protected $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param array  $events
     * @param string $locale
     *
     * @return array
     */
    public function buildChoices(array $events, $locale)
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
