<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\AgendaLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class AgendaLinkCTASubstitution implements SubstituteInterface
{
    /** @var AgendaLinkSubstitution */
    private $agendaLinkSubstitution;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        AgendaLinkSubstitution $agendaLinkSubstitution,
        TemplatingAdapterInterface $templating
    ) {
        $this->agendaLinkSubstitution = $agendaLinkSubstitution;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->agendaLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.event.agenda.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
