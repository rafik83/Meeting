<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;

class NullTranslator implements TranslatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return $id;
    }
}
