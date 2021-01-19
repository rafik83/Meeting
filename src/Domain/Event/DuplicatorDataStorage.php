<?php

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;

/**
 * Store duplicated data indexed by id of the same elements from the target event
 */
class DuplicatorDataStorage
{
    /**
     * @var Product[]
     */
    public $products = [];

    /**
     * @var Nomenclature[]
     */
    public $nomenclatures = [];

    /**
     * @var Type[]
     */
    public $types = [];

    /**
     * @var Category[]
     */
    public $categories = [];

    /**
     * @var SheetTemplate[]
     */
    public $sheetTemplates = [];

    /**
     * @var Package[]
     */
    public $packageTemplates = [];

    /**
     * @var RegistrationTemplate[]
     */
    public $registrationTemplates = [];
}
