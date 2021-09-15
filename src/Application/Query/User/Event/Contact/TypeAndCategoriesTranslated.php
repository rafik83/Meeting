<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class TypeAndCategoriesTranslated
{
    /** @var string */
    private $typeTitle;

    /** @var array */
    private $categoriesTitle;

    public function __construct(string $typeTitle, array $categoriesTitle)
    {
        $this->typeTitle = $typeTitle;
        $this->categoriesTitle = $categoriesTitle;
    }

    public function getTypeTitle(): string
    {
        return $this->typeTitle;
    }

    /**
     * @return string[]
     */
    public function getCategoriesTitle(): array
    {
        return $this->categoriesTitle;
    }
}
