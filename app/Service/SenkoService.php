<?php

namespace App\Service;

class SenkoService
{

    public function __construct()
    {
    }

    public function getSenkoCode(string $senkoumei): int
    {
        return match ($senkoumei) {
            '語学' => 203,
            '教育学', '教員養成' => 207,
            '人文学' => 209,
            '機械工学' => 221,
            '化学' => 225,
            '建築工学', '土木工学' => 227,
            '情報学' => 231,
            '生物学' => 233,
            '数学' => 237,
            '物理学' => 239,
            '地学' => 241,
            '農学' => 251,
            '獣医学', '畜産学' => 253,
            '医学', '歯学' => 261,
            '薬学' => 264,
            '看護/保健学' => 265,
            '家政学' => 271,
            '芸術学' => 291,
            default => 0,
        };
    }
}
