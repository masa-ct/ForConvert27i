<?php

namespace App\Service;

class ShubetsuService
{

    public function __construct()
    {
    }

    public static function getShubetsuCode(string $shubetsu): int
    {
        return match ($shubetsu) {
            '大学院（博士）', '大学院（修士）', '大学院（MBA/MOT）', '大学院（法科）', '大学院（その他専門職）' => 1,
            '4年制大学', '6年制大学', '専門職大学' => 2,
            '短期大学', '専門職短期大学' => 3,
            '高等専門学校' => 4,
            '専門学校' => 5,
            '高等学校' => 6,
            default => 0,
        };
    }
}
