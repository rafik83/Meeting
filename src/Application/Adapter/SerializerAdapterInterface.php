<?php

namespace Proximum\Vimeet\Application\Adapter;

interface SerializerAdapterInterface
{
    /**
     * Deserializes data into the given type.
     *
     * @param mixed  $data
     * @param string $type
     * @param string $format
     * @param array  $context
     *
     * @return object
     */
    public function deserialize($data, $type, $format, array $context = []);

    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed  $data    any data
     * @param string $format  format name
     * @param array  $context options normalizers/encoders have access to
     *
     * @return string
     */
    public function serialize($data, $format, array $context = []);

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param object $data    object to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array|scalar
     */
    public function normalize($data, $format = null, array $context = []);

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed  $data    data to restore
     * @param string $type    the expected class to instantiate
     * @param string $format  format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return object
     */
    public function denormalize($data, $type, $format = null, array $context = []);

    /**
     * Encodes data into the given format.
     *
     * @param mixed  $data    Data to encode
     * @param string $format  Format name
     * @param array  $context options that normalizers/encoders have access to
     *
     * @return scalar
     */
    public function encode($data, $format, array $context = []);

    /**
     * Decodes a string into PHP data.
     *
     * @param string $data    Data to decode
     * @param string $format  Format name
     * @param array  $context options that decoders have access to
     *
     * The format parameter specifies which format the data is in; valid values
     * depend on the specific implementation. Authors implementing this interface
     * are encouraged to document which formats they support in a non-inherited
     * phpdoc comment.
     *
     * @return mixed
     */
    public function decode($data, $format, array $context = []);
}
