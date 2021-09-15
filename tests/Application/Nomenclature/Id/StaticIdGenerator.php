<?php

namespace Proximum\Vimeet\Tests\Application\Nomenclature\Id;

use Proximum\Vimeet\Application\Nomenclature\Id\IdGeneratorInterface;

class StaticIdGenerator implements IdGeneratorInterface
{
    /**
     * @var array
     */
    private $ids;

    /**
     * @var array
     */
    private $bak;

    /**
     * StaticIdGenerator constructor.
     *
     * @param array $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
        $this->bak = $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if (empty($this->ids)) {
            $this->ids = $this->bak;
        }

        return array_shift($this->ids);
    }
}
