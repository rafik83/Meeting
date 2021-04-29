<?php

namespace Proximum\Vimeet\Application\Command\Planner\Callback;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\AbstractSetStatus;

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
class SetStatus extends AbstractSetStatus implements Command
{
    /** @var string */
    public $filepath;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->filepath = $data['build']['parameters']['INPUT'];
    }
}
