<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles via: 'expr:field_a - field_b' — arithmetic expressions between columns.
 *
 * Only allows column names (word chars), numeric literals, basic arithmetic
 * operators (+, -, *, /), parentheses, and whitespace. Rejects anything else
 * to prevent SQL injection.
 */
final class ExpressionResolver implements EloquentComputedResolverInterface
{
    /** SQL keywords that must never appear in expressions. */
    private const SQL_KEYWORDS = [
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE',
        'TRUNCATE', 'EXEC', 'EXECUTE', 'UNION', 'INTO', 'FROM', 'WHERE',
        'TABLE', 'DATABASE', 'GRANT', 'REVOKE', 'HAVING', 'GROUP', 'ORDER',
    ];

    /**
     * Tokens allowed in expressions: column names (with optional table prefix),
     * numeric literals, arithmetic operators, parentheses, whitespace.
     * No consecutive operators like '--' or '/*'.
     */
    private const TOKEN_PATTERN = '/^(?:[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)? | \d+(?:\.\d+)? | [+\-*\/] | [()]| \s+)+$/x';

    public function __construct(
        private readonly string $expression,
        private readonly string $alias,
    ) {
        if (! self::isValidExpression($expression)) {
            throw new \InvalidArgumentException(
                "Invalid expression: '{$expression}'. Only column names, numbers, arithmetic operators (+, -, *, /), and parentheses are allowed.",
            );
        }
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        /** @var Builder<Model> */
        return $query->selectRaw("({$this->expression}) as {$this->alias}");
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function filter(Builder $query, mixed $value, string $operator = '='): Builder
    {
        /** @var Builder<Model> */
        return $query->having($this->alias, $operator, $value);
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function sort(Builder $query, string $direction): Builder
    {
        /** @var Builder<Model> */
        return $query->orderBy($this->alias, $direction);
    }

    /**
     * Validates that the expression only contains safe tokens:
     * column names, numeric literals, arithmetic operators, parentheses, whitespace.
     * Rejects SQL keywords and comment sequences (-- or /*).
     */
    public static function isValidExpression(string $expression): bool
    {
        $trimmed = \trim($expression);

        if ($trimmed === '') {
            return false;
        }

        // Reject comment sequences
        if (\str_contains($trimmed, '--') || \str_contains($trimmed, '/*') || \str_contains($trimmed, '*/')) {
            return false;
        }

        // Check token structure
        if (! \preg_match(self::TOKEN_PATTERN, $trimmed)) {
            return false;
        }

        // Reject SQL keywords as standalone tokens
        $words = \preg_split('/[^a-zA-Z_]+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false) {
            return false;
        }

        foreach ($words as $word) {
            if (\in_array(\strtoupper($word), self::SQL_KEYWORDS, true)) {
                return false;
            }
        }

        return true;
    }
}
