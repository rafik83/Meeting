<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class CreateHandler
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param PackageRepositoryInterface $packageRepository
     * @param \DateTimeInterface                  $dateTime
     */
    public function __construct(PackageRepositoryInterface $packageRepository, \DateTimeInterface $dateTime)
    {
        $this->packageRepository = $packageRepository;
        $this->dateTime                   = $dateTime;
    }

    /**
     * @param Create $create
     *
     * @return CreateResult
     */
    public function handle(Create $create)
    {
        $package = new Package($create->event, $create->title, $this->dateTime);

        foreach ($create->event->getLocales() as $locale) {
            $package->translate($locale, 'Forfait', 'Participant et planning', 'Options');
        }

        $this->packageRepository->add($package);

        return new CreateResult($package);
    }
}
