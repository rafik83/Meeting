<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemTranslationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;

/**
 * Example of NomenclatureItem:
 *   {
 *     +"reference": "21300"
 *     +"code": "21300"
 *     +"nomenclatureLibTrad": array:2 [
 *       0 => {
 *         +"referenceLangue": "FRA"
 *         +"traduction": "Rouleaux vibrants (autres) (Matériels et Engins de terrassement et génie civil)"
 *       }
 *       1 => {
 *         +"referenceLangue": "GBR"
 *         +"traduction": "Vibrating rollers (other) (Machines & equipment for earthmoving and civil engineering)"
 *       }
 *     ]
 *     +"referenceNomenclatureManifestationParent": "21260"
 *     +"supprime": false
 *   }
 */
class RawNomenclatureToNomenclatureViewConverter extends ComexposiumConverter
{
    /**
     * @see getParametragesCatalogue wsdl:
     *     http://webservices.comexposium-admin.com/catalogue-ws-v2/parametragecataloguews.wsdl
     *
     * @param \stdClass $nomenclatureItem
     *
     * @return null|NomenclatureItemView
     */
    public function convert(\stdClass $nomenclatureItem): ?NomenclatureItemView
    {
        if (!isset($nomenclatureItem->reference, $nomenclatureItem->nomenclatureLibTrad)
            || !\is_array($nomenclatureItem->nomenclatureLibTrad)
        ) {
            return null;
        }

        $nomenclatureItemTranslationViews = [];

        foreach ($nomenclatureItem->nomenclatureLibTrad as $translation) {
            $locale = $this->convertLocale($translation->referenceLangue);

            $nomenclatureItemTranslationViews[$locale] = new NomenclatureItemTranslationView(
                $translation->traduction,
                $locale
            );
        }

        return new NomenclatureItemView(
            $nomenclatureItem->reference,
            $nomenclatureItem->referenceNomenclatureManifestationParent ?? null,
            $nomenclatureItemTranslationViews
        );
    }
}
