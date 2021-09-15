<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Catalog\Export;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SheetViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var SheetView $sheetView */
        $sheetView = $object;

        $data = [];

        $data['type'] = $this->convertCharset($sheetView->typeTitle, Charset::UTF_8, $context['charset']);

        foreach ($sheetView->sheetRegistrationInfo as $key => $registrationInfo) {
            $data[$key] = $this->convertCharset($registrationInfo, Charset::UTF_8, $context['charset']);
        }

        $data['participant'] = $this->convertCharset(
            $sheetView->participantPosition,
            Charset::UTF_8,
            $context['charset']
        );

        foreach ($sheetView->sheetInfo as $key => $sheetInfo) {
            $data[$key] = $this->convertCharset($sheetInfo, Charset::UTF_8, $context['charset']);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SheetView && 'csv' === $format;
    }

    /**
     * @param mixed  $input
     * @param string $inCharset
     * @param string $outCharset
     *
     * @return string
     */
    protected function convertCharset($input, $inCharset = Charset::UTF_8, $outCharset = Charset::WINDOWS_1252)
    {
        if (!$input || !is_string($input)) {
            return $input;
        }

        if ($inCharset !== $outCharset) {
            return iconv($inCharset, $outCharset . '//TRANSLIT//IGNORE', $input);
        }

        return $input;
    }
}
