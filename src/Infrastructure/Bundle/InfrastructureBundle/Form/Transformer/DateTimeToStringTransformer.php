<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimeToStringTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $modelTimezone;

    /**
     * @var string
     */
    private $viewTimezone;

    /**
     * DateTimeToStringTransformer constructor.
     *
     * @param string $format
     * @param string $modelTimezone
     * @param string $viewTimezone
     */
    public function __construct($format, $modelTimezone, $viewTimezone)
    {
        $this->format        = $format;
        $this->modelTimezone = $modelTimezone;
        $this->viewTimezone  = $viewTimezone;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return;
        }

        if (!$value instanceof \DateTimeInterface) {
            throw new TransformationFailedException();
        }

        $value = clone $value;
        $value->setTimezone(new \DateTimeZone($this->viewTimezone));

        return $value->format($this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            return new TransformationFailedException();
        }

        $dateTime = \DateTime::createFromFormat($this->format, $value, new \DateTimeZone($this->viewTimezone));

        if (false === $dateTime) {
            throw new \LogicException(
                sprintf('Can not create datetime with format "%s" and value "%s"', $this->format, $value)
            );
        }

        return $dateTime->setTimezone(new \DateTimeZone($this->modelTimezone));
    }
}
