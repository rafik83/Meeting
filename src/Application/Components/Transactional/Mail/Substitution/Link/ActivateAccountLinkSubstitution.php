<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;

class ActivateAccountLinkSubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    public function __construct(
        EventUrlGeneratorInterface $eventUrlGenerator,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator
    ) {
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->activateAccountTokenGenerator = $activateAccountTokenGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (method_exists($prepareMail, 'getActivateAccountToken')
            && $prepareMail->getActivateAccountToken() instanceof ActivateAccountToken
        ) {
            $token = $prepareMail->getActivateAccountToken()->getToken();

            return $this->eventUrlGenerator->generateEventAbsoluteUrl(
                $prepareMail->event,
                'event_activate_account',
                [
                    'token' => $token,
                    '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
                ]
            );
        }

        if ($prepareMail->user->isActive()) {
            return $this->eventUrlGenerator->generateEventAbsoluteUrl(
                $prepareMail->event,
                'event_login',
                ['_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale)]
            );
        }

        if (!$prepareMail->hasSheet()) {
            return '';
        }

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            'event_activate_account',
            [
                'token' => $this->activateAccountTokenGenerator->generate(
                    $prepareMail->user,
                    $prepareMail->sheet
                )->getToken(),
                '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
            ]
        );
    }
}
