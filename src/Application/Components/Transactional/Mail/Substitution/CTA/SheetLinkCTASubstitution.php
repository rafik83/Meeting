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
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\SheetLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class SheetLinkCTASubstitution implements SubstituteInterface
{
    /** @var SheetLinkSubstitution */
    private $sheetLinkSubstitution;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        SheetLinkSubstitution $sheetLinkSubstitution,
        EngineInterface $engine
    ) {
        $this->sheetLinkSubstitution = $sheetLinkSubstitution;
        $this->engine = $engine;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->sheetLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.participant.profile.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
