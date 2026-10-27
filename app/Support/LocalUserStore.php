<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use JsonException;
use RuntimeException;

class LocalUserStore
{
    public function __construct(private readonly string $path)
    {
    }

    public static function normalizeContact(string $contact): string
    {
        $contact = trim($contact);

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return mb_strtolower($contact);
        }

        return preg_replace('/[\s().-]+/u', '', $contact) ?? $contact;
    }

    public static function isValidContact(string $contact): bool
    {
        $contact = trim($contact);

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return preg_match('/^\+?[\d\s().-]{8,20}$/', $contact) === 1
            && strlen(preg_replace('/\D/', '', $contact) ?? '') >= 8;
    }

    /** @return array<string, mixed>|null */
    public function findByContact(string $contact): ?array
    {
        $normalized = self::normalizeContact($contact);

        return $this->withLockedRecords(LOCK_SH, function ($handle, array $records) use ($normalized): ?array {
            foreach ($records as $record) {
                if (self::normalizeContact((string) ($record['contact'] ?? $record['email'] ?? $record['phone'] ?? '')) === $normalized) {
                    return $record;
                }
            }

            return null;
        });
    }

    /** @return array<string, mixed>|null */
    public function findById(string|int $id): ?array
    {
        return $this->withLockedRecords(LOCK_SH, function ($handle, array $records) use ($id): ?array {
            foreach ($records as $record) {
                if ((string) ($record['id'] ?? '') === (string) $id) {
                    return $record;
                }
            }

            return null;
        });
    }

    /** @return array<string, mixed>|null Returns null if the contact is already registered. */
    public function create(string $name, string $contact, string $password): ?array
    {
        $contact = trim($contact);
        $normalized = self::normalizeContact($contact);

        return $this->withLockedRecords(LOCK_EX, function ($handle, array $records) use ($name, $contact, $normalized, $password): ?array {
            foreach ($records as $record) {
                if (self::normalizeContact((string) ($record['contact'] ?? $record['email'] ?? $record['phone'] ?? '')) === $normalized) {
                    return null;
                }
            }

            $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL) !== false;
            $record = [
                'id' => (max(array_map(fn (array $user): int => (int) ($user['id'] ?? 0), $records) ?: [0]) + 1),
                'name' => trim($name),
                'email' => $isEmail ? mb_strtolower($contact) : null,
                'phone' => $isEmail ? null : $contact,
                'contact' => $normalized,
                'password' => Hash::make($password),
                'created_at' => now()->toIso8601String(),
            ];

            $records[] = $record;
            $this->writeRecords($handle, $records);

            return $record;
        });
    }

    public function updatePassword(string|int $id, string $passwordHash): void
    {
        $this->withLockedRecords(LOCK_EX, function ($handle, array $records) use ($id, $passwordHash): void {
            foreach ($records as &$record) {
                if ((string) ($record['id'] ?? '') === (string) $id) {
                    $record['password'] = $passwordHash;
                    unset($record);
                    $this->writeRecords($handle, $records);

                    return;
                }
            }
            unset($record);
        });
    }

    /** @template T @param callable(resource|null, array<int, array<string, mixed>>): T $callback @return T */
    private function withLockedRecords(int $lock, callable $callback): mixed
    {
        if ($lock === LOCK_SH && ! is_file($this->path)) {
            return $callback(null, []);
        }

        File::ensureDirectoryExists(dirname($this->path), 0700, true);
        $handle = @fopen($this->path, 'c+b');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the local user store.');
        }

        try {
            if (! flock($handle, $lock)) {
                throw new RuntimeException('Unable to lock the local user store.');
            }

            return $callback($handle, $this->readRecords($handle));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @param resource $handle @return array<int, array<string, mixed>> */
    private function readRecords($handle): array
    {
        rewind($handle);
        $contents = stream_get_contents($handle);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        try {
            $records = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The local user store contains invalid JSON.', previous: $exception);
        }

        if (! is_array($records) || ! array_is_list($records)) {
            throw new RuntimeException('The local user store has an invalid structure.');
        }

        return $records;
    }

    /** @param resource $handle @param array<int, array<string, mixed>> $records */
    private function writeRecords($handle, array $records): void
    {
        try {
            $contents = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode the local user store.', previous: $exception);
        }

        rewind($handle);
        if (! ftruncate($handle, 0) || fwrite($handle, $contents) !== strlen($contents) || ! fflush($handle)) {
            throw new RuntimeException('Unable to write the local user store.');
        }
    }
}
