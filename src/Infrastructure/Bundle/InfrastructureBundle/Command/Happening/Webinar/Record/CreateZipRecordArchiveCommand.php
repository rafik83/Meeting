<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Download\PlanDownloadRecordArchive;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\ZipRecordArchive;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Webinar\ZipRecordArchivePreparedEvent;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\WebinarIsRecordingException;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateZipRecordArchiveCommand extends Command
{
    public const NAME = 'vimeet:happening:webinar:create-zip-record-archive';

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var DateTimeInterface */
    private $dateTime;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        CommandBusInterface $commandBus,
        EventDispatcherInterface $eventDispatcher,
        DateTimeInterface $dateTime
    ) {
        parent::__construct(self::NAME);

        $this->commandBus = $commandBus;
        $this->happeningRepository = $happeningRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('
                Create the zip of the record archives of the happening webinar and upload the zip.
                The generation is called at the end of the webinar or reprogrammed if recording not ended.
            ')
            ->addArgument('happening', InputArgument::REQUIRED, 'The happening to handle')
        ;
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $happening = $this->happeningRepository->getById($input->getArgument('happening'));

        if (null === $happening) {
            throw new InvalidArgumentException('Happening not found.');
        }

        try {
            $this->commandBus->handle(new ZipRecordArchive($happening));
        } catch (WebinarIsRecordingException $exception) {
            // We reprogram the creation in 15 minutes to wait for the recording to end
            $dueDate = new DateTime();
            $dueDate->setTimestamp($this->dateTime->getTimestamp());
            $dueDate->modify('+15 minutes');

            $this->commandBus->handle(new PlanDownloadRecordArchive($happening, $dueDate));

            $output->writeln('Creation of the zip record archive is reprogrammed at in 15 minutes');

            return 0;
        }

        $this->eventDispatcher->dispatch(
            Events::HAPPENING_ZIP_RECORD_ARCHIVE_PREPARED,
            new ZipRecordArchivePreparedEvent($happening)
        );

        return 0;
    }
}
