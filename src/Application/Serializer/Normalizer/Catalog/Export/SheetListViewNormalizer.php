<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Catalog\Export;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetListView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SheetListViewNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var SheetListView $sheetListView */
        $sheetListView = $object;

        $data   = [];
        $labels = [];
        $labels['type'] = $this->convertCharset(
            $sheetListView->typeOrCategoryColumn,
            Charset::UTF_8,
            $context['charset']
        );

        foreach ($sheetListView->registrationFields as $key => $field) {
            $labels[$key] = $this->convertCharset($field, Charset::UTF_8, $context['charset']);
        }

        $labels['participant'] = $this->convertCharset(
            $sheetListView->participantPositionColumn,
            Charset::UTF_8,
            $context['charset']
        );

        foreach ($sheetListView->sheetFields as $key => $field) {
            $labels[$key] = $this->convertCharset($field, Charset::UTF_8, $context['charset']);
        }

        $data[] = $labels;

        foreach ($sheetListView->sheetViews as $sheet) {
            $data[] = $this->normalizer->normalize($sheet, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'csv' === $format && $data instanceof SheetListView;
    }

    /**
     * @param mixed  $input
     * @param string $inCharset
     * @param string $outCharset
     *
     * @return string
     */
    protected function convertCharset(
        $input,
        string $inCharset = Charset::UTF_8,
        string $outCharset = Charset::WINDOWS_1252
    ) {
        if (!$input || !is_string($input)) {
            return $input;
        }

        if ($inCharset !== $outCharset) {
            return iconv($inCharset, $outCharset . '//TRANSLIT//IGNORE', $input);
        }

        return $input;
    }
}
