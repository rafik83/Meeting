<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ApproveRequest implements Command
{
    /** @var Request */
    public $request;

    /** @var string */
    public $description;

    /** @var Participant[] */
    public $participants;

    /** @var User */
    public $editor;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var bool */
    public $toPriority = false;

    /**
     * @param User    $editor
     * @param Request $request
     * @param Sheet   $sheet
     * @param string  $locale
     */
    public function __construct(User $editor, Request $request, Sheet $sheet, string $locale)
    {
        $this->request      = $request;
        $this->participants = $request->getToParticipants()->toArray();
        $this->editor       = $editor;
        $this->sheet        = $sheet;
        $this->locale       = $locale;
    }
}
