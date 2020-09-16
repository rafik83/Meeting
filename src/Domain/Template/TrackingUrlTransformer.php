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
        return $this->router->generate(
            'event_catalog_sheet_follow_link',
            ['sheet' => $sheet->getId(), 'objectId' => $object->getUid()]
        );
    }
}
