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
        $shubetsu = $this->ask('学校種別を入力してください', '4年制大学');
        $gakkoumei = $this->ask('学校名を入力してください', '北海道大学');
        $senkoumei = $this->ask('専攻名を入力してください', '農学');

        // 学校種別をコード化する（univ.kubun_daiに対応）
        $shubetsu_service = new ShubetsuService();
        $shubetsu_code = $shubetsu_service->getShubetsuCode($shubetsu);

        // 専攻名をコード化する（univ.cd_keitoに対応）
        $senko_service = new SenkoService();
        $senko_code = $senko_service->getSenkoCode($senkoumei);

        // 学校種別と専攻と学校名で学校データを取得する
        $univ_service = new UnivService();
        $mast_school_data = $univ_service->getUnivDataByShubetsuMajorName($shubetsu_code, $senko_code, $gakkoumei);   // 学校種別・専攻名・学校名で学校データを取得する

        dd($mast_school_data);
        $record_count = $univ_service->getUnivDataCount($shubetsu_code, $senko_code, $gakkoumei);   // 学校種別・学校名・専攻名で学部データを取得する
        if ($record_count ==! 0) {
            $faculties = $univ_service->getUnivFaculties($shubetsu_code, $senko_code, $gakkoumei);   // 学校種別・学校名・専攻名で学部データを取得する
        }
        dd($shubetsu_code, $senko_code, $gakkoumei, $senkoumei,$record_count, $faculties);
        //
    }
}
