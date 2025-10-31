<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsQueryBuilder;

/**
 * @internal
 */
#[CoversClass(LearningStatisticsQueryBuilder::class)]
#[RunTestsInSeparateProcesses]
final class LearningStatisticsQueryBuilderTest extends AbstractIntegrationTestCase
{
    private LearningStatisticsQueryBuilder $queryBuilder;

    protected function onSetUp(): void
    {
        $this->queryBuilder = self::getService(LearningStatisticsQueryBuilder::class);
    }

    public function testGetEnrollmentByPeriodWithNoFilters(): void
    {
        $filters = [];

        $result = $this->queryBuilder->getEnrollmentByPeriod($filters);

        $this->assertIsArray($result);
    }

    public function testGetEnrollmentByPeriodWithDateFilters(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getEnrollmentByPeriod($filters);

        $this->assertIsArray($result);
        foreach ($result as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('date', $item);
            $this->assertArrayHasKey('count', $item);
            $this->assertIsString($item['date']);
            $this->assertIsInt($item['count']);
        }
    }

    public function testGetEnrollmentByPeriodWithInstitutionFilter(): void
    {
        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getEnrollmentByPeriod($filters);

        $this->assertIsArray($result);
    }

    public function testGetEnrollmentByPeriodWithInstitutionIdsFilter(): void
    {
        $filters = [
            'institution_ids' => ['supplier123', 'supplier456'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getEnrollmentByPeriod($filters);

        $this->assertIsArray($result);
    }

    public function testGetEnrollmentByInstitutionWithNoFilters(): void
    {
        $filters = [];

        $result = $this->queryBuilder->getEnrollmentByInstitution($filters);

        $this->assertIsArray($result);
    }

    public function testGetEnrollmentByInstitutionWithFilters(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getEnrollmentByInstitution($filters);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));

        foreach ($result as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('institution_name', $item);
            $this->assertArrayHasKey('count', $item);
            $this->assertIsString($item['institution_name']);
            $this->assertIsInt($item['count']);
        }
    }

    public function testGetCompletionByPeriodWithNoFilters(): void
    {
        $filters = [];

        $result = $this->queryBuilder->getCompletionByPeriod($filters);

        $this->assertIsArray($result);
    }

    public function testGetCompletionByPeriodWithDateFilters(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getCompletionByPeriod($filters);

        $this->assertIsArray($result);
        foreach ($result as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('date', $item);
            $this->assertArrayHasKey('count', $item);
            $this->assertIsString($item['date']);
            $this->assertIsInt($item['count']);
        }
    }

    public function testGetCompletionByInstitutionWithFilters(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getCompletionByInstitution($filters);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));

        foreach ($result as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('institution_name', $item);
            $this->assertArrayHasKey('count', $item);
            $this->assertIsString($item['institution_name']);
            $this->assertIsInt($item['count']);
        }
    }

    public function testGetOnlineByPeriodWithNoFilters(): void
    {
        $filters = [];

        $result = $this->queryBuilder->getOnlineByPeriod($filters);

        $this->assertIsArray($result);
    }

    public function testGetOnlineByPeriodWithDateFilters(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->queryBuilder->getOnlineByPeriod($filters);

        $this->assertIsArray($result);
        foreach ($result as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('date', $item);
            $this->assertArrayHasKey('count', $item);
            $this->assertIsString($item['date']);
            $this->assertIsInt($item['count']);
        }
    }

    public function testBuildBaseQueryWithNoFilters(): void
    {
        $filters = [];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
    }

    public function testBuildBaseQueryWithStartDate(): void
    {
        $filters = ['start_date' => '2024-01-01'];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(1, $parameters);
        $firstParameter = $parameters->first();
        $this->assertNotFalse($firstParameter);
        $this->assertEquals('2024-01-01', $firstParameter->getValue());
    }

    public function testBuildBaseQueryWithEndDate(): void
    {
        $filters = ['end_date' => '2024-12-31'];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(1, $parameters);
        $firstParameter = $parameters->first();
        $this->assertNotFalse($firstParameter);
        $this->assertEquals('2024-12-31', $firstParameter->getValue());
    }

    public function testBuildBaseQueryWithInstitutionId(): void
    {
        $filters = ['institution_id' => 'supplier123'];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(1, $parameters);
        $firstParameter = $parameters->first();
        $this->assertNotFalse($firstParameter);
        $this->assertEquals('supplier123', $firstParameter->getValue());
    }

    public function testBuildBaseQueryWithInstitutionIds(): void
    {
        $filters = ['institution_ids' => ['supplier123', 'supplier456']];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(1, $parameters);
        $firstParameter = $parameters->first();
        $this->assertNotFalse($firstParameter);
        $this->assertEquals(['supplier123', 'supplier456'], $firstParameter->getValue());
    }

    public function testBuildBaseQueryWithAllFilters(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'institution_id' => 'supplier123',
        ];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(3, $parameters);
    }

    public function testBuildBaseQueryIgnoresEmptyStrings(): void
    {
        $filters = [
            'start_date' => '',
            'end_date' => '',
            'institution_id' => '',
        ];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(0, $parameters);
    }

    public function testBuildBaseQueryIgnoresEmptyArrays(): void
    {
        $filters = ['institution_ids' => []];

        $qb = $this->queryBuilder->buildBaseQuery($filters);

        $this->assertInstanceOf(QueryBuilder::class, $qb);
        $parameters = $qb->getParameters();
        $this->assertCount(0, $parameters);
    }

    public function testGetEnrollmentByInstitutionReturnsMaxTenResults(): void
    {
        $filters = [];

        $result = $this->queryBuilder->getEnrollmentByInstitution($filters);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));
    }

    public function testGetCompletionByInstitutionReturnsMaxTenResults(): void
    {
        $filters = [];

        $result = $this->queryBuilder->getCompletionByInstitution($filters);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));
    }
}
