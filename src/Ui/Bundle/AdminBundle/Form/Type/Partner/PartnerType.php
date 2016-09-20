<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner;

use Proximum\Vimeet\Domain\Model\Admin;
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
     * @param array $events
     * @param Admin $admin
     *
     * @return array
     */
    public function buildChoices(array $events, Admin $admin)
    {
        $choices = [];

        /** @var Event $event */
        foreach ($events as $event) {
            $types = $this->typeRepository->getTypesByEvent($event);
            /** @var Type $type */
            foreach ($types as $type) {
                $choices[$event->getTitle()][$type->getTitle($admin->getLocale())] = $type;
            }
        }

        return $choices;
    }
}
