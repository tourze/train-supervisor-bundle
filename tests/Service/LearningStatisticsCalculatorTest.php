<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsCalculator;

/**
 * @internal
 */
#[CoversClass(LearningStatisticsCalculator::class)]
#[RunTestsInSeparateProcesses]
final class LearningStatisticsCalculatorTest extends AbstractIntegrationTestCase
{
    private LearningStatisticsCalculator $calculator;

    protected function onSetUp(): void
    {
        $this->calculator = self::getService(LearningStatisticsCalculator::class);
    }

    public function testCalculateTrendsWithBasicFilters(): void
    {
        $isSqlite = $this->calculator->isSqlite();
        $dateFormat = $isSqlite ? "strftime('%Y-%m-%d', s.date)" : "DATE_FORMAT(s.date, '%Y-%m-%d')";

        $sql = "SELECT {$dateFormat} as period,
                SUM(s.daily_login_count) as enrolled_count,
                SUM(s.daily_learn_count) as completed_count,
                AVG(s.daily_learn_count) as avg_learn_count
            FROM train_supervisor_data s
            WHERE 1=1";

        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->calculator->calculateTrends($sql, $filters);

        $this->assertIsArray($result);
    }

    public function testCalculateTrendsWithInstitutionFilter(): void
    {
        $isSqlite = $this->calculator->isSqlite();
        $dateFormat = $isSqlite ? "strftime('%Y-%m-%d', s.date)" : "DATE_FORMAT(s.date, '%Y-%m-%d')";

        $sql = "SELECT {$dateFormat} as period,
                SUM(s.daily_login_count) as enrolled_count,
                SUM(s.daily_learn_count) as completed_count
            FROM train_supervisor_data s
            WHERE 1=1";

        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->calculator->calculateTrends($sql, $filters);

        $this->assertIsArray($result);
    }

    public function testCalculateTrendsWithInstitutionIdsFilter(): void
    {
        $isSqlite = $this->calculator->isSqlite();
        $dateFormat = $isSqlite ? "strftime('%Y-%m-%d', s.date)" : "DATE_FORMAT(s.date, '%Y-%m-%d')";

        $sql = "SELECT {$dateFormat} as period,
                SUM(s.daily_login_count) as enrolled_count
            FROM train_supervisor_data s
            WHERE 1=1";

        $filters = [
            'institution_ids' => ['supplier123', 'supplier456'],
            'start_date' => '2024-01-01',
        ];

        $result = $this->calculator->calculateTrends($sql, $filters);

        $this->assertIsArray($result);
    }

    public function testBuildSqlFiltersWithNoFilters(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = [];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertEquals($sql, $resultSql);
        $this->assertEmpty($params);
        $this->assertEmpty($types);
    }

    public function testBuildSqlFiltersWithStartDate(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = ['start_date' => '2024-01-01'];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertStringContainsString('s.date >= :start_date', $resultSql);
        $this->assertEquals('2024-01-01', $params['start_date']);
    }

    public function testBuildSqlFiltersWithEndDate(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = ['end_date' => '2024-12-31'];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertStringContainsString('s.date <= :end_date', $resultSql);
        $this->assertEquals('2024-12-31', $params['end_date']);
    }

    public function testBuildSqlFiltersWithInstitutionId(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = ['institution_id' => 'supplier123'];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertStringContainsString('s.supplier_id = :institution_id', $resultSql);
        $this->assertEquals('supplier123', $params['institution_id']);
    }

    public function testBuildSqlFiltersWithInstitutionIds(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = ['institution_ids' => ['supplier123', 'supplier456']];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertStringContainsString('s.supplier_id IN (:institution_ids)', $resultSql);
        $this->assertEquals(['supplier123', 'supplier456'], $params['institution_ids']);
    }

    public function testBuildSqlFiltersWithAllFilters(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'institution_id' => 'supplier123',
        ];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertStringContainsString('s.date >= :start_date', $resultSql);
        $this->assertStringContainsString('s.date <= :end_date', $resultSql);
        $this->assertStringContainsString('s.supplier_id = :institution_id', $resultSql);
        $this->assertCount(3, $params);
    }

    public function testBuildSqlFiltersIgnoresEmptyStrings(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = [
            'start_date' => '',
            'institution_id' => '',
        ];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertEquals($sql, $resultSql);
        $this->assertEmpty($params);
    }

    public function testBuildSqlFiltersIgnoresEmptyArrays(): void
    {
        $sql = 'SELECT * FROM train_supervisor_data s WHERE 1=1';
        $filters = ['institution_ids' => []];

        [$resultSql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters);

        $this->assertEquals($sql, $resultSql);
        $this->assertEmpty($params);
    }

    /**
     * @param array<int, array{date: string, count: int}> $periodData
     */
    #[DataProvider('growthRateDataProvider')]
    public function testCalculateGrowthRate(array $periodData, float $expected): void
    {
        $result = $this->calculator->calculateGrowthRate($periodData);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<string, array{0: array<int, array{date: string, count: int}>, 1: float}>
     */
    public static function growthRateDataProvider(): array
    {
        return [
            'empty data' => [[], 0.0],
            'single period' => [[['date' => '2024-01-01', 'count' => 100]], 0.0],
            'growth from 100 to 150' => [
                [
                    ['date' => '2024-01-01', 'count' => 100],
                    ['date' => '2024-01-02', 'count' => 150],
                ],
                50.0,
            ],
            'decline from 100 to 80' => [
                [
                    ['date' => '2024-01-01', 'count' => 100],
                    ['date' => '2024-01-02', 'count' => 80],
                ],
                -20.0,
            ],
            'zero previous value' => [
                [
                    ['date' => '2024-01-01', 'count' => 0],
                    ['date' => '2024-01-02', 'count' => 100],
                ],
                0.0,
            ],
        ];
    }

    /**
     * @param array<int, array{date: string, count: int}> $periodData
     */
    #[DataProvider('averageOnlineDataProvider')]
    public function testCalculateAverageOnline(array $periodData, float $expected): void
    {
        $result = $this->calculator->calculateAverageOnline($periodData);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<string, array{0: array<int, array{date: string, count: int}>, 1: float}>
     */
    public static function averageOnlineDataProvider(): array
    {
        return [
            'empty data' => [[], 0.0],
            'single period' => [[['date' => '2024-01-01', 'count' => 100]], 100.0],
            'multiple periods' => [
                [
                    ['date' => '2024-01-01', 'count' => 100],
                    ['date' => '2024-01-02', 'count' => 200],
                    ['date' => '2024-01-03', 'count' => 150],
                ],
                150.0,
            ],
        ];
    }

    /**
     * @param array{absolute: int, percentage: float, direction: string} $expected
     */
    #[DataProvider('changeDataProvider')]
    public function testCalculateChange(int $current, int $previous, array $expected): void
    {
        $result = $this->calculator->calculateChange($current, $previous);

        $this->assertEquals($expected['absolute'], $result['absolute']);
        $this->assertEquals($expected['percentage'], $result['percentage']);
        $this->assertEquals($expected['direction'], $result['direction']);
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: array{absolute: int, percentage: float, direction: string}}>
     */
    public static function changeDataProvider(): array
    {
        return [
            'increase' => [150, 100, ['absolute' => 50, 'percentage' => 50.0, 'direction' => 'up']],
            'decrease' => [80, 100, ['absolute' => -20, 'percentage' => -20.0, 'direction' => 'down']],
            'stable' => [100, 100, ['absolute' => 0, 'percentage' => 0.0, 'direction' => 'stable']],
            'from zero' => [100, 0, ['absolute' => 100, 'percentage' => 0.0, 'direction' => 'up']],
        ];
    }

    public function testIsSqlite(): void
    {
        $result = $this->calculator->isSqlite();

        $this->assertIsBool($result);
    }
}
