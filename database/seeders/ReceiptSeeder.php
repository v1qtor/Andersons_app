<?php

namespace Database\Seeders;

use App\Models\Receipt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('receipts');

        if (!Storage::disk('public')->exists('receipts/sample.pdf')) {
            $pdf = "%PDF-1.4\n"
                . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
                . "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
                . "3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n"
                . "4 0 obj<</Length 44>>stream\nBT /F1 12 Tf 100 700 Td (Sample Receipt) Tj ET\nendstream\nendobj\n"
                . "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n"
                . "xref\n0 6\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n"
                . "0000000115 00000 n\n0000000274 00000 n\n0000000369 00000 n\n"
                . "trailer<</Size 6/Root 1 0 R>>\nstartxref\n441\n%%EOF";

            Storage::disk('public')->put('receipts/sample.pdf', $pdf);
        }

        Receipt::factory(10)->create();
    }
}
