<?php

namespace App;

use InvalidArgumentException;

class MinimalExpenseSplit implements IExpenseSplit
{
    private array $expensesByUser = [];
    private array $users = [];
    private float $totalAmount = 0;

    public function addUser(string $name): void
    {
        $this->users[$name] = true;
    }

    public function addExpense(float $amount, string $userName, string $comment): void
    {
        if (!isset($this->users[$userName])) {
            throw new InvalidArgumentException(sprintf('Unknown user "%s".', $userName));
        }

        if (!isset($this->expensesByUser[$userName])) {
            $this->expensesByUser[$userName] = [];
        }

        $this->expensesByUser[$userName][] = [
            'amount' => $amount,
            'comment' => $comment,
        ];
        $this->totalAmount += $amount;
    }

    /**
     * Calculates which user what amount owes to the other users
     *
     * @return array Eg. [
     *  'john' => [
     *      'jane' => 10,
     *      'mike' => 20
     *   ],
     *   'jane' => [
     *       'mike' => 10
     *   ],
     *   'mike' => [
     *   ]
     * ]
     */
    public function split(): array
    {
        $amountPaidByUser = [];
        foreach (array_keys($this->users) as $user) {
            $amountPaidByUser[$user] = $this->computeTotalExpensesForUser($user);
        }

        $allUsers = array_keys($amountPaidByUser);
        $matrix = [];
        foreach ($allUsers as $payingUser) {
            foreach ($allUsers as $receivingUser) {
                $matrix[$payingUser][$receivingUser] = 0;
            }
        }
        foreach ($amountPaidByUser as $receivingUser => $amount) {
            $amountToBePaid = $amount / count($allUsers);
            foreach ($allUsers as $payingUser) {
                $matrix[$payingUser][$receivingUser] += $amountToBePaid;
            }
        }

        $split = [];
        foreach ($allUsers as $payingUser) {
            $split[$payingUser] = [];
            foreach ($allUsers as $receivingUser) {
                $paid = $matrix[$payingUser][$receivingUser];
                $toPay = $matrix[$receivingUser][$payingUser];
                $diff = $toPay - $paid;
                if ($diff > 0) {
                    $split[$receivingUser][$payingUser] = $diff;
                }
                if ($diff < 0) {
                    $split[$payingUser][$receivingUser] = abs($diff);
                }
            }
        }

        return $split;
    }

    private function computeTotalExpensesForUser(string $user): float
    {
        $total = 0;
        foreach ($this->expensesByUser[$user] ?? [] as $expense) {
            $total += $expense['amount'];
        }

        return $total;
    }
}
