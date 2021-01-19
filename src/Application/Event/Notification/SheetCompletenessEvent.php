<?php

namespace Proximum\Vimeet\Application\Event\Notification;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class SheetCompletenessEvent extends Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * An array of locale => bool
     *
     * @var array
     */
    private $notificationCompleteness;

    /**
     * SheetCompletenessEvent constructor.
     *
     * @param Sheet $sheet
     * @param array $notificationCompleteness
     */
    public function __construct(Sheet $sheet, $notificationCompleteness)
    {
        $this->sheet                    = $sheet;
        $this->notificationCompleteness = $notificationCompleteness;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Return an array of locale => bool
     *
     * @return array
     */
    public function getNotificationCompleteness()
    {
        return $this->notificationCompleteness;
    }
}
