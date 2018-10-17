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
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\ActivateAccountLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class ActivateAccountCTASubstitution implements SubstituteInterface
{
    /** @var ActivateAccountLinkSubstitution */
    private $activateAccountLinkSubstitution;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        ActivateAccountLinkSubstitution $activateAccountLinkSubstitution,
        EngineInterface $engine
    ) {
        $this->activateAccountLinkSubstitution = $activateAccountLinkSubstitution;
        $this->engine = $engine;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->activateAccountLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.activateAccount.activate',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
