<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\ParticipantAccountLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class ParticipantAccountCTASubstitution implements SubstituteInterface
{
    /** @var ParticipantAccountLinkSubstitution */
    private $participantAccountLinkSubstitution;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        ParticipantAccountLinkSubstitution $participantAccountLinkSubstitution,
        TemplatingAdapterInterface $templating
    ) {
        $this->participantAccountLinkSubstitution = $participantAccountLinkSubstitution;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->participantAccountLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.event.preregister.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
