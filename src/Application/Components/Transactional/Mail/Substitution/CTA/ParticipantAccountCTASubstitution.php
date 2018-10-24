<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Adapter\EngineInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\ParticipantAccountLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class ParticipantAccountCTASubstitution implements SubstituteInterface
{
    /** @var ParticipantAccountLinkSubstitution */
    private $participantAccountLinkSubstitution;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        ParticipantAccountLinkSubstitution $participantAccountLinkSubstitution,
        EngineInterface $engine
    ) {
        $this->participantAccountLinkSubstitution = $participantAccountLinkSubstitution;
        $this->engine = $engine;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->participantAccountLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.event.preregister.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
