<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class TrackingUrlTransformer
{
    /** @var RouterInterface */
    private $router;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        RouterInterface $router,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->router = $router;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function transform(Sheet $sheet, TemplateObject\Url $object): string
    {
        $locale = $this->router->getContext()->getParameter('_locale');
        if (empty($locale)) {
            $locale = $sheet->getEvent()->getLocaleFallback();
        }

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            'event_catalog_sheet_follow_link',
            ['sheet' => $sheet->getId(), 'objectId' => $object->getUid(), '_locale' => $locale]
        );
    }
}
