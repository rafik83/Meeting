<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink;

class LeniBadgeLinkParametersView
{
    /** @var string */
    public $link;

    /** @var int[] */
    public $concernedTypeIds;

    public function __construct(string $link, $concernedTypeIds)
    {
        $this->link = $link;
        $this->concernedTypeIds = $concernedTypeIds;
    }
}
