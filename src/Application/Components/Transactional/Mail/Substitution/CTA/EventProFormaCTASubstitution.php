<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\EventProFormaLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class EventProFormaCTASubstitution implements SubstituteInterface
{
    /** @var EventProFormaLinkSubstitution */
    private $eventProFormaLinkSubstitution;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        EventProFormaLinkSubstitution $eventProFormaLinkSubstitution,
        TemplatingAdapterInterface $templating
    ) {
        $this->eventProFormaLinkSubstitution = $eventProFormaLinkSubstitution;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->eventProFormaLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.orderConfirm.linkToProForma',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
