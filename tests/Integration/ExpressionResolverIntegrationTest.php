<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Integration;

use DanDoeTech\LaravelResourceRegistry\Resolvers\ExpressionResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ExpressionResolverIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('expression_test', function (Blueprint $table): void {
            $table->id();
            $table->float('amount_a')->nullable();
            $table->float('amount_b')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('expression_test');

        parent::tearDown();
    }

    #[Test]
    public function apply_adds_arithmetic_expression_to_query(): void
    {
        DB::table('expression_test')->insert([
            ['amount_a' => 100.0, 'amount_b' => 30.0],
            ['amount_a' => 200.0, 'amount_b' => 50.0],
        ]);

        $resolver = new ExpressionResolver('amount_a - amount_b', 'differenz');

        /** @var Builder<Model> $query */
        $query = ExpressionTestModel::query()->select('*');
        $result = $resolver->apply($query)->get();

        $this->assertCount(2, $result);
        $this->assertEquals(70.0, $result[0]->getAttribute('differenz'));
        $this->assertEquals(150.0, $result[1]->getAttribute('differenz'));
    }

    #[Test]
    public function apply_handles_multiplication(): void
    {
        DB::table('expression_test')->insert([
            ['amount_a' => 10.0, 'amount_b' => 3.0],
        ]);

        $resolver = new ExpressionResolver('amount_a * amount_b', 'product');

        /** @var Builder<Model> $query */
        $query = ExpressionTestModel::query()->select('*');
        $result = $resolver->apply($query)->first();

        $this->assertNotNull($result);
        $this->assertEquals(30.0, $result->getAttribute('product'));
    }

    #[Test]
    public function apply_handles_numeric_literals(): void
    {
        DB::table('expression_test')->insert([
            ['amount_a' => 100.0, 'amount_b' => 0.0],
        ]);

        $resolver = new ExpressionResolver('amount_a * 1.19', 'with_tax');

        /** @var Builder<Model> $query */
        $query = ExpressionTestModel::query()->select('*');
        $result = $resolver->apply($query)->first();

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(119.0, $result->getAttribute('with_tax'), 0.01);
    }

    #[Test]
    public function sort_orders_by_computed_expression(): void
    {
        DB::table('expression_test')->insert([
            ['amount_a' => 100.0, 'amount_b' => 80.0],  // diff: 20
            ['amount_a' => 200.0, 'amount_b' => 50.0],  // diff: 150
            ['amount_a' => 50.0, 'amount_b' => 10.0],   // diff: 40
        ]);

        $resolver = new ExpressionResolver('amount_a - amount_b', 'differenz');

        /** @var Builder<Model> $query */
        $query = ExpressionTestModel::query()->select('*');
        $resolver->apply($query);
        $result = $resolver->sort($query, 'asc')->get();

        $this->assertCount(3, $result);
        $this->assertEquals(20.0, $result[0]->getAttribute('differenz'));
        $this->assertEquals(40.0, $result[1]->getAttribute('differenz'));
        $this->assertEquals(150.0, $result[2]->getAttribute('differenz'));
    }
}

final class ExpressionTestModel extends Model
{
    protected $table = 'expression_test';
}
