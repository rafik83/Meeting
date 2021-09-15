<?php

namespace Proximum\Vimeet\Domain\Model;

interface BillingInfoInterface
{
    /**
     * @return array
     */
    public function getBillingData();

    /**
     * @return array
     *
     * @deprecated to be rewritten to return a Proximum\Vimeet\Domain\Model\Template\AbstractTemplate object
     */
    public function getBillingTemplate();
}
