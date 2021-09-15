<?php

namespace Proximum\Vimeet\Domain\Service;

use Proximum\Vimeet\Domain\Exception\Service\Markdown\HeadingNotSupportedException;

final class MarkdownFormatter
{
    /**
     * @param string $string
     *
     * @return string
     */
    public static function bold($string)
    {
        return sprintf('**%s**', $string);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function italic($string)
    {
        return sprintf('_%s_', $string);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function strikethrough($string)
    {
        return sprintf('~~%s~~', $string);
    }

    /**
     * @param array $list
     *
     * @return string
     */
    public static function lists(array $list)
    {
        return sprintf('%s%s',
            implode("\n", array_map(function ($item) {
                return sprintf('- %s', $item);
            }, $list)),
            "\n"
        );
    }

    /**
     * @param string      $string
     * @param string|null $format
     *
     * @return string
     */
    public static function highlight($string, $format = null)
    {
        return sprintf('```%s%s%s%s```%s', $format, "\n", $string, "\n", "\n");
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function newLine($string)
    {
        return sprintf('%s%s%s', $string, "\n", "\n");
    }

    /**
     * @return string
     */
    public static function newBreak()
    {
        return "\n";
    }

    /**
     * @param string      $link
     * @param string|null $label
     * @param string|null $title
     *
     * @return string
     */
    public static function link($link, $label = null, $title = null)
    {
        return sprintf(
            '[%s](%s%s)',
            null !== $label ? $label : $link,
            $link,
            null !== $title ? sprintf(' "%s"', $title) : null
        );
    }

    /**
     * @param string      $link
     * @param string|null $label
     * @param string|null $title
     *
     * @return string
     */
    public static function image($link, $label = null, $title = null)
    {
        return sprintf('!%s', self::link($link, $label, $title));
    }

    /**
     * @param string $string
     * @param int    $head
     *
     * @throws HeadingNotSupportedException
     *
     * @return string
     */
    public static function heading($string, $head)
    {
        if ($head > 6 || $head < 1) {
            throw new HeadingNotSupportedException(
                sprintf('The given heading (%s) is not supported, should be between 1 and 6', $head)
            );
        }

        return sprintf('%s %s', str_repeat('#', $head), $string);
    }
}
