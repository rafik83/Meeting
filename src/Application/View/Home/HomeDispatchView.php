<?php

namespace Proximum\Vimeet\Application\View\Home;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class HomeDispatchView
{
    const TYPE_GROUP                 = 'group';
    const TYPE_ONE_SHEET             = 'one_sheet';
    const TYPE_MULTIPLE_SHEETS       = 'multiple_sheets';

    /** @var string */
    private $type;

    /** @var null|Sheet */
    private $sheet;

    /** @var null|Group */
    private $group;

    /**
     * @param string           $type
     * @param null|Group|Sheet $object
     */
    public function __construct($type, $object = null)
    {
        if (false === $this->isGivenTypeValid($type)) {
            throw new \InvalidArgumentException('Given type is invalid');
        }

        if (self::TYPE_GROUP === $type && !$object instanceof Group) {
            throw new \InvalidArgumentException('A Group object must be provided');
        }

        if (self::TYPE_ONE_SHEET === $type && !$object instanceof Sheet) {
            throw new \InvalidArgumentException('A Sheet object must be provided');
        }

        $this->type = $type;

        if ($object instanceof Group) {
            $this->group = $object;
        } elseif ($object instanceof Sheet) {
            $this->sheet = $object;
        }
    }

    /**
     * @return bool
     */
    public function isGroup()
    {
        return self::TYPE_GROUP === $this->type;
    }

    /**
     * @return bool
     */
    public function isOneSheet()
    {
        return self::TYPE_ONE_SHEET === $this->type;
    }

    /**
     * @return bool
     */
    public function isMultipleSheet()
    {
        return self::TYPE_MULTIPLE_SHEETS === $this->type;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        if (!$this->group instanceof Group) {
            throw new \LogicException('getGroup() method cannot be called in this context');
        }

        return $this->group;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        if (!$this->sheet instanceof Sheet) {
            throw new \LogicException('getSheet() method cannot be called in this context');
        }

        return $this->sheet;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isGivenTypeValid(string $type): bool
    {
        return in_array(
            $type,
            [
                self::TYPE_GROUP,
                self::TYPE_ONE_SHEET,
                self::TYPE_MULTIPLE_SHEETS,
            ]
        );
    }
}
