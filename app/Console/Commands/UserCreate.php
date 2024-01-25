<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {name} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает нового пользователя';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = $this->arguments();
        $data['password'] = $this->secret('Пароль (мин-м 8 символов):');

        if (!$this->validate($data)) {
            return 0;
        }

        $user = new User();
        $user->password = Hash::make($data['password']);
        $user->email = $data['email'];
        $user->name = $data['name'];
        $user->save();

        $this->info('Пользователь создан');
    }

    private function validate(array $data) : bool
    {
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        if ($validator->fails()) {
            $this->error('Ошибка:');

            collect($validator->errors()->all())
                ->each(fn ($error) => $this->line($error));

            return false;
        }

        return true;
    }
}
