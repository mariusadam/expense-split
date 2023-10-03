<?php

namespace App;

interface IExpenseSplit {

    /**
     * Adds a user to the list
     *
     * @param string $name
     */
    public function addUser(string $name): void;

    /**
     * Adds an expense to the list to be split
     *
     * @param float $amount amount to be split
     * @param string $userName
     * @param string $comment
     * @return void
     */
    public function addExpense(float $amount, string $userName, string $comment): void;

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
    public function split(): array;
}
