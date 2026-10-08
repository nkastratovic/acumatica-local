<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('app:create-user {email : Email address used to sign in}
    {--name= : Display name}
    {--role=admin : Role name to assign (admin, manager, viewer, ...)}
    {--password= : Password (prompted securely when omitted)}')]
#[Description('Create a user from the command line, e.g. the first administrator')]
class CreateUser extends Command
{
    public function handle(): int
    {
        $role = Role::where('name', $this->option('role'))->first();

        if (! $role) {
            $this->error("Role [{$this->option('role')}] not found. Run: php artisan db:seed --class=RolesAndPermissionsSeeder");

            return self::FAILURE;
        }

        $data = [
            'email' => $this->argument('email'),
            'name' => $this->option('name') ?: text('Name', required: true),
            'password' => $this->option('password') ?: password('Password', required: true, hint: 'Min 12 chars, upper and lower case letters and numbers.'),
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create($data + ['is_active' => true, 'must_change_password' => false]);
        $user->roles()->attach($role);

        $this->info("Created {$user->email} with role [{$role->name}].");

        return self::SUCCESS;
    }
}
