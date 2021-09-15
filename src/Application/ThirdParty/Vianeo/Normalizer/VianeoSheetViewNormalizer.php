<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\Vianeo\View\VianeoSheetView;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VianeoSheetViewNormalizer implements NormalizerInterface
{
    const VIANEO_EMAIL                  = 'email';
    const VIANEO_NAME                   = 'name';
    const VIANEO_PROJECT_NAME           = 'project_name';
    const VIANEO_CATEGORY               = 'category';
    const VIANEO_CATEGORY_NOT_SPECIFIED = 'not_specified';
    const VIANEO_PROJECT_SUMMARY        = 'project_summary';
    const VIANEO_CIVILITY               = 'civility';
    const VIANEO_FIRST_NAME             = 'first_name';
    const VIANEO_LAST_NAME              = 'last_name';
    const VIANEO_TITLE                  = 'title';
    const VIANEO_PHONE                  = 'phone';

    const GENDER_MAPPING = [
        Gender::MAN => 'M',
        Gender::WOMAN => 'Mme',
    ];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var VianeoSheetView $sheetView */
        $sheetView = $object;

        $data = [
            self::VIANEO_EMAIL           => $sheetView->email,
            self::VIANEO_NAME            => $sheetView->fullName,
            self::VIANEO_PROJECT_NAME    => $sheetView->company,
            self::VIANEO_CATEGORY        => $sheetView->category ?? self::VIANEO_CATEGORY_NOT_SPECIFIED,
            self::VIANEO_PROJECT_SUMMARY => $sheetView->projectSummary,
            self::VIANEO_CIVILITY        => self::GENDER_MAPPING[$sheetView->gender] ?? '',
            self::VIANEO_FIRST_NAME      => $sheetView->firstName,
            self::VIANEO_LAST_NAME       => $sheetView->lastName,
            self::VIANEO_TITLE           => $sheetView->position,
            self::VIANEO_PHONE           => $sheetView->phone,
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof VianeoSheetView;
    }
}
