<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\AgendaConfirmationLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class AgendaConfirmationCTASubstitution implements SubstituteInterface
{
    /** @var TemplatingAdapterInterface */
    private $templating;

    /** @var AgendaConfirmationLinkSubstitution */
    private $agendaConfirmationLinkSubstitution;

    public function __construct(
        TemplatingAdapterInterface $templating,
        AgendaConfirmationLinkSubstitution $agendaConfirmationLinkSubstitution
    ) {
        $this->templating = $templating;
        $this->agendaConfirmationLinkSubstitution = $agendaConfirmationLinkSubstitution;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.confirm.agenda.link',
            'link' => $this->agendaConfirmationLinkSubstitution->substitute($prepareMail),
            'locale' => $prepareMail->locale,
        ]);
    }
}
