<?php

namespace Proximum\Vimeet\Application\View\Meeting\Message;

use Proximum\Vimeet\Domain\Model\Sheet;

class MessageMeetingRequestView
{
    const RIGHT = 'right';
    const LEFT  = 'left';

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var string
     */
    public $sheetName;

    /**
     * @var string
     */
    public $side;

    /**
     * @var string
     */
    public $content;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param Sheet              $sheet
     * @param string             $sheetName
     * @param string             $content
     * @param \DateTimeInterface $createdAt
     * @param string             $side
     */
    public function __construct(Sheet $sheet, $sheetName, $content, \DateTimeInterface $createdAt, $side)
    {
        $this->sheet     = $sheet;
        $this->sheetName = $sheetName;
        $this->content   = $content;
        $this->createdAt = $createdAt;
        $this->side      = $side;
    }

    /**
     * @return bool
     */
    public function isLeft()
    {
        return self::LEFT === $this->side;
    }
}
