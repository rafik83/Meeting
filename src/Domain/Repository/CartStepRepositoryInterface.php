<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Sheet;

interface CartStepRepositoryInterface
{
    /**
     * @param CartStep $cartStep
     */
    public function add(CartStep $cartStep);

    /**
     * @param CartStep $cartStep
     */
    public function set(CartStep $cartStep);

    /**
     * @param Sheet $sheet
     *
     * @return null|CartStep
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param CartStep $cartStep
     */
    public function delete(CartStep $cartStep);

    /**
     * @param Sheet $sheet
     */
    public function deleteForSheet(Sheet $sheet);
}
