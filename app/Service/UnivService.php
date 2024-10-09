<?php

namespace App\Service;

use DB;
use Illuminate\Support\Facades\Config;

class UnivService
{

    const BASE_DATA = [
        1 => [
            'paxcd' => 9992009998009998,
            'dcd' => 9992,
            'dname' => 'その他大学院',
            'bname' => '研究科不明',
            'kname' => '課程不明',
            'bunri' => 1,
            'kokushi' => 3,
            'kubun' => 1,
            'gakkei' => 399,
            'lank' => 129,
            'joshi' => null,
        ],
        2 => [
            'paxcd' => 9991009999009999,
            'dcd' => 9991,
            'dname' => 'その他大学',
            'bname' => '学部不明',
            'kname' => '学科不明',
            'bunri' => 1,
            'kokushi' => 3,
            'kubun' => 2,
            'gakkei' => 299,
            'lank' => 129,
            'joshi' => null,
        ],
        3 => [
            'paxcd' => 9993001149009999,
            'dcd' => 9993,
            'dname' => 'その他短大',
            'bname' => '短大学部',
            'kname' => '学科不明',
            'bunri' => 1,
            'kokushi' => 3,
            'kubun' => 3,
            'gakkei' => 401,
            'lank' => 133,
            'joshi' => null,
        ],
        4 => [
            'paxcd' => 9994000000000000,
            'dcd' => 9994,
            'dname' => 'その他高等専門学校',
            'bname' => '学部なし',
            'kname' => '学科なし',
            'bunri' => 2,
            'kokushi' => 3,
            'kubun' => 4,
            'gakkei' => 411,
            'lank' => 134,
            'joshi' => null,
        ],
        5 => [
            'paxcd' => 2073009849000000,
            'dcd' => 2073,
            'dname' => '専門学校',
            'bname' => 'その他',
            'kname' => '学科なし',
            'bunri' => 1,
            'kokushi' => 3,
            'kubun' => 5,
            'gakkei' => 449,
            'lank' => 135,
            'joshi' => null,
        ],
        6 => [
            'paxcd' => 9996009998009998,
            'dcd' => 9996,
            'dname' => 'その他高校',
            'bname' => '学部なし',
            'kname' => '学科なし',
            'bunri' => 1,
            'kokushi' => 3,
            'kubun' => 6,
            'gakkei' => 449,
            'lank' => 135,
            'joshi' => null,
        ],
    ];
    public array $columns = ['paxcd', 'dcd', 'dname', 'bname', 'kname', 'bunri', 'kokushi', 'kubun', 'gakkei', 'lank', 'myccd'];

    public function __construct()
    {
        // 接続
        Config::set('database.connections.mysql.host', getenv('DB_HOST'));
        Config::set('database.connections.mysql.port', getenv('DB_PORT'));
        Config::set('database.connections.mysql.database', getenv('DB_DATABASE'));
        DB::connection('mysql')->reconnect();
    }

    private static function getDnameDcode(int $shubetsu_code, string $gakkoumei,string $na_bu)
    {
        // ここに処理を書く
        // 学校内で指定された系統に該当する学部を取得する
        return DB::connection('mysql')->table('univ')
            ->where('kubun_dai', $shubetsu_code)
            ->where('na_dai', $gakkoumei)
            ->where('na_bu', $na_bu)
            ->orderBy('cd_pax','desc')
            ->first();
    }

    private static function hasGakkoMei(int $shubetsu_code, string $gakkoumei): bool
    {
        // ここに処理を書く
        // 学校内で指定された系統に該当する学部を取得する
        return DB::connection('mysql')->table('univ')
            ->where('kubun_dai', $shubetsu_code)
            ->where('na_dai', $gakkoumei)
            ->exists();
    }

    private static function getBaseData(int $shubetsu_code): array
    {
        return self::BASE_DATA[$shubetsu_code];

    }

    public function getUnivDataCount(int $shubetsu_code, int $senko_code, mixed $gakkoumei)
    {
        // ここに処理を書く
        // 学校内で指定された系統に該当する学部を取得する
        return DB::connection('mysql')->table('univ')
            ->where('kubun_dai', $shubetsu_code)
            ->where('cd_keito', $senko_code)
            ->where('na_dai', $gakkoumei)
            ->count();
    }

    public function getUnivFaculties(int $shubetsu_code, int $senko_code, mixed $gakkoumei): array
    {
        // 学校内で指定された系統に該当する学部を取得する
        $rtn = [];
        $records = DB::connection('mysql')->table('univ')
            ->where('kubun_dai', $shubetsu_code)
            ->where('cd_keito', $senko_code)
            ->where('na_dai', $gakkoumei)
            ->groupBy('na_bu')
            ->get('na_bu');
        foreach ($records as $record) {
            $rtn[] = $record->na_bu;
        }
        return $rtn;
    }


    public function getUnivDataByShubetsuMajorName(int $shubetsu_code, int $senko_code, string $gakkoumei)
    {
        // まずは回答した学校種別の「その他」を設定する
        $predata = self::getBaseData($shubetsu_code);
        // 学校内で指定された系統に該当する学部を取得する
        // 学校名で該当するものがあれば、専攻から学部を取得する
        if (self::hasGakkoMei($shubetsu_code, $gakkoumei)) {
            // 学校名で取得
            $gakko = DB::connection('mysql')->table('univ')
                ->where('na_dai', $gakkoumei)
                ->where('kubun_dai', $shubetsu_code)
                ->orderby('cd_pax', 'desc')
                ->first();
            $record = DB::connection('mysql')->table('univ')
                ->where('na_dai', $gakkoumei)
                ->where('kubun_dai', $shubetsu_code)
                ->where('cd_keito', $senko_code)
                ->groupBy('na_bu')
                ->get('na_bu');
            // データのない場合はそのまま何もしない
            if (!$record) {
                return $predata;
            }
            // データが1件のみの場合はその学部の区分を反映させる
            if ($record->count() == 1) {
                $na_bu = $record->first()->na_bu;
                $record = self::getDnameDcode($shubetsu_code, $gakkoumei, $na_bu);
                $predata['paxcd'] = $record->cd_pax;
                $predata['dcd'] = $record->cd_dai;
                $predata['dname'] = $record->na_dai;
                $predata['bname'] = $record->na_bu;
                $predata['bunri'] = $record->kubun_bunri;
                $predata['kokushi'] = $record->kubun_ritsu;
                $predata['kubun'] = $record->kubun_dai;
                $predata['gakkei'] = $record->cd_keito;
                $predata['lank'] = $record->cd_lank;
                $predata['joshi'] = $record->kubun_joshi;
                return $predata;
            }

            // データが複数ある場合は学部名を/でつないで名称に入れるが区分はそのままにする
            if ($record->count() > 1) {
                $bu_na = [];
                foreach ($record as $rec) {
                    $bu_na[] = $rec->na_bu;
                }
                $predata['na_bu'] = implode('/', $bu_na);
                dd($predata);
                return $predata;
            }
        }
    }
}
