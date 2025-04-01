<?php

declare(strict_types=1);

namespace WebMavens\Triki\Http\Controllers;

use WebMavens\Triki\Jobs\GenerateObfuscatedDumpJob;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Exception;

class TrikiController extends Controller
{
    public function index(): View
    {
        $database = env('DB_DATABASE');
        $connection = env('DB_CONNECTION');

        if ($connection === 'pgsql') {
            $tables = DB::select("SELECT tablename AS TABLE_NAME FROM pg_tables WHERE schemaname = 'public'");
        } else {
            $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", [$database]);
        }

        $dumpFiles = [];
        $dumpDir = storage_path('app/private/obfuscated');
    
        if (is_dir($dumpDir)) {
            $dumpFiles = array_filter(scandir($dumpDir), function ($file) use ($dumpDir) {
                return is_file($dumpDir . DIRECTORY_SEPARATOR . $file) && str_ends_with($file, '.sql');
            });
        }
    
        return view('triki::triki', compact('tables', 'dumpFiles'));
    }

    public function downloadStoredDump($filename): BinaryFileResponse
    {
        $path = storage_path('app/private/obfuscated/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path);
    }

    public function startDumpJob(Request $request): JsonResponse
    {
        $request->validate([
            'tables' => 'required|array|min:1',
            'email'  => 'required|email'
        ]);

        $keepTables = $request->input('tables', []);
        $email = $request->input('email');

        GenerateObfuscatedDumpJob::dispatch($keepTables, $email);

        return response()->json(['message' => 'Dump job dispatched']);
    }

    public function deleteDump(Request $request): RedirectResponse
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filePath = storage_path('app/private/obfuscated/' . $request->filename);

        if (file_exists($filePath)) {
            try {
                unlink($filePath);
                return back()->with('success', 'File deleted successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to delete the file.');
            }
        }

        return back()->with('error', 'File not found.');
    }
}
