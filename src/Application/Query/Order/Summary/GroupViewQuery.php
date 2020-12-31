<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\RowView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class GroupViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var null|int
     */
    public $groupId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var null|RowView
     */
    public $planView;

    /**
     * @var null|Step
     */
    public $step;

    /**
     * @param Sheet        $sheet
     * @param null|Step    $step
     * @param Order        $order
     * @param string       $locale
     * @param string       $type
     * @param int|null     $groupId
     * @param RowView|null $planView
     */
    public function __construct(
        Sheet $sheet,
        Step $step = null,
        Order $order,
        $locale,
        $type,
        $groupId = null,
        RowView $planView = null
    ) {
        $this->sheet    = $sheet;
        $this->step     = $step;
        $this->order    = $order;
        $this->locale   = $locale;
        $this->type     = $type;
        $this->groupId  = $groupId;
        $this->planView = $planView;
    }
}
