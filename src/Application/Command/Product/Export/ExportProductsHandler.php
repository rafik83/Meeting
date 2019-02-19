<?php

namespace Proximum\Vimeet\Application\Command\Product\Export;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Order\Export\InvalidArgumentForExportException;
use Proximum\Vimeet\Application\Query\Product\ProductsListViewQuery;
use Proximum\Vimeet\Application\Query\Product\ProductsListViewQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ExportProductsMail;

class ExportProductsHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var ProductsListViewQueryHandler */
    private $queryHandler;

    /** @var FileStorageInterface */
    private $fileStorage;
    
    /** @var FileFactory */
    private $fileFactory;

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

    public function __construct(
        EventRepositoryInterface $eventRepository,
        SerializerAdapterInterface $serializer,
        ProductsListViewQueryHandler $queryHandler,
        FileStorageInterface $fileStorage,
        FileRepositoryInterface $fileRepository,
        FileFactory $fileFactory,
        MailerInterface $mailer,
        string $mailSender,
        string $exportLocationDirectoryPath,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->serializer = $serializer;
        $this->queryHandler = $queryHandler;
        $this->fileStorage = $fileStorage;
        $this->fileFactory = $fileFactory;
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
    public function handle(ExportProducts $command): void
    {
        $event = $this->eventRepository->getById($command->eventId);

        if (null === $event) {
            throw new InvalidArgumentForExportException(sprintf('Event %s not found', $command->eventId));
        }

        $view = $this->queryHandler->handle(new ProductsListViewQuery($event, $command->locale));
        $data = $this->serializer->serialize($view, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);

        $file = $this->createFile($data);
    
        $this->notifyCreationOfFile($event, $command, $file);
    }

    /**
     * @param string $data
     *
     * @return File
     */
    public function createFile(string $data): File
    {
        $filePath = $this->fileStorage->create(
            $data,
            sprintf('export_product_list_%s.csv', $this->dateTime->format('H_i_s_d_m_Y')),
            $this->exportLocationDirectoryPath
        );
    
        $file = $this->fileFactory->createAndPersistFile($filePath, File::TYPE_EXPORT_PRODUCT_LIST);
        $this->fileRepository->add($file);

        return $file;
    }
    
    /**
     * Send a mail to the emailToNotify with the link to download the csv file
     *
     * @param Event          $event
     * @param ExportProducts $command
     * @param File           $file
     */
    public function notifyCreationOfFile(Event $event, ExportProducts $command, File $file): void
    {
        $this->mailer->send(new ExportProductsMail(
            $event,
            $this->mailSender,
            $command->emailToNotify,
            $command->locale,
            $file->getHash(),
            $file->getId()
        ));
    }
}
