<?php

namespace Proximum\Vimeet\Application\Command\Planner\Callback;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Planner\Import;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class SetStatusHandler
{
    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var string */
    private $plannerTrustedName;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var string */
    private $plannerFilesPath;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param PlannerJobRepositoryInterface $plannerJobRepository
     * @param FileRepositoryInterface       $fileRepository
     * @param string                        $plannerTrustedName
     * @param JobQueueInterface             $jobQueue
     * @param string                        $plannerFilesPath
     * @param EventOpenAccessChecker        $eventOpenAccessChecker
     * @param FileSystemAdapterInterface    $fileSystemAdapter
     * @param \DateTimeInterface            $dateTime
     */
    public function __construct(
        PlannerJobRepositoryInterface $plannerJobRepository,
        FileRepositoryInterface $fileRepository,
        string $plannerTrustedName,
        JobQueueInterface $jobQueue,
        string $plannerFilesPath,
        EventOpenAccessChecker $eventOpenAccessChecker,
        FileSystemAdapterInterface $fileSystemAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->plannerJobRepository = $plannerJobRepository;
        $this->fileRepository = $fileRepository;
        $this->plannerTrustedName = $plannerTrustedName;
        $this->jobQueue = $jobQueue;
        $this->plannerFilesPath = $plannerFilesPath;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->dateTime = $dateTime;
    }

    /**
     * @param SetStatus $setStatus
     */
    public function handle(SetStatus $setStatus)
    {
        if ($this->plannerTrustedName !== $setStatus->name) {
            throw new \InvalidArgumentException(sprintf('Given build name %s is not trusted', $setStatus->name));
        }

        $plannerJob = $this->plannerJobRepository->findByFilename($setStatus->filepath);

        if (null === $plannerJob) {
            throw new \InvalidArgumentException(sprintf('PlannerJob not found with file: %s', $setStatus->filepath));
        }

        if ($setStatus->isPhaseCompleted()) {
            // do nothing
            return;
        }

        if ($setStatus->isPhaseFinalized() && $setStatus->isStatusSuccess()) {
            $this->handleSuccess($plannerJob);
        } elseif ($setStatus->isPhaseQueued()) {
            $plannerJob->setQueued();
        } elseif ($setStatus->isPhaseStarted()) {
            $plannerJob->setStarted();
        } elseif ($setStatus->isPhaseFinalized() && $setStatus->isStatusFailure()) {
            $plannerJob->setError('flash.admin.planner.export.plannerError');
        } elseif ($setStatus->isPhaseFinalized() && $setStatus->isStatusAborted()) {
            $plannerJob->setAborted();
        }

        $this->plannerJobRepository->set($plannerJob);
    }

    /**
     * @param PlannerJob $plannerJob
     */
    private function handleSuccess(PlannerJob $plannerJob)
    {
        $isEventOpened = $this->eventOpenAccessChecker->allowedToAccess($plannerJob->getEvent());

        if (true === $isEventOpened) {
            $plannerJob->setError('flash.admin.planner.export.eventIsAlreadyOpened');

            return;
        }

        $solvedFilePath = str_replace(
            Import::UNSOLVED_SUFFIX,
            Import::SOLVED_SUFFIX,
            $plannerJob->getFile()->getPath()
        );

        if (!$this->fileSystemAdapter->exists($this->plannerFilesPath . $solvedFilePath)) {
            $plannerJob->setError('flash.admin.planner.export.solvedFileNotFound');

            return;
        }

        $file = new File($solvedFilePath, $this->dateTime);
        $this->fileRepository->add($file);

        $this->jobQueue->importPlannerForEvent(
            $file,
            $plannerJob->getEvent(),
            $plannerJob->getAdmin(),
            $plannerJob->getAdmin()->getLocale(),
            $plannerJob
        );

        $plannerJob->setSuccess();
    }
}
