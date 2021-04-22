<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class Export implements Command
{
    /** @var bool */
    public $lockMeetingRequest;

    /** @var string one of SolutionType constants */
    public $solutionType;

    /** @var int */
    public $eventId;

    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /** @var bool */
    public $isModeAuto;

    /** @var int|null */
    public $plannerJobId;

    /**
     * @param int      $eventId
     * @param string   $locale
     * @param string   $emailToNotify
     * @param bool     $lockMeetingRequest
     * @param string   $solutionType
     * @param bool     $isModeAuto
     * @param int|null $plannerJobId
     */
    public function __construct(
        int $eventId,
        string $locale,
        string $emailToNotify,
        bool $lockMeetingRequest,
        string $solutionType,
        bool $isModeAuto,
        ?int $plannerJobId
    ) {
        if (!in_array($solutionType, ExportSolutionType::getExportSolutionTypes(), true)) {
            throw new \InvalidArgumentException('solutionType must be one of ExportSolutionType');
        }

        $this->eventId = $eventId;
        $this->locale = $locale;
        $this->emailToNotify = $emailToNotify;
        $this->lockMeetingRequest = $lockMeetingRequest;
        $this->solutionType = $solutionType;
        $this->isModeAuto = $isModeAuto;
        $this->plannerJobId = $plannerJobId;
    }
}
