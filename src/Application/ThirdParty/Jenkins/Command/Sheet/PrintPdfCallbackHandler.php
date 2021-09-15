<?php

namespace Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Exception\Sheet\PrintPdfErrorException;
use Proximum\Vimeet\Domain\Exception\DomainException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\PrintPdf\ErrorPrintMail;

class PrintPdfCallbackHandler
{
    /** @var string */
    private $phantomJsSheetPdfTrustedName;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var MailerInterface */
    private $mailer;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var string */
    private $emailSender;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var FileStorageInterface */
    private $fileStorage;

    /**
     * @param FileStorageInterface     $fileStorage
     * @param EventRepositoryInterface $eventRepository
     * @param FileRepositoryInterface  $fileRepository
     * @param MailerInterface          $mailer
     * @param string                   $emailSender
     * @param string                   $phantomJsSheetPdfTrustedName
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        FileStorageInterface $fileStorage,
        EventRepositoryInterface $eventRepository,
        FileRepositoryInterface $fileRepository,
        MailerInterface $mailer,
        string $emailSender,
        string $phantomJsSheetPdfTrustedName,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository              = $eventRepository;
        $this->fileRepository               = $fileRepository;
        $this->mailer                       = $mailer;
        $this->emailSender                  = $emailSender;
        $this->phantomJsSheetPdfTrustedName = $phantomJsSheetPdfTrustedName;
        $this->dateTime                     = $dateTime;
        $this->fileStorage                  = $fileStorage;
    }

    /**
     * @param PrintPdfCallback $command
     *
     * @throws PrintPdfErrorException
     */
    public function handle(PrintPdfCallback $command): void
    {
        if ($this->phantomJsSheetPdfTrustedName !== $command->name) {
            throw new \InvalidArgumentException(sprintf('Given build name %s is not trusted', $command->name));
        }

        $event = $this->eventRepository->getById($command->eventId);

        if (!$event instanceof Event) {
            throw new DomainException(
                sprintf('The event %s given does not exist', $command->eventId)
            );
        }

        if ($command->isPhaseFinalized() && $command->isStatusSuccess()) {
            $file = new File($command->output, $this->dateTime);
            $this->fileRepository->add($file);

            $this->mailer->send(
                new PrintPdfMail(
                    $event,
                    $this->emailSender,
                    $command->email,
                    $command->locale,
                    $file->getHash(),
                    $file->getId()
                )
            );

            $this->fileStorage->remove($command->input, true);
            $htmlFile = $this->fileRepository->getById($command->inputFileId);

            if ($htmlFile instanceof File) {
                $this->fileRepository->remove($htmlFile);
            }

            return;
        }

        if ($command->isPhaseFinalized() && $command->isStatusFailure()) {
            // Notify the user and then throw an exception
            $this->mailer->send(new ErrorPrintMail(
                $event,
                $this->emailSender,
                $command->email,
                $command->locale
            ));

            throw new PrintPdfErrorException('An error occurred during the print of sheets');
        }
    }
}
