<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Resolvers\ExpressionResolver;
use DanDoeTech\LaravelResourceRegistry\Resolvers\RelationCountResolver;
use DanDoeTech\LaravelResourceRegistry\Resolvers\RelationFieldResolver;
use DanDoeTech\LaravelResourceRegistry\Resolvers\RelationPluckResolver;
use DanDoeTech\LaravelResourceRegistry\Resolvers\ViaResolverFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ViaResolverFactoryTest extends TestCase
{
    private ViaResolverFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ViaResolverFactory();
    }

    #[Test]
    public function it_creates_relation_field_resolver_for_dot_notation(): void
    {
        $resolver = $this->factory->create('category.name', 'category_name');

        $this->assertInstanceOf(RelationFieldResolver::class, $resolver);
    }

    #[Test]
    public function it_creates_relation_count_resolver_for_count_prefix(): void
    {
        $resolver = $this->factory->create('count:orders', 'orders_count');

        $this->assertInstanceOf(RelationCountResolver::class, $resolver);
    }

    #[Test]
    public function it_creates_relation_pluck_resolver_for_pluck_prefix(): void
    {
        $resolver = $this->factory->create('pluck:categories.name', 'category_labels');

        $this->assertInstanceOf(RelationPluckResolver::class, $resolver);
    }

    #[Test]
    #[DataProvider('validRelationFieldProvider')]
    public function it_parses_various_relation_field_patterns(string $via): void
    {
        $resolver = $this->factory->create($via, 'alias');

        $this->assertInstanceOf(RelationFieldResolver::class, $resolver);
    }

    /** @return iterable<string, array{string}> */
    public static function validRelationFieldProvider(): iterable
    {
        yield 'simple' => ['category.name'];
        yield 'underscore relation' => ['order_item.quantity'];
        yield 'underscore field' => ['user.first_name'];
    }

    #[Test]
    #[DataProvider('validCountProvider')]
    public function it_parses_various_count_patterns(string $via): void
    {
        $resolver = $this->factory->create($via, 'alias');

        $this->assertInstanceOf(RelationCountResolver::class, $resolver);
    }

    /** @return iterable<string, array{string}> */
    public static function validCountProvider(): iterable
    {
        yield 'simple' => ['count:orders'];
        yield 'underscore relation' => ['count:order_items'];
    }

    #[Test]
    #[DataProvider('validPluckProvider')]
    public function it_parses_various_pluck_patterns(string $via): void
    {
        $resolver = $this->factory->create($via, 'alias');

        $this->assertInstanceOf(RelationPluckResolver::class, $resolver);
    }

    /** @return iterable<string, array{string}> */
    public static function validPluckProvider(): iterable
    {
        yield 'simple' => ['pluck:categories.name'];
        yield 'underscore relation' => ['pluck:tag_groups.label'];
    }

    #[Test]
    public function it_creates_expression_resolver_for_expr_prefix(): void
    {
        $resolver = $this->factory->create('expr:re_betrag_partner - re_betrag_wtt', 'differenz');

        $this->assertInstanceOf(ExpressionResolver::class, $resolver);
    }

    #[Test]
    #[DataProvider('validExpressionProvider')]
    public function it_parses_various_expression_patterns(string $via): void
    {
        $resolver = $this->factory->create($via, 'alias');

        $this->assertInstanceOf(ExpressionResolver::class, $resolver);
    }

    /** @return iterable<string, array{string}> */
    public static function validExpressionProvider(): iterable
    {
        yield 'subtraction' => ['expr:a - b'];
        yield 'addition' => ['expr:price + tax'];
        yield 'multiplication' => ['expr:quantity * unit_price'];
        yield 'with parens' => ['expr:(a + b) * c'];
        yield 'numeric literal' => ['expr:price * 1.19'];
    }

    #[Test]
    #[DataProvider('invalidViaProvider')]
    public function it_throws_on_invalid_via_format(string $via): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->create($via, 'alias');
    }

    /** @return iterable<string, array{string}> */
    public static function invalidViaProvider(): iterable
    {
        yield 'no dot or prefix' => ['category'];
        yield 'empty string' => [''];
        yield 'dot at start' => ['.name'];
        yield 'dot at end' => ['category.'];
        yield 'count with empty relation' => ['count:'];
        yield 'pluck without dot' => ['pluck:categories'];
        yield 'pluck with dot at start' => ['pluck:.name'];
        yield 'pluck with dot at end' => ['pluck:categories.'];
        yield 'expr with empty expression' => ['expr:'];
        yield 'expr with whitespace only' => ['expr:   '];
        yield 'expr with sql injection' => ['expr:a; DROP TABLE users'];
    }
}
