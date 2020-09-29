<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class TrackingUrlTransformer
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function transform(Sheet $sheet, TemplateObject\Url $object): string
    {
        $locale = $this->router->getContext()->getParameter('_locale');
        if (empty($locale)) {
            $locale = $sheet->getEvent()->getLocaleFallback();
        }

        return $this->router->generateAbsoluteUrl(
            'event_catalog_sheet_follow_link',
            ['sheet' => $sheet->getId(), 'objectId' => $object->getUid(), '_locale' => $locale]
        );
    }
}
