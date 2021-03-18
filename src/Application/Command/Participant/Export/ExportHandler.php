<?php

namespace Proximum\Vimeet\Application\Command\Participant\Export;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Participant\Export\ExportQuery;
use Proximum\Vimeet\Application\Query\Participant\Export\ExportQueryHandler;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Participant\Export\DownloadExportMail;

class ExportHandler
{
    /** @var ExportQueryHandler */
    private $exportQueryHandler;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $uploadDir;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $sender;

    public function __construct(
        ExportQueryHandler $exportQueryHandler,
        SerializerAdapterInterface $serializerAdapter,
        FileRepositoryInterface $fileRepository,
        FileStorageInterface $fileStorage,
        MailerInterface $mailer,
        string $uploadDir,
        string $sender,
        \DateTimeInterface $dateTime
    ) {
        $this->exportQueryHandler = $exportQueryHandler;
        $this->serializerAdapter = $serializerAdapter;
        $this->fileStorage = $fileStorage;
        $this->dateTime = $dateTime;
        $this->uploadDir = $uploadDir;
        $this->fileRepository = $fileRepository;
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public function handle(Export $command): void
    {
        $view = $this->exportQueryHandler->handle(new ExportQuery(
            $command->event,
            $command->participantIds,
            $command->locale
        ));

        $serializedContent = $this->serializerAdapter->serialize($view, 'csv', [
            'csv_delimiter' => ';',
        ]);

        // Remove first line of file which is composed of the key
        $dataWithoutFirstLine = substr($serializedContent, strpos($serializedContent, "\n") + 1);

        $path = $this->fileStorage->create(
            $dataWithoutFirstLine,
            sprintf('export_event_participants_%s.csv', $this->dateTime->format('Y_m_d_His')),
            $this->uploadDir
        );

        $file = new File($path, $this->dateTime);
        $this->fileRepository->add($file);

        $this->mailer->send(new DownloadExportMail(
            $command->event,
            $this->sender,
            $command->admin->getEmail(),
            $command->locale,
            $file->getHash(),
            $file->getId()
        ));
    }
}
