<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\EventActivateAccountAlreadyKnownLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class EventActivateAccountAlreadyKnownCTASubstitution implements SubstituteInterface
{
    /** @var EventActivateAccountAlreadyKnownLinkSubstitution */
    private $eventActivateAccountAlreadyKnownLinkSubstitution;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        EventActivateAccountAlreadyKnownLinkSubstitution $eventActivateAccountAlreadyKnownLinkSubstitution,
        TemplatingAdapterInterface $templating
    ) {
        $this->eventActivateAccountAlreadyKnownLinkSubstitution = $eventActivateAccountAlreadyKnownLinkSubstitution;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->eventActivateAccountAlreadyKnownLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.completeProfile.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
