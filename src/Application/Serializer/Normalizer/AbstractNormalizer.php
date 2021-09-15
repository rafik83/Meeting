<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;

abstract class AbstractNormalizer
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $normalizerType = 'common';

    /**
     * AbstractNormalizer constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * This is the privileged method to normalize input whose type is not known in advance. It takes care of
     * boolean conversion to string and label translation, charset encoding, etc.
     *
     * @param mixed  $input
     * @param string $inCharset
     * @param string $outCharset
     *
     * @return mixed
     */
    protected function normalizeInput($input, $inCharset = Charset::UTF_8, $outCharset = Charset::WINDOWS_1252)
    {
        if (is_bool($input)) {
            return $this->normalizeBoolean($input);
        }

        if (is_string($input) && '' !== $input) {
            return $this->convertCharset($input, $inCharset, $outCharset);
        }

        return $input;
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

    /**
     * Takes a boolean and converts it into a translated human readable form.
     *
     * @param bool|mixed $value
     *
     * @return string
     */
    protected function normalizeBoolean($value)
    {
        $value = (bool) $value;

        return $this->translator->trans(
            sprintf('admin.%s.export.%s', $this->normalizerType, $value ? 'yes' : 'no')
        );
    }
}
