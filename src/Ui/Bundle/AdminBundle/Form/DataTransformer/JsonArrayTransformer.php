<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JsonArrayTransformer implements DataTransformerInterface
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function transform($data)
    {
        if (!is_array($data)) {
            throw new TransformationFailedException('data is not an array');
        }

        return json_encode($data);
    }

    /**
     * @param string $data
     *
     * @return array
     */
    public function reverseTransform($data)
    {
        if (!is_string($data)) {
            throw new TransformationFailedException('data is not a string');
        }

        $json = json_decode($data, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TransformationFailedException('data is not a valid json');
        }

        return $json;
    }
}
