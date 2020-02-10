<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Token\ChangeMailTokenGenerator;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;

class LinkUrlEventActivateNewMailWithCTASubstitution implements SubstituteInterface
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var ChangeMailTokenGenerator */
    private $changeMailTokenGenerator;

    public function __construct(
        EventUrlGeneratorInterface $eventUrlGenerator,
        ChangeMailTokenGenerator $changeMailTokenGenerator
    )
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->changeMailTokenGenerator = $changeMailTokenGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (method_exists($prepareMail, 'getChangeMailToken')
            && $prepareMail->getChangeMailToken() instanceof ChangeMailToken
        ) {
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

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            'event_activate_new_mail',
            [
                'token' => $this->changeMailTokenGenerator->generate(
                    $prepareMail->user,
                    $prepareMail->user->getEmail()
                )->getToken(),
                '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
            ]
        );
    }
}
