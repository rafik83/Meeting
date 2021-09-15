<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareActivateAccountMailView extends AbstractPrepareMail
{
    /** @var User\ActivateAccountToken */
    private $activateAccountToken;

    public function __construct(
        Event $event,
        User $user,
        string $locale,
        User\ActivateAccountToken $activateAccountToken
    ) {
        parent::__construct($event, $user, Constant::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT, $locale, null);

        $this->activateAccountToken = $activateAccountToken;
    }

    /**
     * @return User\ActivateAccountToken
     */
    public function getActivateAccountToken(): User\ActivateAccountToken
    {
        return $this->activateAccountToken;
    }
}
