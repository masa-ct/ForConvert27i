<?php

namespace App\Console\Commands;

use App\Service\SenkouService;
use App\Service\UnivService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GetResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'axol-convert:get-result';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // daigaku_henkan.txtを読み込む
        $file_path = storage_path('app/private/daigaku_henkan.txt');
        $file = file_get_contents($file_path);
        $lines = explode("\n", $file);
        // 1行目はタイトル行なのでスキップ
        array_shift($lines);
        $results = [];

        $univ_service = new UnivService();

        // 専攻をランダムに返すサービス
        $senkou_service = new SenkouService();

        foreach ($lines as $line) {
            // 2項目のどちらかに値がない場合はスキップ
            $data = explode("\t", $line);
            $shubetsu = Arr::get($data, 0, '');
            $dname = Arr::get($data, 1, '');
            if ($shubetsu  === '' || $dname  === '') {
                continue;
            }
            // 学校種別「高校」「海外の学校」「海外学校」「海外大学」は除外
            if (in_array($shubetsu, ['高校', '海外の学校', '海外学校', '海外大学'])) {
                continue;
            }

            $senkou_mei = $senkou_service->getSenkouMei();   // 専攻名をランダムに返す
            // 学校データを取得する
            $mast_school_data = $univ_service->getUnivDataByShubetsuMajorName($shubetsu, $dname, $senkou_mei);   // 学校種別・専攻名・学校名で学校データを取得する

            $results[] = [
                'shubetsu' => Arr::get($data, 0, ''),
                'dname_org' => Arr::get($data, 1, ''),
                'senkou_mei' => $senkou_mei,
                'paxcd' => Arr::get($mast_school_data, 'paxcd', ''),
                'dcd' => Arr::get($mast_school_data, 'dcd', ''),
                'dname' => Arr::get($mast_school_data, 'dname', ''),
                'bname' => Arr::get($mast_school_data, 'bname', ''),
                'kname' => Arr::get($mast_school_data, 'kname', ''),
                'bunri' => Arr::get($mast_school_data, 'bunri', ''),
                'kokushi' => Arr::get($mast_school_data, 'kokushi', ''),
                'kubun' => Arr::get($mast_school_data, 'kubun', ''),
                'gakkei' => Arr::get($mast_school_data, 'gakkei', ''),
                'lank' => Arr::get($mast_school_data, 'lank', ''),
                'joshi' => Arr::get($mast_school_data, 'joshi', ''),
            ];
        }
        // 配列作成を通知
        $this->info('Results are created');

        // 配列をCSVファイルに出力
        $file_path = storage_path('app/private/results.csv');
        $file = fopen($file_path, 'w');
        fputcsv($file, ['shubetsu', 'dname_org', 'senkou_mei', 'paxcd', 'dcd', 'dname', 'bname', 'kname', 'bunri', 'kokushi', 'kubun', 'gakkei', 'lank', 'joshi']);
        foreach ($results as $result) {
            fputcsv($file, $result);
        }
        // 文字コードをUTF-8からSJISに変換
        $file = file_get_contents($file_path);
        $file = mb_convert_encoding($file, 'SJIS', 'UTF-8');
        file_put_contents($file_path, $file);

        fclose($file_path);
        // ファイル作成の完了を通知する
        $this->info('Results are saved in ' . $file_path);


    }
}
