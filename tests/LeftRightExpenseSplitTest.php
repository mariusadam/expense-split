<?php

namespace App\Tests;

use App\LeftRightExpenseSplit;
use PHPUnit\Framework\TestCase;

class LeftRightExpenseSplitTest extends TestCase
{
    private LeftRightExpenseSplit $expenseSplit;

    protected function setUp(): void
    {
        $this->expenseSplit = new LeftRightExpenseSplit();
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
                'mike' => 15.0,
            ],
            'jane' => [
                'mike' => 5.0,
            ],
            'mike' => [],
            'greg' => [
                'mike' => 5.0,
            ],
        ];
        $this->assertEquals($expected, $this->expenseSplit->split());
    }

    public function testSplitThreeUsersTwoInitialExpenses(): void
    {
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addExpense(6, 'mike', '');
        $this->expenseSplit->addExpense(3, 'jane', '');

        $expected = [
            'john' => [
                'mike' => 3.0,
            ],
            'jane' => [
            ],
            'mike' => [
            ],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }

    public function testSplitThreeUsersThreeInitialExpenses(): void
    {
        $this->expenseSplit->addUser('mike');
        $this->expenseSplit->addUser('john');
        $this->expenseSplit->addUser('jane');
        $this->expenseSplit->addExpense(5, 'mike', '');
        $this->expenseSplit->addExpense(3, 'jane', '');
        $this->expenseSplit->addExpense(1, 'john', '');

        $expected = [
            'mike' => [
            ],
            'john' => [
                'mike' => 2.0,
            ],
            'jane' => [
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
            ],
            'jane' => [
                'mike' => 0.7999999999999998,
                'john' => 0.8000000000000003,
            ],
            'mike' => [],
            'greg' => [
                'john' => 1.5999999999999992,
            ],
            'abcd' => [
                'mike' => 2.6,
            ],
        ];
        $this->assertSame($expected, $this->expenseSplit->split());
    }
}
