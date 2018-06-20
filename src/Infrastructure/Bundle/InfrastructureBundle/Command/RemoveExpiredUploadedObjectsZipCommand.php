<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveExpiredUploadedObjectsZipCommand extends Command
{
    public const NAME = 'vimeet:remove:expired_uploaded_objects_zip';

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(FileRepositoryInterface $fileRepository, \DateTimeInterface $dateTime)
    {
        parent::__construct(self::NAME);

        $this->dateTime = $dateTime;
        $this->fileRepository = $fileRepository;
    }

    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Remove expired uploaded objects ZIP files');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->fileRepository->removeExpiredFilesByType(
            File::TYPE_UPLOADED_OBJECTS_ZIP,
            $this->dateTime->modify('-48 hours')
        );
    }
}
