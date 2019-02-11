<?php

namespace Proximum\Vimeet\Application\Command\Catalog\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Order\Export\InvalidArgumentForExportException;
use Proximum\Vimeet\Application\Query\Product\ProductsListViewQuery;
use Proximum\Vimeet\Application\Query\Product\ProductsListViewQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class ExportProductsHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var ProductsListViewQueryHandler */
    private $queryHandler;

    /** @var LocalFileStorageAdapter */
    private $fileStorageAdapter;

    /** @var string */
    private $exportLocationDirectoryPath;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $mailSender;

    /**
     * @param EventRepositoryInterface     $eventRepository
     * @param SerializerAdapterInterface   $serializer
     * @param ProductsListViewQueryHandler $queryHandler
     * @param LocalFileStorageAdapter      $fileStorageAdapter
     * @param FileRepositoryInterface      $fileRepository
     * @param MailerInterface              $mailer
     * @param string                       $mailSender
     * @param string                       $exportLocationDirectoryPath
     * @param \DateTimeInterface           $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SerializerAdapterInterface $serializer,
        ProductsListViewQueryHandler $queryHandler,
        LocalFileStorageAdapter $fileStorageAdapter,
        FileRepositoryInterface $fileRepository,
        MailerInterface $mailer,
        $mailSender,
        $exportLocationDirectoryPath,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->serializer = $serializer;
        $this->queryHandler = $queryHandler;
        $this->fileStorageAdapter = $fileStorageAdapter;
        $this->fileRepository = $fileRepository;
        $this->exportLocationDirectoryPath = $exportLocationDirectoryPath;
        $this->dateTime = $dateTime;
        $this->mailer = $mailer;
        $this->mailSender = $mailSender;
    }

    /**
     * @param ExportProducts $command
     *
     * @throws InvalidArgumentForExportException
     */
    public function handle(ExportProducts $command)
    {
        $event = $this->eventRepository->getById($command->eventId);

        if (null === $event) {
            throw new InvalidArgumentForExportException(sprintf('Event %s not found', $command->eventId));
        }

        $view = $this->queryHandler->handle(new ProductsListViewQuery($event, $command->locale));
        $data = $this->serializer->normalize($view, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);

        // Remove first line of file which is composed of the key
        $dataWithoutFirstLine = substr($data, strpos($data, "\n") + 1);

        $file = $this->createFile($event, $dataWithoutFirstLine);

        //$this->notifyCreationOfFile();
    }


    /**
     * @param Event  $event
     * @param string $data
     *
     * @return File
     */
    private function createFile(Event $event, &$data)
    {
        $filePath = $this->fileStorageAdapter->create(
            $data,
            sprintf('products_%s.csv', $event->getId()),
            $this->exportLocationDirectoryPath
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    private function notifyCreationOfFile()
    {
    }
}
