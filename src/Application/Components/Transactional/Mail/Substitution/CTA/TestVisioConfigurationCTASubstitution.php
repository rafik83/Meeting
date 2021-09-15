<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\CTA;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\TestVisioConfigurationLinkSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class TestVisioConfigurationCTASubstitution implements SubstituteInterface
{
    /** @var TemplatingAdapterInterface */
    private $templating;

    /** @var TestVisioConfigurationLinkSubstitution */
    private $testVisioConfigurationLinkSubstitution;

    public function __construct(
        TemplatingAdapterInterface $templating,
        TestVisioConfigurationLinkSubstitution $testVisioConfigurationLinkSubstitution
    ) {
        $this->templating = $templating;
        $this->testVisioConfigurationLinkSubstitution = $testVisioConfigurationLinkSubstitution;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $link = $this->testVisioConfigurationLinkSubstitution->substitute($prepareMail);

        return $this->templating->render('MailBundle:Mail:CTA/cta.html.twig', [
            'link' => $link,
            'label' => 'mail.confirm.test_visio_configuration.link',
            'locale' => $prepareMail->locale,
        ]);
    }
}
