<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class TestVisioConfigurationCTASubstitution
{
    /** @var TemplatingAdapterInterface */
    private $templating;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        TemplatingAdapterInterface $templating,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->templating = $templating;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function getCTA(Sheet $sheet, string $locale)
    {
        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            'event_video_conference_create_network_test',
            [
                '_locale' => $locale
            ]
        );

        return $this->templating->render('MailBundle:Mail:CTA/testVisioConfiguration.html.twig', [
            'link' => $link,
            'locale' => $locale,
        ]);
    }
}
