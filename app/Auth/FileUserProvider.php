<?php

namespace App\Auth;

use App\Models\User;
use App\Support\LocalUserStore;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class FileUserProvider implements UserProvider
{
    public function __construct(private readonly LocalUserStore $users)
    {
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $record = $this->users->findById((string) $identifier);

        return $record ? $this->makeUser($record) : null;
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void
    {
        // Remember-me login is not enabled for this prototype.
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        $contact = $credentials['email'] ?? $credentials['contact'] ?? null;

        if (! is_string($contact) || trim($contact) === '') {
            return null;
        }

        $record = $this->users->findByContact($contact);

        return $record ? $this->makeUser($record) : null;
    }

    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        $password = $credentials['password'] ?? null;

        return is_string($password) && Hash::check($password, $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
        $password = $credentials['password'] ?? null;

        if (! is_string($password)) {
            return;
        }

        $currentHash = $user->getAuthPassword();
        if ($force || Hash::needsRehash($currentHash)) {
            $newHash = Hash::make($password);
            $this->users->updatePassword($user->getAuthIdentifier(), $newHash);
            $user->forceFill(['password' => $newHash]);
        }
    }

    /** @param array<string, mixed> $record */
    private function makeUser(array $record): User
    {
        $user = new User;
        $user->forceFill([
            'id' => $record['id'],
            'name' => $record['name'],
            'email' => $record['email'] ?? $record['phone'] ?? null,
            'phone' => $record['phone'] ?? null,
            'password' => $record['password'],
            'created_at' => $record['created_at'],
        ]);

        return $user;
    }
}
