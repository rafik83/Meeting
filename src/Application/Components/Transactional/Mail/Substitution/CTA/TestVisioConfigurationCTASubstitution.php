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
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\TestVisioConfigurationLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

class TestVisioConfigurationCTASubstitution implements SubstituteInterface
{
    /** @var EngineInterface */
    private $engine;

    /** @var TestVisioConfigurationLinkSubstitution */
    private $testVisioConfigurationLinkSubstitution;

    public function __construct(
        EngineInterface $engine,
        TestVisioConfigurationLinkSubstitution $testVisioConfigurationLinkSubstitution
    ) {
        $this->engine = $engine;
        $this->testVisioConfigurationLinkSubstitution = $testVisioConfigurationLinkSubstitution;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->testVisioConfigurationLinkSubstitution->substitute($prepareMail);

        return $this->engine->render('MailBundle:Mail:CTA/cta.html.twig', [
            'link' => $link,
            'label' => 'mail.confirm.test_visio_configuration.link',
            'locale' => $prepareMail->locale,
        ]);
    }
}
