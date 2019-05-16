<?php

namespace Proximum\Vimeet\Application\Adapter;

interface InvoicesPdfBulkPrinterInterface
{
    /**
     * Return pdf path
     *
     * @param int[] $sheetIds
     *
     * @return string
     */
    public function generate(array $sheetIds): string;
}
