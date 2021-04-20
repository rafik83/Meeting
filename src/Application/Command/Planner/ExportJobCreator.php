<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ExportJobCreator implements Command
{
    const MODE_AUTO = 'auto';
    const MODE_MANUAL = 'manual';

    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    /** @var bool */
    public $lockMeetingRequest = false;

    /** @var string one of SolutionType constants */
    public $solutionType;

    /** @var string */
    public $mode;

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     * @param string $mode
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Event $event, Admin $admin, string $locale, string $mode)
    {
        if (!in_array($mode, [self::MODE_AUTO, self::MODE_MANUAL], true)) {
            throw new \InvalidArgumentException('The mode must be manual or auto');
        }

        $this->event  = $event;
        $this->admin  = $admin;
        $this->locale = $locale;
        $this->mode   = $mode;
    }

    /**
     * @return bool
     */
    public function isModeAuto(): bool
    {
        return self::MODE_AUTO === $this->mode;
    }
}
