<?php

namespace Proximum\Vimeet\Domain\Repository\Payment;

use Proximum\Vimeet\Domain\Model\Payment\Payment;

interface PaymentRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|Payment
     */
    public function findById($id);
}
