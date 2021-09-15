<?php

namespace Proximum\Vimeet\Domain\Rule;

use Proximum\Vimeet\Domain\Model\Rule;

class ComposedRule
{
    /**
     * @var array
     */
    public $tags;

    /**
     * @var Rule
     */
    public $rule;

    /**
     * @param string $tag
     */
    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * @param string $key
     *
     * @return ComposedRule
     */
    public function removeFromTags($key)
    {
        if (isset($this->tags[$key])) {
            unset($this->tags[$key]);
        }

        return $this;
    }

    /**
     * @param string $tagToCheck
     *
     * @return bool
     */
    public function isPresent($tagToCheck)
    {
        foreach ($this->tags as $tag) {
            if ($tagToCheck === $tag) {
                return true;
            }
        }

        return false;
    }
}
