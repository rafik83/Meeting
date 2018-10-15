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
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class DownloadEbadgeCTASubstitution implements SubstituteInterface
{
    /** @var EngineInterface */
    private $engine;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var UserEventTokenGenerator */
    private $userEventTokenGenerator;

    public function __construct(
        EngineInterface $engine,
        EventUrlGeneratorInterface $eventUrlGenerator,
        UserEventTokenGenerator $userEventTokenGenerator
    ) {
        $this->engine = $engine;
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->userEventTokenGenerator = $userEventTokenGenerator;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $userEventToken = $this->userEventTokenGenerator->getUserEventTokenForConfirmAgenda(
            $prepareMail->event,
            $prepareMail->user,
            UserEventTokenType::EBADGE_CONFIRMATION
        );

        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $prepareMail->event,
            Route::BADGE_DOWNLOAD,
            [
                '_locale' => $prepareMail->event->getAvailableLocale($prepareMail->locale),
                'token' => $userEventToken->getToken(),
                'format' => 'pdf',
            ]
        );

        return $this->engine->render(
            'MailBundle:Mail:CTA/cta.html.twig',
            [
                'link' => $link,
                'label' => 'mail.download.ebadge.link',
                'locale' => $prepareMail->locale,
            ]
        );
    }
}
