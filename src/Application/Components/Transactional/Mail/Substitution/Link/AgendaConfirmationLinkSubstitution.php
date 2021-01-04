<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class AgendaConfirmationLinkSubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var UserEventTokenGenerator */
    private $userEventTokenGenerator;

    public function __construct(
        EventUrlGeneratorInterface $eventUrlGenerator,
        UserEventTokenGenerator $userEventTokenGenerator
    ) {
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->userEventTokenGenerator = $userEventTokenGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $userEventToken = $this->userEventTokenGenerator->getUserEventTokenForConfirmAgenda(
            $prepareMail->event,
            $prepareMail->user,
            UserEventTokenType::AGENDA_CONFIRMATION
        );

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            Route::AGENDA_CONFIRMATION,
            [
                '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
                'token' => $userEventToken->getToken(),
            ]
        );
    }
}
