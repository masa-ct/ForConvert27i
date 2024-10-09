<?php

namespace Tests\Unit;

use App\Service\ShubetsuService;
use PHPUnit\Framework\TestCase;

class UnivServiceTest extends TestCase
{
    public static function shubetsuCodeProvider(): array
    {
        return [
            ['大学院（博士）', 1],
            ['大学院（修士）', 1],
            ['大学院（MBA/MOT）', 1],
            ['大学院（法科）', 1],
            ['大学院（その他専門職）', 1],
            ['4年制大学', 2],
            ['6年制大学', 2],
            ['専門職大学', 2],
            ['短期大学', 3],
            ['専門職短期大学', 3],
            ['高等専門学校', 4],
            ['専門学校', 5],
            ['高等学校', 6],
        ];
    }

    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider shubetsuCodeProvider
     * @param string $shubetsu
     * @param int $expects
     */
    public function test_getShubetsuCode(string $shubetsu, int $expects): void
    {
        $univ_service = new ShubetsuService();
        $result = $univ_service->getShubetsuCode($shubetsu);
        $this->assertEquals($expects, $result);
    }
}
