<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareSheetChangeTypeMailView extends AbstractPrepareMail
{
    /** @var string|null */
    public $fromTypeTitle;

    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        string $locale,
        ?string $fromTypeTitle
    ) {
        parent::__construct($event, $user, Constant::TRANSACTIONAL_MAIL_KEY_SHEET_TYPE_CHANGED, $locale, $sheet);

        $this->fromTypeTitle = $fromTypeTitle;
    }
}
