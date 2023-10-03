<?php

namespace App;

use InvalidArgumentException;

/**
 * Implementation that splits the expenses by first sorting the users by the amount contributed initially,
 * and then starting from the user which contributed the least amount, allocate the required amount to the user which contributed the most
 */
class LeftRightExpenseSplit implements IExpenseSplit
{
    const IGNORABLE_DIFF = 0.0000001;
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
        $numberOfUsers = count($this->users);
        if (0 === $numberOfUsers) {
            return [];
        }

        $amountToBePaidByEachUser = $this->totalAmount / $numberOfUsers;

        $userExpenseMap = $this->buildUserExpenseMap();
        $orderedUsers = $this->getOrderedUserList($userExpenseMap);

        $left = 0;
        $right = $numberOfUsers - 1;
        $split = $this->initializeSplit();
        while ($left < $right) {
            $payingUser = $orderedUsers[$left];
            $receivingUser = $orderedUsers[$right];
            $maxAmountToPay = $amountToBePaidByEachUser - $userExpenseMap[$payingUser];
            $maxAmountToReceive = $userExpenseMap[$receivingUser] - $amountToBePaidByEachUser;
            $amountExchanged = min($maxAmountToPay, $maxAmountToReceive);
            if (abs($amountExchanged) > self::IGNORABLE_DIFF) {
                $userExpenseMap[$payingUser] += $amountExchanged;
                $userExpenseMap[$receivingUser] -= $amountExchanged;
                $split[$payingUser][$receivingUser] = $amountExchanged;
            }
            if (abs($userExpenseMap[$payingUser] - $amountToBePaidByEachUser) <= self::IGNORABLE_DIFF) {
                $left++;
            }
            if (abs($userExpenseMap[$receivingUser] - $amountToBePaidByEachUser) <= self::IGNORABLE_DIFF) {
                $right--;
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

    private function initializeSplit(): array
    {
        $split = [];
        foreach (array_keys($this->users) as $user) {
            $split[$user] = [];
        }

        return $split;
    }

    /**
     * @return array<string, float>
     */
    private function buildUserExpenseMap(): array
    {
        $expenseByUser = [];
        foreach (array_keys($this->users) as $user) {
            $expenseByUser[$user] = $this->computeTotalExpensesForUser($user);
        }

        return $expenseByUser;
    }

    /**
     * @param array<string, float> $expenseByUser
     * @return array<string>
     */
    private function getOrderedUserList(array $expenseByUser): array
    {
        $orderedUsers = array_keys($expenseByUser);
        usort($orderedUsers, fn(string $userX, string $userY) => $expenseByUser[$userX] <=> $expenseByUser[$userY]);

        return $orderedUsers;
    }
}
