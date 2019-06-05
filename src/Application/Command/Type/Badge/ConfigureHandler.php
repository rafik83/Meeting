<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type\Badge;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConfigureHandler
{
    /** @var BadgeRepositoryInterface */
    private $badgeRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(
        BadgeRepositoryInterface $badgeRepository,
        FileStorageInterface $fileStorage
    ) {
        $this->badgeRepository = $badgeRepository;
        $this->fileStorage = $fileStorage;
    }

    public function handle(Configure $configure): void
    {
        $badge = $configure->badge;

        if (!$badge instanceof Badge) {
            $badge = new Badge($configure->event, $configure->type);
        }

        // save header, remove previous
        $header = $previousHeader = $badge->getHeader();

        if ($configure->header instanceof UploadedFile) {
            $header = $this->fileStorage->upload($configure->header);

            if (null !== $previousHeader) {
                $this->fileStorage->remove($previousHeader);
            }
        }

        $badge->update(
            $header,
            $configure->showHeader,
            $configure->showFooterTypeOrCategory,
            $configure->footerTextColor,
            $configure->footerColor,
            $configure->showPosition,
            $configure->showFirstName,
            $configure->showLastName,
            $configure->showSheetTitle,
            $configure->showQRCode,
            $configure->activated,
            $configure->conditioned,
            $configure->conditionedByPackage,
            $configure->showCountry,
            $configure->conditionedByStates
        );

        if ($configure->badge instanceof Badge) {
            $this->badgeRepository->set($badge);

            return;
        }

        $this->badgeRepository->add($badge);
    }
}
