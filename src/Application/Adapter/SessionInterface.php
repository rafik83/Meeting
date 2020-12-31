<?php

namespace Proximum\Vimeet\Application\Adapter;

interface SessionInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Gets and clears flash from the stack.
     *
     * @param string $type
     * @param array  $default Default value if $type does not exist
     *
     * @return array
     */
    public function getFromFlashBag($type, array $default = []): array;

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param mixed  $message
     */
    public function addToFlashBag($type, $message): void;

    /**
     * @param string $key
     * @param mixed  $data
     */
    public function set($key, $data): void;

    /**
     * @param string $key
     */
    public function remove($key);
}
