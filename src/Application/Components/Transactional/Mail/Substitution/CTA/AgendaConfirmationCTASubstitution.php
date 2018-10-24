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
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\AgendaConfirmationLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class AgendaConfirmationCTASubstitution implements SubstituteInterface
{
    /** @var EngineInterface */
    private $engine;

    /** @var AgendaConfirmationLinkSubstitution */
    private $agendaConfirmationLinkSubstitution;

    public function __construct(
        EngineInterface $engine,
        AgendaConfirmationLinkSubstitution $agendaConfirmationLinkSubstitution
    ) {
        $this->engine = $engine;
        $this->agendaConfirmationLinkSubstitution = $agendaConfirmationLinkSubstitution;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.confirm.agenda.link',
            'link' => $this->agendaConfirmationLinkSubstitution->substitute($prepareMail),
            'locale' => $prepareMail->locale,
        ]);
    }
}
