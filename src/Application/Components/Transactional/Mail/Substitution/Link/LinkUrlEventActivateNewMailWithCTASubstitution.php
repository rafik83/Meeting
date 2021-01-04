<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;

class LinkUrlEventActivateNewMailWithCTASubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if ($prepareMail->getChangeMailToken() instanceof ChangeMailToken) {
            $token = $prepareMail->getChangeMailToken()->getToken();

            return $this->eventUrlGenerator->generateEventAbsoluteUrl(
                $prepareMail->event,
                'event_activate_new_mail',
                [
                    'token' => $token,
                    '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
                ]
            );
        }

        return '';
    }
}
