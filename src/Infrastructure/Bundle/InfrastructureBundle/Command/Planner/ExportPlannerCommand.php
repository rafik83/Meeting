<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner;

use Proximum\Vimeet\Application\Command\Planner\Export;
use Proximum\Vimeet\Application\Command\Planner\ExportHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportPlannerCommand extends Command
{
    public const NAME = 'vimeet:planner:export';

    public const LOCK_MEETING_REQUEST = 'lock-requests';
    public const DONT_LOCK_MEETING_REQUEST = 'not-lock-requests';

    public const MODE_AUTO = 'auto';
    public const MODE_MANUAL = 'manual';

    /** @var ExportHandler */
    private $exportPlannerHandler;

    public function __construct(ExportHandler $exportPlannerHandler)
    {
        parent::__construct(self::NAME);

        $this->exportPlannerHandler = $exportPlannerHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate xml file for planner export')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
            ->addArgument('admin_email', InputArgument::REQUIRED, 'Admin email to notify')
            ->addArgument('locale', InputArgument::REQUIRED, 'Locale for the email')
            ->addArgument('solutionType', InputArgument::REQUIRED, 'Solution type to prepare for algorithm')
            ->addArgument(
                'lockMeetingRequest',
                InputArgument::REQUIRED,
                'Should meeting request be locked after export'
            )
            ->addArgument('mode', InputArgument::REQUIRED, 'Mode: auto or manual')
            ->addArgument('plannerJob', InputArgument::OPTIONAL, 'plannerJob id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $mode = $input->getArgument('mode');

        if (!in_array($mode, [self::MODE_AUTO, self::MODE_MANUAL], true)) {
            throw new \InvalidArgumentException('Mode must be auto or manual');
        }

        $plannerJob = $input->getArgument('plannerJob');

        if ('' === $plannerJob) {
            $plannerJob = null;
        }

        $result = $this->exportPlannerHandler->handle(
            new Export(
                $input->getArgument('eventId'),
                $input->getArgument('locale'),
                $input->getArgument('admin_email'),
                self::LOCK_MEETING_REQUEST === $input->getArgument('lockMeetingRequest'),
                $input->getArgument('solutionType'),
                self::MODE_AUTO === $mode,
                $plannerJob
            )
        );

        $output->writeln($result);

        return 0;
    }
}
