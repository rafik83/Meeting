<?php

namespace Proximum\Vimeet\Behat\Context\Storage;

interface StorageInterface
{
    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function get($name);
}
