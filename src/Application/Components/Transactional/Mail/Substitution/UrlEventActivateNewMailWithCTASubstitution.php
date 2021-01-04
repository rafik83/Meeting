<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\LinkUrlEventActivateNewMailWithCTASubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class UrlEventActivateNewMailWithCTASubstitution implements SubstituteInterface
{
    /** @var LinkUrlEventActivateNewMailWithCTASubstitution */
    private $linkUrlEventActivateNewMailWithCTASubstitution;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        LinkUrlEventActivateNewMailWithCTASubstitution $linkUrlEventActivateNewMailWithCTASubstitution,
        TemplatingAdapterInterface $templating
    ) {
        $this->linkUrlEventActivateNewMailWithCTASubstitution = $linkUrlEventActivateNewMailWithCTASubstitution;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->linkUrlEventActivateNewMailWithCTASubstitution->substitute($prepareMail);

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'label' => 'mail.changeMail.link',
            'link' => $link,
            'locale' => $prepareMail->locale
        ]);
    }
}
