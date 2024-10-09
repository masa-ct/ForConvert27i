<?php

namespace App\Console\Commands;

use App\Service\SenkoService;
use App\Service\ShubetsuService;
use App\Service\UnivService;
use Illuminate\Console\Command;

class GetUnivDataByName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'axol-convert:get-univ-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '学校種別、学校名、専攻名でデータを取得する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // ここに処理を書く
        // 学校種別、学校名、専攻名でデータを取得する
        $shubetsu_mei = $this->ask('学校種別を入力してください', '4年制大学');
        $gakkou_mei = $this->ask('学校名を入力してください', '北海道大学');
        $senkou_mei = $this->ask('専攻名を入力してください', '農学');

        // 学校種別と専攻と学校名で学校データを取得する
        $univ_service = new UnivService();
        $mast_school_data = $univ_service->getUnivDataByShubetsuMajorName($shubetsu_mei, $gakkou_mei, $senkou_mei);   // 学校種別・専攻名・学校名で学校データを取得する

        dd($mast_school_data);
        $record_count = $univ_service->getUnivDataCount($shubetsu_code, $senko_code, $gakkou_mei);   // 学校種別・学校名・専攻名で学部データを取得する
        if ($record_count ==! 0) {
            $faculties = $univ_service->getUnivFaculties($shubetsu_code, $senko_code, $gakkou_mei);   // 学校種別・学校名・専攻名で学部データを取得する
        }
        dd($shubetsu_code, $senko_code, $gakkou_mei, $senkou_mei,$record_count, $faculties);
        //
    }
}
