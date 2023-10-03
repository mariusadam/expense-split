<?php

namespace App\Tests;

use App\MatrixBasedExpenseSplit;
use PHPUnit\Framework\TestCase;

class MatrixBasedExpenseSplitTest extends TestCase
{
    private MatrixBasedExpenseSplit $expenseSplit;

    protected function setUp(): void
    {
        $this->expenseSplit = new MatrixBasedExpenseSplit();
    }

    public function testSplitWithNoUsers(): void
    {
        $this->assertSame([], $this->expenseSplit->split());
    }

    public function testSplitWithNoExpenses(): void
    {
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addUser('mike');

        $expected = [
            'jane' => [],
            'mike' => [],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }

    public function testAddExpenseWithUnknownUser(): void
    {
        $this->expectExceptionMessage('Unknown user "mike".');

        $this->expenseSplit->addExpense(1, 'mike', '');
    }

    public function testSplitTwoUsers(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addExpense(10, 'mike', '');

        $expected = [
            'john' => [
                'mike' => 5.0,
            ],
            'mike' => [],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }

    public function testSplitTwoUsersUneven(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addExpense(25, 'mike', '');
        $this->expenseSplit->addExpense(5, 'john', '');

        $expected = [
            'john' => [
                'mike' => 10.0,
            ],
            'mike' => [],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }

    public function testSplitThreeUsersEven(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addExpense(5, 'mike', '');
        $this->expenseSplit->addExpense(5, 'jane', '');
        $this->expenseSplit->addExpense(5, 'john', '');

        $expected = [
            'john' => [],
            'mike' => [],
            'jane' => [],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }

    public function testSplitThreeUsers(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addUser('greg');
        $this->expenseSplit->addExpense(10, 'jane', '');
        $this->expenseSplit->addExpense(10, 'greg', '');
        $this->expenseSplit->addExpense(40, 'mike', '');

        $expected = [
            'john' => [
                'jane' => 2.5,
                'mike' => 10.0,
                'greg' => 2.5,
            ],
            'jane' => [
                'mike' => 7.5,
            ],
            'mike' => [],
            'greg' => [
                'mike' => 7.5,
            ],
        ];
        $this->assertEquals($expected, $this->expenseSplit->split());
    }

    public function testSplitThreeUsersTwoTransactions(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addExpense(6, 'mike', '');
        $this->expenseSplit->addExpense(3, 'jane', '');

        $expected = [
            'john' => [
                'jane' => 1.0,
                'mike' => 2.0,
            ],
            'jane' => [
                'mike' => 1.0,
            ],
            'mike' => [
            ],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }

    public function testSplitThreeUsersTwoTransactionsOther(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addUser('greg');
        $this->expenseSplit->addUser('abcd');
        $this->expenseSplit->addExpense(6, 'mike', '');
        $this->expenseSplit->addExpense(5, 'john', '');
        $this->expenseSplit->addExpense(1, 'jane', '');
        $this->expenseSplit->addExpense(1, 'greg', '');

        $expected = [
            'john' => [
                    'mike' => 0.19999999999999996,
                ],
            'jane' => [
                    'john' => 0.8,
                    'mike' => 1.0,
                ],
            'greg' => [
                    'john' => 0.8,
                    'mike' => 1.0,
                ],
            'abcd' => [
                    'john' => 1.0,
                    'jane' => 0.2,
                    'mike' => 1.2,
                    'greg' => 0.2,
                ],
            'mike' => [],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }
}
