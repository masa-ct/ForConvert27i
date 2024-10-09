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

    public function getUnivDataByShubetsuMajorName(string $shubetsu_mei, string $gakkou_mei, string $senkou_mei): array
    {
        // 学校種別のコード化
        $shubetsu_code = ShubetsuService::getShubetsuCode($shubetsu_mei);

        // まずは回答した学校種別の「その他」を設定する
        $predata = self::getBaseData($shubetsu_code);
        // 学校名に、学校種別 + 学校名をセット
        $predata['dname'] = sprintf('%s %s', $shubetsu_mei, $gakkou_mei);
        // 学科名に、専攻をセット
        $predata['kname'] = $senkou_mei;

        // 学校種別コードと学校名で学校データを取得する
        if ($this->hasGakkoMei($shubetsu_code, $gakkou_mei)) {
            // 該当するものがあれば、その他としていたものを上書きする
            $record = $this->getUnivDataSonota($shubetsu_code, $gakkou_mei);
            $this->setKobetsuData($predata, $record);
        } else {
            // 該当するものがなければ、学校名を編集して検索する
            $edited_gakkoumei = $this->editGakkoumei($shubetsu_code, $gakkou_mei);
            foreach ($edited_gakkoumei as $gakkoumei) {
                if ($this->hasGakkoMei($shubetsu_code, $gakkoumei)) {
                    $record = $this->getUnivDataSonota($shubetsu_code, $gakkoumei);
                    if ($record == null) {
                        continue;
                    } else {
                        $this->setKobetsuData($predata, $record);
                        break;
                    }
                }
            }
        }
        return $predata;
    }

    private function getUnivDataSonota(int $shubetsu_code, string $gakkou_mei)
    {
        // ここに処理を書く
        // 学校種別と学校名で降順にして最初のデータを取得する（学部不明、学科不明を取得する想定）
        return DB::connection('mysql')->table('univ')
            ->where('kubun_dai', $shubetsu_code)
            ->where('na_dai', $gakkou_mei)
            ->orderBy('cd_pax', 'desc')
            ->first(['cd_pax', 'cd_dai', 'na_dai', 'kubun_dai', 'kubun_ritsu', 'kubun_bunri', 'cd_keito', 'cd_lank', 'kubun_joshi']);
    }

    private function editGakkoumei(int $shubetsu_code, string $gakkou_mei): array
    {
        $gakkou_mei_original = $gakkou_mei;
        $rtn = [];
        // 学校種別が大学院で学校名の最後に「〇〇大学院」と入っている場合は除外する
        if ($shubetsu_code == 1) {
            if (preg_match('/(専門職大学院|知的財産専門職大学院|会計職大学院|法科大学院|教職大学院|大学院)$/', $gakkou_mei)) {
                $gakkou_mei = preg_replace('/大学院$/', '', $gakkou_mei);
                $rtn[] = $gakkou_mei;
            }
        }
        if ($gakkou_mei == '慶応義塾大学') {
            $gakkou_mei = '慶應義塾大学';
            $rtn[] = $gakkou_mei;
        }
        if ($gakkou_mei == '国学院大学') {
            $gakkou_mei = '國學院大學';
            $rtn[] = $gakkou_mei;
        }

        return $rtn;
    }

    private function setKobetsuData(array &$predata, $record): void
    {
        $predata['paxcd'] = $record->cd_pax;
        $predata['dcd'] = $record->cd_dai;
        $predata['dname'] = $record->na_dai;
        $predata['kubun'] = $record->kubun_dai;
        $predata['bunri'] = $record->kubun_bunri;
        $predata['lank'] = $record->cd_lank;
        $predata['joshi'] = $record->kubun_joshi;
    }
}
