<?php

namespace App\Console\Commands;

use App\Service\SenkoService;
use App\Service\ShubetsuService;
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
        $shubetsu = $this->ask('学校種別を入力してください');
        $gakkoumei = $this->ask('学校名を入力してください');
        $senkoumei = $this->ask('専攻名を入力してください');
        // 学校種別をコード化する（univ.kubun_daiに対応）
        $shubetsu_service = new ShubetsuService();
        $shubetsu_code = $shubetsu_service->getShubetsuCode($shubetsu);
        // 専攻名をコード化する（univ.cd_keitoに対応）
        $senko_service = new SenkoService();
        $senko_code = $senko_service->getSenkoCode($senkoumei);
        // ここに処理を書く
        // ここに処理を書く
        dd($shubetsu_code,$senko_code, $gakkoumei, $senkoumei);
        //
    }
}
