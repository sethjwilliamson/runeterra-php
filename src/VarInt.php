<?php

namespace MikeReinders\RuneTerraPHP;

use MikeReinders\RuneTerraPHP\Exception\VarIntException;

/**
 * Class VarInt
 * @package MikeReinders\RuneTerraPHP
 */
final class VarInt {

    private const AllButMSB = 0x7F;
    private const JustMSB = 0x80;

    /**
     * @param string $bytes
     * @param int $offset
     * @param int|null $bytesPopped
     * @return int
     */
    public static function pop(string $bytes, int $offset = 0, int &$bytesPopped = null): int {
        $result = 0;
        $bytesPopped = 0;

        for ($i = 0, $m = (strlen($bytes) - $offset); $i < $m; $i++) {
            $byte = ord($bytes[$offset + $i]);

            $result |= ($byte & VarInt::AllButMSB) << ($i * 7);

            if (($byte & VarInt::JustMSB) != VarInt::JustMSB) {
                $bytesPopped = $i + 1;
                return $result;
            }
        }

        throw new VarIntException('Byte array did not contain valid varints.');
    }


    /**
     * @param int $value
     * @return string
     */
    public static function get(int $value): string {
        $buff = "\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0";

        if ($value < 0) {
            throw new VarIntException('VarInt requires non-negative values');
        }

        $currentIndex = 0;
        if ($value == 0) return "\x0";

        while ($value != 0) {
            $byteVal = $value & VarInt::AllButMSB;
            $value >>= 7;

            if ($value != 0) {
                $byteVal |= VarInt::JustMSB;
            }
            $buff[$currentIndex++] = chr($byteVal);
        }

        return substr($buff, 0, $currentIndex);
    }

}