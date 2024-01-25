<?php

namespace App\Service;

use App\Models\Balance;
use App\Models\BalancesAmounts;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use \Exception;

/**
 * Сервис для работы с транзакциями пользователей
 */
class BalancesService
{
    private string $error = '';

    /**
     * Выполняет операцию
     * @param User $user
     * @param string $operation
     * @param string $description
     * @return bool Возвращает true при успешном завершении, false в случае ошибки. Текст ошибки можно получить через getLastError()
     */
    public function runOperation(User $user, string $operation, string $description) : bool
    {
        if (!($balance = $this->getMainBalance($user))) {
            $this->setError('Баланс не найден');
            return false;
        }

        DB::beginTransaction();

        try {
            $this->applyOperationToBalance($balance, $operation);
            $this->saveOperation($balance->id, $operation, $description);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Применяет операцию к балансу
     * @param Balance $balance
     * @param string $operation
     * @return void
     * @throws Exception
     */
    private function applyOperationToBalance(Balance $balance, string $operation)
    {
        list('operator' => $operator, 'value' => $value) = $this->parseOperation($operation);
        $latestAmount = $balance->latestAmount ? $balance->latestAmount->amount : 0;
        $amount = match ($operator) {
            '+' => $latestAmount + $value,
            '-' => $latestAmount - $value
        };

        $this->validateAmount($amount);

        BalancesAmounts::create([
            'balance_id' => $balance->id,
            'amount' => $amount
        ]);
    }

    /**
     * Записывает операцию в БД
     * @param int $balanceId
     * @param string $operation
     * @param string $description
     * @return void
     */
    private function saveOperation(int $balanceId, string $operation, string $description)
    {
        Operation::create([
            'balance_id' => $balanceId,
            'operation' => $operation,
            'description' => $description
        ]);
    }

    /**
     * Парсит строковое представление операции
     * @param string $operation
     * @return array Массив ['operator' => '', 'value' => '']
     * @throws Exception
     */
    private function parseOperation(string $operation) : array
    {
        if (!preg_match('#^(\+|-)(\d+)#', $operation, $matches)) {
            throw new Exception('Недопустимая операция ' . $operation);
        }

        return [
            'operator' => $matches[1],
            'value' => $matches[2]
        ];
    }

    /**
     * Проверка
     */
    private function validateAmount(float $amount)
    {
        if ($amount < 0) {
            throw new Exception('Операция отклонена: недостаточно средств');
        }
    }

    /**
     * Возвращает баланс пользователя
     * @param User $user
     * @return Balance|null
     */
    private function getMainBalance(User $user) : Balance|null
    {
        return Balance::where('user_id', $user->id)
            ->orderBy('created_at')
            ->first();
    }

    private function setError(string $error)
    {
        $this->error = $error;
    }

    public function getLastError() : string
    {
        return $this->error;
    }
}
