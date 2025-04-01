<?php

declare(strict_types=1);

namespace WebMavens\Triki\Jobs;

use WebMavens\Triki\Notifications\DumpReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Exception;

class GenerateObfuscatedDumpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $keepTables,
        protected string $email
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting obfuscated dump download...');
        try {
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbName = env('DB_DATABASE');
            $date = now()->format('Y-m-d_H:i:s');
            $filename = "obfuscated_dump_{$date}.sql";
            $dumpPath = storage_path("app/private/obfuscated/{$filename}");
            $packagePath = realpath(__DIR__ . '/../../');
            $obfuscatorPath = escapeshellarg(base_path('obfuscator.cr'));
            Storage::makeDirectory('obfuscated');
            $ignored = '';

            $tablesToKeep = $this->keepTables;
            $tablesString = implode(' ', $tablesToKeep);

            $command = "cd {$packagePath} && mysqldump --single-transaction --quick --no-autocommit --add-drop-table --hex-blob -u {$dbUser} -p{$dbPass} {$dbName} --tables {$tablesString} | crystal run {$obfuscatorPath} 2>&1 | grep -v 'WARN - triki' > {$dumpPath}";

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('Obfuscated dump generation failed.', [
                    'command'     => $command,
                    'output'      => $output,
                    'return_code' => $returnVar,
                ]);
                throw new Exception('Failed to generate obfuscated dump. Check logs for details.');
            }

            Log::info('Obfuscated dump generated successfully.', ['file' => $dumpPath]);
            Notification::route('mail', $this->email)->notify(new DumpReadyNotification());
        } catch (Exception $e) {
            Log::error('Error in GenerateObfuscatedDumpJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
