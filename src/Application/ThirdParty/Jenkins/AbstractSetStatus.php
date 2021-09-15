<?php

namespace Proximum\Vimeet\Application\ThirdParty\Jenkins;

/**
 * A sample of the payload received from Jenkins:
 *     {
 *         "name": "BUILD_NAME",
 *         "display_name": "BUILD_NAME",
 *         "url": "job/BUILD_NAME/",
 *         "build": {
 *             "full_url": "http://host/job/BUILD_NAME/10/",
 *             "number": 10,
 *             "queue_id": 116,
 *             "timestamp": 1505481815678,
 *             "phase": "FINALIZED",
 *             "status": "SUCCESS",
 *             "url": "job/BUILD_NAME/10/",
 *             "scm": {},
 *             "parameters": {
 *                  "INPUT": "export_planner_99_2017_09_15_122018.xml"
 *             },
 *             "log": "",
 *             "artifacts": {}
 *         }
 *     }
 *
 * The different couples of phase / status are:
 *     "phase": "FINALIZED"/"status": "ABORTED",
 *     "phase": "COMPLETED"/"status": "ABORTED",
 *     "phase": "STARTED"
 *     "phase": "QUEUED"
 *     "phase": "FINALIZED"/ "status": "FAILURE",
 *     "phase": "COMPLETED"/"status": "FAILURE",
 *     "phase": "FINALIZED"/"status": "SUCCESS",
 *     "phase": "COMPLETED"/"status": "SUCCESS"
 */
abstract class AbstractSetStatus
{
    const PHASE_FINALIZED = 'FINALIZED';
    const PHASE_COMPLETED = 'COMPLETED';
    const PHASE_STARTED = 'STARTED';
    const PHASE_QUEUED = 'QUEUED';

    const PHASE_ALL = [
        self::PHASE_FINALIZED,
        self::PHASE_COMPLETED,
        self::PHASE_STARTED,
        self::PHASE_QUEUED,
    ];

    const STATUS_ABORTED = 'ABORTED';
    const STATUS_FAILURE = 'FAILURE';
    const STATUS_SUCCESS = 'SUCCESS';

    const STATUS_ALL = [
        self::STATUS_ABORTED,
        self::STATUS_FAILURE,
        self::STATUS_SUCCESS,
    ];

    /** @var string */
    public $name;

    /** @var string */
    public $phase;

    /** @var null|string */
    public $status;

    /**
     * @param array $data
     *
     * @throw \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $build = $data['build'];

        if (!in_array($build['phase'], self::PHASE_ALL, true)) {
            throw new \InvalidArgumentException(sprintf('PHASE %s not valid.', $build['phase']));
        }

        $this->phase = $build['phase'];

        if (isset($build['status'])) {
            if (!in_array($build['status'], self::STATUS_ALL, true)) {
                throw new \InvalidArgumentException(sprintf('STATUS %s not valid.', $build['status']));
            }

            $this->status = $build['status'];
        }
    }

    public function isPhaseFinalized(): bool
    {
        return self::PHASE_FINALIZED === $this->phase;
    }

    public function isPhaseCompleted(): bool
    {
        return self::PHASE_COMPLETED === $this->phase;
    }

    public function isPhaseStarted(): bool
    {
        return self::PHASE_STARTED === $this->phase;
    }

    public function isPhaseQueued(): bool
    {
        return self::PHASE_QUEUED === $this->phase;
    }

    public function isStatusSuccess(): bool
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    public function isStatusAborted(): bool
    {
        return self::STATUS_ABORTED === $this->status;
    }

    public function isStatusFailure(): bool
    {
        return self::STATUS_FAILURE === $this->status;
    }
}
