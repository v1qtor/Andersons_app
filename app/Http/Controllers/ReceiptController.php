<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function show($path): StreamedResponse
    {
        $fullPath = 'receipts/'.$path;

        $receipt = Receipt::where('file_path', $fullPath)->firstOrFail();
        Gate::authorize('view', $receipt);

        // Check if file exists in public storage
        if (! Storage::disk('public')->exists($fullPath)) {
            abort(404, 'Receipt file not found');
        }

        // Get the file
        $file = Storage::disk('public')->get($fullPath);
        $mimeType = Storage::disk('public')->mimeType($fullPath);

        return response()->stream(
            function () use ($file) {
                echo $file;
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.basename($path).'"',
                'Cache-Control' => 'public, max-age=3600',
            ]
        );
    }
}
