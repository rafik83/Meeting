<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\ZipRecordArchive;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Webinar\ZipRecordArchiveNotPreparedEvent;
use Proximum\Vimeet\Application\Event\Happening\Webinar\ZipRecordArchivePreparedEvent;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\WebinarIsRecordingException;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForceZipRecordArchiveCommand extends Command
{
    public const NAME = 'vimeet:happening:webinar:force-zip-record-archive';

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        AdminRepositoryInterface $adminRepository,
        CommandBusInterface $commandBus,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct(self::NAME);

        $this->commandBus = $commandBus;
        $this->happeningRepository = $happeningRepository;
        $this->adminRepository = $adminRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('This command forces the creation of the zip of record archive')
            ->addArgument('happening', InputArgument::REQUIRED, 'The happening to handle')
            ->addArgument('force-regeneration', InputArgument::REQUIRED, 'Force the regeneration of the zip')
            ->addArgument('admin', InputArgument::OPTIONAL, 'The admin to notify')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale to use to notify')
        ;
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $happening = $this->happeningRepository->getById($input->getArgument('happening'));
        $force = $input->getArgument('force-regeneration') === 'force';
        $admin = $this->adminRepository->findById($input->getArgument('admin'));
        $locale = $input->getArgument('locale');

        if (null === $happening) {
            throw new \InvalidArgumentException('Happening not found.');
        }

        try {
            $this->commandBus->handle(new ZipRecordArchive(
                $happening,
                $force
            ));

            if (null !== $admin) {
                $this->eventDispatcher->dispatch(
                    Events::HAPPENING_ZIP_RECORD_ARCHIVE_PREPARED,
                    new ZipRecordArchivePreparedEvent($happening, $admin, $locale ?? $admin->getLocale())
                );
            }
        } catch (WebinarIsRecordingException $exception) {
            if (null !== $admin) {
                $this->eventDispatcher->dispatch(
                    Events::HAPPENING_ZIP_RECORD_ARCHIVE_NOT_PREPARED,
                    new ZipRecordArchiveNotPreparedEvent(
                        $happening,
                        $admin,
                        $locale ?? $admin->getLocale()
                    )
                );
            }
        }

        if (null !== $admin) {
            $this->eventDispatcher->dispatch(
                Events::HAPPENING_ZIP_RECORD_ARCHIVE_PREPARED,
                new ZipRecordArchivePreparedEvent($happening, $admin, $locale ?? $admin->getLocale())
            );
        }

        return 0;
    }
}
