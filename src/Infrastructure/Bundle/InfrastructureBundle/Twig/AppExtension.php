<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\Markdown;
use Proximum\Vimeet\Ui\Helper\ChoiceListFormatter;
use Proximum\Vimeet\Ui\Helper\DataFormatter;
use Sonata\IntlBundle\Templating\Helper\LocaleHelper;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    /**
     * @var LocaleHelper
     */
    private $localeHelper;

    /**
     * @var DataFormatter
     */
    private $dataFormatter;

    /**
     * @var ChoiceListFormatter
     */
    private $choiceListFormatter;

    /** @var Markdown */
    private $markdown;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        LocaleHelper $localeHelper,
        Markdown $markdown,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->localeHelper = $localeHelper;
        $this->dataFormatter = new DataFormatter($localeHelper);
        $this->choiceListFormatter = new ChoiceListFormatter();
        $this->markdown = $markdown;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('html', [$this, 'html'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('locales', [$this, 'locales']),
            new \Twig_SimpleFilter('localize', [$this, 'localize']),
            new \Twig_SimpleFilter('intersect', 'array_intersect'),
            new \Twig_SimpleFilter('format_data', [$this, 'formatData']),
            new \Twig_SimpleFilter('choices_list', [$this, 'choicesList'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('boolean_tick', [$this, 'booleanTick'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('currency_symbol', [$this, 'currencySymbol']),
            new \Twig_SimpleFilter('format_amount', [$this, 'formatAmount']),
            new \Twig_SimpleFilter('markdown_to_html', [$this, 'markdownToHtml'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('eventUrl', [$this, 'generateEventUrl']),
            new \Twig_SimpleFunction('imageUrl', [$this, 'generateImageUrl'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('url', [$this, 'isUrl']),
        ];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function html($value)
    {
        return $value;
    }

    public function generateEventUrl(Event $event, string $routeName, array $parameters = []): string
    {
        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $event,
            $routeName,
            $parameters
        );
    }

    public function generateImageUrl(Event $event, string $path): string
    {
        return $this->eventUrlGenerator->generateImageAbsoluteUrl(
            $event,
            $path
        );
    }

    /**
     * @param mixed  $value
     * @param string $fieldTemplate
     * @param string $locale
     *
     * @return mixed
     */
    public function formatData($value, $fieldTemplate, $locale)
    {
        return $this->dataFormatter->format($value, $fieldTemplate, $locale);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function booleanTick($value)
    {
        if (true === $value) {
            return '&#10003;';
        }

        return $value;
    }

    /**
     * @param array  $choices
     * @param string $locale
     *
     * @return array|string
     */
    public function choicesList($choices, $locale)
    {
        $this->choiceListFormatter->format($choices, $locale);
    }

    /**
     * @param array  $locales
     * @param string $locale
     *
     * @return array
     */
    public function locales(array $locales, $locale = null)
    {
        return array_map(function ($code) use ($locale) {
            return $this->localeHelper->locale($code, $locale);
        }, $locales);
    }

    /**
     * @param array  $locales
     * @param string $locale
     * @param string $fallback
     * @param string $default
     *
     * @return string
     */
    public function localize($locales, $locale, $fallback, $default = '')
    {
        if (is_string($locales)) {
            return $locales;
        }

        return isset($locales[$locale]) ? $locales[$locale] : (isset($locales[$fallback]) ? $locales[$fallback] : $default);
    }

    /**
     * @param string      $currency
     * @param string|null $locale
     *
     * @return string|null
     */
    public function currencySymbol($currency, $locale = null)
    {
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency, $locale);
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public function formatAmount($value)
    {
        return AmountFormatter::centsToDecimalAmount($value);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isUrl($value)
    {
        $pattern = sprintf(UrlValidator::PATTERN, implode('|', ['http', 'https']));

        return (bool) is_string($value) && preg_match($pattern, $value);
    }

    public function markdownToHtml($value)
    {
        return $this->markdown->toHtml($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_extension';
    }
}
