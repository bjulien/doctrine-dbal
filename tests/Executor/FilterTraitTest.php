<?php

declare(strict_types=1);

namespace Tests\RulerZ\Executor;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use RulerZ\Context\ExecutionContext;
use Tests\RulerZ\Stub\DBALExecutorStub;

class FilterTraitTest extends TestCase
{
    /** @var DBALExecutorStub */
    private $executor;

    public function setUp()
    {
        $this->executor = new DBALExecutorStub();
    }

    public function testItCanApplyAFilterOnATarget()
    {
        $sql = 'some SQL generated by RulerZ';

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder->expects($this->once())->method('setParameter')->with('foo', 'bar', $type = null);
        $queryBuilder->expects($this->once())->method('andWhere')->with($sql);

        DBALExecutorStub::$executeReturn = $sql;

        $filteretTarget = $this->executor->applyFilter($queryBuilder, $parameters = ['foo' => 'bar'], $operators = [], new ExecutionContext());

        $this->assertSame($queryBuilder, $filteretTarget, 'The trait is called and it returns the result generated by the executor');
    }

    public function testItCanExecuteTheRequestAndReturnTheResultsAsATraversableObject()
    {
        $sql = 'some SQL generated by RulerZ';
        $results = ['result'];

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $statement = $this->createMock(Statement::class);

        $queryBuilder->expects($this->once())->method('setParameter')->with('foo', 'bar', $type = null);
        $queryBuilder->expects($this->once())->method('andWhere')->with($sql);
        $queryBuilder->expects($this->once())->method('execute')->willReturn($statement);
        $statement->expects($this->once())->method('fetchAll')->willReturn($results);

        DBALExecutorStub::$executeReturn = $sql;

        $returnedResults = $this->executor->filter($queryBuilder, $parameters = ['foo' => 'bar'], $operators = [], new ExecutionContext());

        $this->assertInstanceOf(\Traversable::class, $returnedResults);
        $this->assertEquals($results, iterator_to_array($returnedResults));
    }
}