<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\AvatarInterface;
use YoHang88\LetterAvatar\LetterAvatar;

class AvatarAdapter implements AvatarInterface
{
    public function generate(string $name): string
    {
        $avatar = new LetterAvatar($name, 'square', 48);

        return $avatar->encode();
    }
}
