<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class Import
{
    const UNSOLVED_SUFFIX = '.xml';
    const SOLVED_SUFFIX = '-solved.xml';

    /** @var File */
    public $file;

    /** @var Event */
    public $event;

    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /** @var int */
    public $plannerJobId;

    /**
     * @param File   $file
     * @param Event  $event
     * @param string $emailToNotify
     * @param string $locale
     * @param int    $plannerJobId
     */
    public function __construct(
        File $file,
        Event $event,
        string $emailToNotify,
        string $locale,
        int $plannerJobId
    ) {
        $this->file          = $file;
        $this->event         = $event;
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
        $this->plannerJobId  = $plannerJobId;
    }
}
