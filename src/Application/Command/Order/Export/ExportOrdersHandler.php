<?php

namespace Proximum\Vimeet\Application\Command\Order\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Order\Export\InvalidArgumentForExportException;
use Proximum\Vimeet\Application\Query\Order\Export\OrdersExportViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\OrdersExportViewQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ExportOrdersMail;

class ExportOrdersHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var OrdersExportViewQueryHandler */
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
     * @param OrdersExportViewQueryHandler $queryHandler
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
        OrdersExportViewQueryHandler $queryHandler,
        LocalFileStorageAdapter $fileStorageAdapter,
        FileRepositoryInterface $fileRepository,
        MailerInterface $mailer,
        $mailSender,
        $exportLocationDirectoryPath,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository             = $eventRepository;
        $this->serializer                  = $serializer;
        $this->queryHandler                = $queryHandler;
        $this->fileStorageAdapter          = $fileStorageAdapter;
        $this->fileRepository              = $fileRepository;
        $this->exportLocationDirectoryPath = $exportLocationDirectoryPath;
        $this->dateTime                    = $dateTime;
        $this->mailer                      = $mailer;
        $this->mailSender                  = $mailSender;
    }

    /**
     * @param ExportOrders $command
     *
     * @throws InvalidArgumentForExportException
     */
    public function handle(ExportOrders $command)
    {
        $event = $this->eventRepository->getById($command->eventId);

        if (null === $event) {
            throw new InvalidArgumentForExportException(sprintf('Event %s not found', $command->eventId));
        }

        $view = $this->queryHandler->handle(new OrdersExportViewQuery($event, $command->locale));
        $data = $this->serializer->serialize($view, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);

        // Remove first line of file which is composed of the key
        $dataWithoutFirstLine = substr($data, strpos($data, "\n") + 1);

        $file = $this->createFile($event, $dataWithoutFirstLine);

        $this->notifyCreationOfFile($event, $command, $file);
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
            sprintf('orders_%s.csv', $event->getId()),
            $this->exportLocationDirectoryPath
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }

    /**
     * Send a mail to the emailToNotify with the link to download the csv file
     *
     * @param Event        $event
     * @param ExportOrders $command
     * @param File         $file
     */
    private function notifyCreationOfFile(Event $event, ExportOrders $command, File $file)
    {
        $this->mailer->send(new ExportOrdersMail(
            $event,
            $this->mailSender,
            $command->emailToNotify,
            $command->locale,
            $file->getHash(),
            $file->getId()
        ));
    }
}
