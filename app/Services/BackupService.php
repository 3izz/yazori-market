<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupService
{
    private const SETTING_KEY = 'last_backup_at';

    private const INTERVAL_DAYS = 2;

    private const KEEP_LAST = 30;

    public function backupsDirectory(): string
    {
        return storage_path('app/backups');
    }

    public function isDue(): bool
    {
        $last = Setting::get(self::SETTING_KEY);

        if (! $last) {
            return true;
        }

        return Carbon::parse($last)->diffInDays(now()) >= self::INTERVAL_DAYS;
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

        $source = database_path('database.sqlite');
        $filename = 'backup-'.now()->format('Y-m-d_H-i-s').'.sqlite';
        $destination = $directory.DIRECTORY_SEPARATOR.$filename;

        File::copy($source, $destination);

        Setting::set(self::SETTING_KEY, now()->toDateTimeString());

        $this->prune();

        return $destination;
    }

    public function lastBackupAt(): ?string
    {
        return Setting::get(self::SETTING_KEY);
    }

    private function prune(): void
    {
        $files = collect(File::files($this->backupsDirectory()))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $files->slice(self::KEEP_LAST)->each(fn ($file) => File::delete($file->getPathname()));
    }
}
