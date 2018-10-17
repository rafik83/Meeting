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
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\EventProFormaLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class EventProFormaCTASubstitution implements SubstituteInterface
{
    /** @var EventProFormaLinkSubstitution */
    private $eventProFormaLinkSubstitution;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        EventProFormaLinkSubstitution $eventProFormaLinkSubstitution,
        EngineInterface $engine
    ) {
        $this->eventProFormaLinkSubstitution = $eventProFormaLinkSubstitution;
        $this->engine = $engine;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->eventProFormaLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.orderConfirm.linkToProForma',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
