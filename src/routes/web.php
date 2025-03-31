<?php

use WebMavens\Triki\Http\Controllers\TrikiController;

Route::middleware(['web', 'triki.auth'])->group(function () {
    Route::get('triki/download', [TrikiController::class, 'index'])->name('index');
    Route::post('/triki/start-dump', [TrikiController::class, 'startDumpJob'])->name('triki.startDumpJob');
    Route::get('/triki/download-dump/{filename}', [TrikiController::class, 'downloadStoredDump'])->name('triki.download.stored');
    Route::get('/triki/delete-dump', [TrikiController::class, 'deleteDump'])->name('triki.delete.dump');
});
