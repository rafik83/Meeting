<?php

namespace Proximum\Vimeet\Domain\Order\Row;

use Proximum\Vimeet\Domain\Model\Order;

class ProductIncludedInfoGuesser
{
    /**
     * @param Order\Row $row
     * @param string    $locale
     *
     * @return array
     */
    public function getProductIncludedInfo(Order\Row $row, $locale)
    {
        $productsInfo = [];
        if (!$row->hasIncludedProduct()) {
            return $productsInfo;
        }

        $data = json_decode($row->getData(), true);

        foreach ($data['productsIncluded'] as $includedProduct) {
            $label = '';

            if (isset($includedProduct['included']['translations'][$locale])
                && isset($includedProduct['included']['translations'][$locale]['title'])
            ) {
                $label = $includedProduct['included']['translations'][$locale]['title'];
            }

            $productsInfo[] = [
                'id'       => $includedProduct['included']['id'],
                'quantity' => $includedProduct['quantity'],
                'price'    => 0,
                'label'    => $label,
            ];
        }

        return $productsInfo;
    }
}
