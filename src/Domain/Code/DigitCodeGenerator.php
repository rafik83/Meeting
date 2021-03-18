<?php

namespace Proximum\Vimeet\Domain\Code;

class DigitCodeGenerator
{
    /**
     * @param int $length Length of the code
     *
     * @return string
     */
    public function generateCode($length)
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('The length can not be smaller than 1');
        }

        $code = '';

        for ($i = 0; $i < $length; ++$i) {
            $code .= (string) mt_rand(0, 9);
        }

        return $this->isExcluded($code) ? $this->generateCode($length) : $code;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    private function isExcluded($code)
    {
        $exclude = [];
        $codeLength = strlen($code);

        for ($digit = 0; $digit <= 9; ++$digit) {
            $exclude[] = str_repeat((string) $digit, $codeLength);
        }

        return in_array($code, $exclude);
    }
}
