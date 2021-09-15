<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Planner\Import;
use Proximum\Vimeet\Application\Command\Planner\ImportHandler;
use Proximum\Vimeet\Application\Exception\Planner\InvalidXmlException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\Error\ImportPlannerMailError;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportPlannerCommand extends Command
{
    const NAME = 'vimeet:planner:import';

    /** @var ImportHandler */
    private $importPlannerHandler;

    /** @var MailerInterface */
    private $mailer;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var string */
    private $mailSender;

    /**
     * ImportPlannerCommand constructor.
     *
     * @param ImportHandler            $importPlannerHandler
     * @param MailerInterface          $mailer
     * @param EventRepositoryInterface $eventRepository
     * @param FileRepositoryInterface  $fileRepository
     * @param string                   $mailSender
     */
    public function __construct(
        ImportHandler $importPlannerHandler,
        MailerInterface $mailer,
        EventRepositoryInterface $eventRepository,
        FileRepositoryInterface $fileRepository,
        $mailSender
    ) {
        parent::__construct(self::NAME);

        $this->importPlannerHandler = $importPlannerHandler;
        $this->mailer               = $mailer;
        $this->eventRepository      = $eventRepository;
        $this->fileRepository       = $fileRepository;
        $this->mailSender           = $mailSender;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Import xml planner file for algorithm')
            ->addArgument('file', InputArgument::REQUIRED, 'File id')
            ->addArgument('event', InputArgument::REQUIRED, 'Event id')
            ->addArgument('admin_email', InputArgument::REQUIRED, 'Admin email to notify')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale')
            ->addArgument('plannerJobId', InputArgument::OPTIONAL, 'plannerJob id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments        = $input->getArguments();
        $errorMailOptions = [
            'event'        => $arguments['event'],
            'emailToNotify' => $arguments['admin_email'],
            'locale'       => $arguments['locale'],
        ];

        $event = $this->eventRepository->getById($arguments['event']);
        $file  = $this->fileRepository->getById($arguments['file']);

        if (null === $event || null === $file) {
            $object = (null === $event) ? 'event' : 'file';
            $this->notifyAdminAboutError(sprintf('%s %s not found', $object, $arguments[$object]), $errorMailOptions);

            return;
        }

        try {
            $this->importPlannerHandler->handle(
                new Import(
                    $file,
                    $event,
                    $arguments['admin_email'],
                    $arguments['locale'],
                    (int) $arguments['plannerJobId']
                )
            );
        } catch (InvalidXmlException $exception) {
            $this->notifyAdminAboutError($exception->getMessage(), $errorMailOptions);

            return;
        }
    }

    /**
     * @param string $errorMessage
     * @param array  $options
     */
    private function notifyAdminAboutError($errorMessage, array $options)
    {
        $this->mailer->send(new ImportPlannerMailError(
            $options['event'],
            $this->mailSender,
            $options['emailToNotify'],
            $options['locale'],
            $errorMessage
        ));
    }
}
