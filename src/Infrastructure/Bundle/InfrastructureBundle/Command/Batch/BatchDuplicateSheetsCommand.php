<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchDuplicateSheets;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchDuplicateSheetsHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as ExtraDataType;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchDuplicateSheetsCommand extends Command
{
    public const NAME = 'vimeet:sheets:duplicate';

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var PostBatchDuplicateSheetsHandler */
    private $postBatchDuplicateSheetsHandler;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        TypeRepositoryInterface $typeRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        PostBatchDuplicateSheetsHandler $postBatchDuplicateSheetsHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository = $adminRepository;
        $this->sheetRepository = $sheetRepository;
        $this->typeRepository = $typeRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->postBatchDuplicateSheetsHandler = $postBatchDuplicateSheetsHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch duplicate sheets action')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
            ->addArgument('typeId', InputArgument::REQUIRED, 'Type id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $admin = $this->adminRepository->findById($input->getArgument('adminId'));

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $type = $this->typeRepository->getById($input->getArgument('typeId'));

        if (!$type instanceof Type) {
            throw new \InvalidArgumentException('Type not found.');
        }

        $extraData = $this->extraDataRepository
            ->getExtraDataForEvent($type->getEvent(), ExtraDataType::DUPLICATE_SHEET_IDS);

        if (!$extraData instanceof ExtraData) {
            throw new \InvalidArgumentException('Extra data not found.');
        }

        $sheetIds = explode(',', $extraData->getValue());
        $sheets = $this->sheetRepository->findByIds($sheetIds);

        $this->postBatchDuplicateSheetsHandler->handle(new PostBatchDuplicateSheets($sheets, $type));
    }
}
