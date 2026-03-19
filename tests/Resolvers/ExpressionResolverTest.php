<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Resolvers\ExpressionResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExpressionResolverTest extends TestCase
{
    #[Test]
    #[DataProvider('validExpressionProvider')]
    public function it_accepts_valid_expressions(string $expression): void
    {
        $resolver = new ExpressionResolver($expression, 'alias');

        $this->assertInstanceOf(ExpressionResolver::class, $resolver);
    }

    /** @return iterable<string, array{string}> */
    public static function validExpressionProvider(): iterable
    {
        yield 'subtraction' => ['re_betrag_partner - re_betrag_wtt'];
        yield 'addition' => ['price + tax'];
        yield 'multiplication' => ['quantity * unit_price'];
        yield 'division' => ['total / count'];
        yield 'with parentheses' => ['(price + tax) * quantity'];
        yield 'numeric literal' => ['price * 1.19'];
        yield 'mixed operators' => ['a + b - c * d / e'];
        yield 'nested parens' => ['((a + b) * (c - d))'];
        yield 'column with table prefix' => ['t.column + o.column'];
    }

    #[Test]
    #[DataProvider('invalidExpressionProvider')]
    public function it_rejects_invalid_expressions(string $expression): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid expression');

        new ExpressionResolver($expression, 'alias');
    }

    /** @return iterable<string, array{string}> */
    public static function invalidExpressionProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'semicolon injection' => ['a; DROP TABLE users'];
        yield 'sql comment dashes' => ['a -- comment'];
        yield 'sql comment block' => ['a /* comment */'];
        yield 'single quotes' => ["a + 'b'"];
        yield 'double quotes' => ['a + "b"'];
        yield 'equals sign' => ['a = b'];
        yield 'comma' => ['a, b'];
        yield 'select keyword' => ['SELECT 1'];
        yield 'drop keyword' => ['DROP something'];
        yield 'union keyword' => ['a UNION b'];
    }

    #[Test]
    public function is_valid_expression_returns_true_for_safe_input(): void
    {
        $this->assertTrue(ExpressionResolver::isValidExpression('a - b'));
    }

    #[Test]
    public function is_valid_expression_returns_false_for_dangerous_input(): void
    {
        $this->assertFalse(ExpressionResolver::isValidExpression('a; DROP TABLE'));
    }
}
