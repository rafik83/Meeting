<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareChangeNewMailAccountView extends AbstractPrepareMail
{
    /** @var ChangeMailToken */
    public $changeMailToken;



    public function __construct(
        Event $event,
        User $user,
        string $locale,
        ChangeMailToken $changeMailToken
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGE_NEW_MAIL,
            $locale,
            null
        );

        $this->changeMailToken = $changeMailToken;
    }

    /**
     * @return ChangeMailToken
     */
    public function getChangeMailToken(): ChangeMailToken
    {
        return $this->changeMailToken;
    }
}
