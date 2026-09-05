<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupService
{
    private const SETTING_KEY = 'last_backup_at';

    private const INTERVAL_HOURS = 6;

    private const KEEP_LAST = 60;

    public function backupsDirectory(): string
    {
        return storage_path('app/backups');
    }

    public function externalPath(): ?string
    {
        return Setting::get('backup_external_path') ?: null;
    }

    /**
     * Validates that a path exists (or can be created) and is actually
     * writable before it's trusted as a backup destination - a silently
     * wrong path here is exactly the kind of failure that would only be
     * discovered after the data it was supposed to protect is already gone.
     */
    public function setExternalPath(?string $path): bool
    {
        $path = $path ? trim($path) : null;

        if (! $path) {
            Setting::set('backup_external_path', '');

            return true;
        }

        if (! File::isDirectory($path)) {
            try {
                File::makeDirectory($path, 0755, true);
            } catch (\Throwable) {
                return false;
            }
        }

        $probe = $path.DIRECTORY_SEPARATOR.'.write-test-'.uniqid();

        if (@file_put_contents($probe, 'x') === false) {
            return false;
        }

        @unlink($probe);

        Setting::set('backup_external_path', $path);

        return true;
    }

    public function isDue(): bool
    {
        $last = Setting::get(self::SETTING_KEY);

        if (! $last) {
            return true;
        }

        return Carbon::parse($last)->diffInHours(now()) >= self::INTERVAL_HOURS;
    }

    public function runIfDue(): void
    {
        if ($this->isDue()) {
            $this->runNow();
        }
    }

    public function runNow(): string
    {
        $directory = $this->backupsDirectory();

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = 'backup-'.now()->format('Y-m-d_H-i-s').'.sqlite';
        $destination = $directory.DIRECTORY_SEPARATOR.$filename;

        // A plain file copy of a WAL-mode database can miss recently committed
        // transactions still sitting in the -wal file. VACUUM INTO asks SQLite
        // itself for a complete, consistent snapshot in one atomic step, so the
        // backup is never a half-written or stale copy.
        DB::statement('VACUUM INTO ?', [$destination]);

        if ($externalPath = $this->externalPath()) {
            try {
                if (File::isDirectory($externalPath) || File::makeDirectory($externalPath, 0755, true)) {
                    File::copy($destination, $externalPath.DIRECTORY_SEPARATOR.$filename);
                }
            } catch (\Throwable) {
                // The local backup above already succeeded; losing the external
                // drive (unplugged, full, etc.) should not fail the whole backup.
            }
        }

        Setting::set(self::SETTING_KEY, now()->toDateTimeString());

        $this->prune($directory);

        if ($externalPath && File::isDirectory($externalPath)) {
            $this->prune($externalPath);
        }

        return $destination;
    }

    public function lastBackupAt(): ?string
    {
        return Setting::get(self::SETTING_KEY);
    }

    private function prune(string $directory): void
    {
        $files = collect(File::files($directory))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), 'backup-'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $files->slice(self::KEEP_LAST)->each(fn ($file) => File::delete($file->getPathname()));
    }
}
