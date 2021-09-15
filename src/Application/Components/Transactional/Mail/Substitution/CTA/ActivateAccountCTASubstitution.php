<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\ActivateAccountLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class ActivateAccountCTASubstitution implements SubstituteInterface
{
    /** @var ActivateAccountLinkSubstitution */
    private $activateAccountLinkSubstitution;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        ActivateAccountLinkSubstitution $activateAccountLinkSubstitution,
        TemplatingAdapterInterface $templating
    ) {
        $this->activateAccountLinkSubstitution = $activateAccountLinkSubstitution;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->activateAccountLinkSubstitution->substitute($prepareMail);

        if (empty($link)) {
            return '';
        }

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.activateAccount.activate',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
