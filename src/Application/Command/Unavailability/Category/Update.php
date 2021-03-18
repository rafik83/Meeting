<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Category;

use Proximum\Vimeet\Domain\Model\Unavailability\Category;

class Update
{
    /**
     * @var string
     */
    public $picto;

    /**
     * @var string
     */
    public $title;

    /**
     * @var Category
     */
    public $category;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category   = $category;
        $this->title      = $category->getTitle();
        $this->picto      = $category->getPicto();
        $this->leftColor  = $category->getLeftColor();
        $this->rightColor = $category->getRightColor();
    }
}
