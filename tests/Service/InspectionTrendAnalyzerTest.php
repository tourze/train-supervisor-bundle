<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Service\InspectionTrendAnalyzer;

/**
 * @internal
 */
#[CoversClass(InspectionTrendAnalyzer::class)]
final class InspectionTrendAnalyzerTest extends TestCase
{
    private InspectionTrendAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new InspectionTrendAnalyzer();
    }

    public function testBuildTrendDataWithEmptyInspections(): void
    {
        $result = $this->analyzer->buildTrendData([]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('inspections', $result);
        $this->assertArrayHasKey('problems', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertEmpty($result['inspections']);
        $this->assertEmpty($result['problems']);
        $this->assertEmpty($result['scores']);
    }

    public function testBuildTrendDataWithSingleInspection(): void
    {
        $inspection = $this->createInspection(
            new \DateTimeImmutable('2024-01-15'),
            true,
            85.5
        );

        $result = $this->analyzer->buildTrendData([$inspection]);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['inspections']['2024-01-15']);
        $this->assertEquals(1, $result['problems']['2024-01-15']);
        $this->assertEquals(85.5, $result['scores']['2024-01-15']);
    }

    public function testBuildTrendDataWithMultipleInspectionsOnSameDate(): void
    {
        $inspection1 = $this->createInspection(
            new \DateTimeImmutable('2024-01-15'),
            true,
            85.0
        );
        $inspection2 = $this->createInspection(
            new \DateTimeImmutable('2024-01-15'),
            false,
            90.0
        );
        $inspection3 = $this->createInspection(
            new \DateTimeImmutable('2024-01-15'),
            true,
            75.0
        );

        $result = $this->analyzer->buildTrendData([$inspection1, $inspection2, $inspection3]);

        $this->assertEquals(3, $result['inspections']['2024-01-15']);
        $this->assertEquals(2, $result['problems']['2024-01-15']);
        $scoreValue = $result['scores']['2024-01-15'];
        $this->assertIsNumeric($scoreValue);
        $this->assertEquals(83.33, round((float) $scoreValue, 2));
    }

    public function testBuildTrendDataWithMultipleDates(): void
    {
        $inspections = [
            $this->createInspection(new \DateTimeImmutable('2024-01-15'), true, 85.0),
            $this->createInspection(new \DateTimeImmutable('2024-01-16'), false, 90.0),
            $this->createInspection(new \DateTimeImmutable('2024-01-17'), true, 75.0),
        ];

        $result = $this->analyzer->buildTrendData($inspections);

        $this->assertCount(3, $result['inspections']);
        $this->assertEquals(1, $result['inspections']['2024-01-15']);
        $this->assertEquals(1, $result['inspections']['2024-01-16']);
        $this->assertEquals(1, $result['inspections']['2024-01-17']);
        $this->assertEquals(85.0, $result['scores']['2024-01-15']);
        $this->assertEquals(90.0, $result['scores']['2024-01-16']);
        $this->assertEquals(75.0, $result['scores']['2024-01-17']);
    }

    public function testBuildTrendDataWithNullScores(): void
    {
        $inspection = $this->createInspection(
            new \DateTimeImmutable('2024-01-15'),
            false,
            null
        );

        $result = $this->analyzer->buildTrendData([$inspection]);

        $this->assertEquals(0, $result['scores']['2024-01-15']);
    }

    public function testBuildTrendDataWithMixedScores(): void
    {
        $inspections = [
            $this->createInspection(new \DateTimeImmutable('2024-01-15'), true, 85.0),
            $this->createInspection(new \DateTimeImmutable('2024-01-15'), false, null),
            $this->createInspection(new \DateTimeImmutable('2024-01-15'), true, 90.0),
        ];

        $result = $this->analyzer->buildTrendData($inspections);

        $this->assertEquals(87.5, $result['scores']['2024-01-15']);
    }

    public function testCalculateTrendsSummaryWithEmptyData(): void
    {
        $trends = [
            'inspections' => [],
            'problems' => [],
            'scores' => [],
        ];

        $result = $this->analyzer->calculateTrendsSummary([], $trends);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['totalInspections']);
        $this->assertEquals(0, $result['totalProblems']);
        $this->assertEquals(0, $result['averageScore']);
    }

    public function testCalculateTrendsSummaryWithValidData(): void
    {
        $inspections = [
            $this->createInspection(new \DateTimeImmutable('2024-01-15'), true, 85.0),
            $this->createInspection(new \DateTimeImmutable('2024-01-16'), false, 90.0),
            $this->createInspection(new \DateTimeImmutable('2024-01-17'), true, 75.0),
        ];

        $trends = [
            'inspections' => ['2024-01-15' => 1, '2024-01-16' => 1, '2024-01-17' => 1],
            'problems' => ['2024-01-15' => 1, '2024-01-16' => 0, '2024-01-17' => 1],
            'scores' => ['2024-01-15' => 85.0, '2024-01-16' => 90.0, '2024-01-17' => 75.0],
        ];

        $result = $this->analyzer->calculateTrendsSummary($inspections, $trends);

        $this->assertEquals(3, $result['totalInspections']);
        $this->assertEquals(2, $result['totalProblems']);
        $averageScore = $result['averageScore'];
        $this->assertIsNumeric($averageScore);
        $this->assertEquals(83.33, round((float) $averageScore, 2));
    }

    /**
     * @param array<string, mixed> $problemsData
     * @param array<string, mixed> $scoresData
     */
    #[DataProvider('invalidTrendsDataProvider')]
    public function testCalculateTrendsSummaryWithInvalidScores(array $problemsData, array $scoresData, int $expectedProblems, float $expectedAvgScore): void
    {
        $trends = [
            'inspections' => [],
            'problems' => $problemsData,
            'scores' => $scoresData,
        ];

        $result = $this->analyzer->calculateTrendsSummary([], $trends);

        $this->assertEquals($expectedProblems, $result['totalProblems']);
        $this->assertEquals($expectedAvgScore, $result['averageScore']);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: int, 3: float}>
     */
    public static function invalidTrendsDataProvider(): array
    {
        return [
            'with zero scores' => [
                ['2024-01-15' => 5],
                ['2024-01-15' => 0, '2024-01-16' => 0],
                5,
                0.0,
            ],
            'with mixed valid and zero scores' => [
                ['2024-01-15' => 3],
                ['2024-01-15' => 85.0, '2024-01-16' => 0, '2024-01-17' => 90.0],
                3,
                87.5,
            ],
            'with non-numeric problems' => [
                ['2024-01-15' => 'invalid', '2024-01-16' => 5],
                ['2024-01-15' => 85.0],
                5,
                85.0,
            ],
        ];
    }

    private function createInspection(
        \DateTimeImmutable $date,
        bool $hasProblems,
        ?float $score,
    ): SupervisionInspection {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('routine');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('approved');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate($date);
        $inspection->setInspector('检查员');
        $inspection->setInspectionStatus('completed');

        if ($hasProblems) {
            $inspection->setFoundProblems(['问题1' => '描述1']);
        }

        if (null !== $score) {
            $inspection->setOverallScore($score);
        }

        return $inspection;
    }
}
