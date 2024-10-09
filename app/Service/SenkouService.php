<?php

namespace App\Service;

class SenkouService
{
    public function __construct()
    {
    }

    public function getSenkouMei(): string
    {
        // 専攻名をランダムに返します
        $senkou_mei = [
            '人文学',
            '法学',
            '政治学',
            '商学',
            '経済学',
            '社会学',
            '語学',
            '教育学',
            '教員養成',
            '数学',
            '物理学',
            '化学',
            '生物学',
            '地学',
            '機械工学',
            '電気通信工学',
            '建築工学',
            '土木工学',
            '応用化学',
            '応用物理学',
            '原子力工学',
            '経営工学',
            '情報学',
            '環境学',
            '農学',
            '水産学',
            '畜産学',
            '医学',
            '歯学',
            '薬学',
            '獣医学',
            '看護/保健学',
            'スポーツ/人間科学',
            '家政学',
            '芸術学',
            'その他',

        ];
        return $senkou_mei[array_rand($senkou_mei)];
    }
}
