<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchValidate extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var Event
     */
    public $event;

    /**
     * BatchValidate constructor.
     *
     * @param Event  $event
     * @param array  $ids
     * @param Admin  $admin
     * @param string $comment
     */
    public function __construct(Event $event, array $ids, Admin $admin, $comment)
    {
        $this->ids     = $ids;
        $this->admin   = $admin;
        $this->comment = $comment;
        $this->event   = $event;
    }
}
