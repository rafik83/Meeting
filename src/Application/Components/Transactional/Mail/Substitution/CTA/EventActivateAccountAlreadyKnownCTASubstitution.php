<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Adapter\EngineInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\EventActivateAccountAlreadyKnownLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class EventActivateAccountAlreadyKnownCTASubstitution implements SubstituteInterface
{
    /** @var EventActivateAccountAlreadyKnownLinkSubstitution */
    private $eventActivateAccountAlreadyKnownLinkSubstitution;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        EventActivateAccountAlreadyKnownLinkSubstitution $eventActivateAccountAlreadyKnownLinkSubstitution,
        EngineInterface $engine
    ) {
        $this->eventActivateAccountAlreadyKnownLinkSubstitution = $eventActivateAccountAlreadyKnownLinkSubstitution;
        $this->engine = $engine;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->eventActivateAccountAlreadyKnownLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.completeProfile.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
