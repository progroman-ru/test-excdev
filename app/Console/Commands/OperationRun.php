<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Service\BalancesService;
use Illuminate\Console\Command;

class OperationRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operation:run
                            {user : Email или ID пользователя}
                            {operation : Операция, например "+1000" или "-123.45"}
                            {--D|description= : Описание}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проведение операций по балансу пользователя';

    /**
     * Execute the console command.
     */
    public function handle(BalancesService $service)
    {
        if (!($user = $this->findUser())) {
            $this->error('Пользователь не найден');
            return 0;
        }

        $result = $service->runOperation($user, $this->argument('operation'), $this->option('description'));

        if ($result) {
            $this->info('Успешно');
        } else {
            $this->error($service->getLastError());
        }
    }

    /**
     * Поиск пользователя по данным в параметрах
     * @return User|object|null
     */
    private function findUser()
    {
        $query = User::query();

        $userArgument = $this->argument('user');
        if (is_int($userArgument)) {
            $query->where('id', $userArgument);
        } else {
            $query->where('email', $userArgument);
        }

        return $query->first();
    }
}
