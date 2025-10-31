<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsExporter;

/**
 * @internal
 */
#[CoversClass(LearningStatisticsExporter::class)]
final class LearningStatisticsExporterTest extends TestCase
{
    private LearningStatisticsExporter $exporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exporter = new LearningStatisticsExporter();
    }

    public function testExportToCsvWithValidData(): void
    {
        $statistics = [
            'enrollment' => [
                'by_institution' => [
                    ['institution_name' => '机构A', 'count' => 100],
                    ['institution_name' => '机构B', 'count' => 200],
                ],
            ],
        ];

        $result = $this->exporter->export($statistics, 'csv');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertEquals('text/csv; charset=utf-8', $result['mime_type']);
        $content = $result['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString('机构名称,报名人数,完成人数,完成率,在线人数', $content);
        $this->assertStringContainsString('机构A,100', $content);
        $this->assertStringContainsString('机构B,200', $content);
    }

    public function testExportToCsvWithEmptyData(): void
    {
        $statistics = [];

        $result = $this->exporter->export($statistics, 'csv');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertEquals('text/csv; charset=utf-8', $result['mime_type']);
        $content = $result['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString('机构名称,报名人数,完成人数,完成率,在线人数', $content);
    }

    public function testExportToCsvWithMissingEnrollmentKey(): void
    {
        $statistics = [
            'other_data' => ['some' => 'value'],
        ];

        $result = $this->exporter->export($statistics, 'csv');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $content = $result['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString('机构名称,报名人数,完成人数,完成率,在线人数', $content);
    }

    public function testExportToCsvWithInvalidEnrollmentStructure(): void
    {
        $statistics = [
            'enrollment' => 'invalid',
        ];

        $result = $this->exporter->export($statistics, 'csv');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $content = $result['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString('机构名称,报名人数,完成人数,完成率,在线人数', $content);
    }

    public function testExportToCsvWithInvalidByInstitutionStructure(): void
    {
        $statistics = [
            'enrollment' => [
                'by_institution' => 'invalid',
            ],
        ];

        $result = $this->exporter->export($statistics, 'csv');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
    }

    /**
     * @param array<string, mixed> $institution
     */
    #[DataProvider('invalidInstitutionDataProvider')]
    public function testExportToCsvWithInvalidInstitutionData(array $institution, string $expectedName, int $expectedCount): void
    {
        $statistics = [
            'enrollment' => [
                'by_institution' => [$institution],
            ],
        ];

        $result = $this->exporter->export($statistics, 'csv');

        $content = $result['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString($expectedName, $content);
        $this->assertStringContainsString((string) $expectedCount, $content);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string, 2: int}>
     */
    public static function invalidInstitutionDataProvider(): array
    {
        return [
            'missing institution_name' => [
                ['count' => 100],
                ',100',
                100,
            ],
            'invalid institution_name type' => [
                ['institution_name' => 123, 'count' => 200],
                ',200',
                200,
            ],
            'missing count' => [
                ['institution_name' => '机构C'],
                '机构C,0',
                0,
            ],
            'invalid count type' => [
                ['institution_name' => '机构D', 'count' => 'invalid'],
                '机构D,0',
                0,
            ],
        ];
    }

    public function testExportToExcel(): void
    {
        $statistics = [
            'enrollment' => [
                'by_institution' => [
                    ['institution_name' => '机构A', 'count' => 100],
                ],
            ],
        ];

        $result = $this->exporter->export($statistics, 'excel');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertEquals('text/csv; charset=utf-8', $result['mime_type']);
    }

    public function testExportToPdf(): void
    {
        $statistics = [];

        $result = $this->exporter->export($statistics, 'pdf');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertEquals('text/html; charset=utf-8', $result['mime_type']);
        $content = $result['content'];
        $this->assertIsString($content);
        $this->assertStringContainsString('<h1>学习统计报告</h1>', $content);
        $this->assertStringContainsString('生成时间：', $content);
    }

    public function testExportWithUnsupportedFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('不支持的导出格式');

        $this->exporter->export([], 'json');
    }

    /**
     * @param array<string, mixed> $statistics
     */
    #[DataProvider('supportedFormatsDataProvider')]
    public function testExportWithSupportedFormats(string $format, array $statistics): void
    {
        $result = $this->exporter->export($statistics, $format);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertNotEmpty($result['content']);
        $this->assertNotEmpty($result['mime_type']);
    }

    /**
     * @return array<string, array{0: string, 1: array<string, mixed>}>
     */
    public static function supportedFormatsDataProvider(): array
    {
        $statistics = [
            'enrollment' => [
                'by_institution' => [
                    ['institution_name' => '机构A', 'count' => 100],
                ],
            ],
        ];

        return [
            'csv format' => ['csv', $statistics],
            'excel format' => ['excel', $statistics],
            'pdf format' => ['pdf', $statistics],
        ];
    }
}
