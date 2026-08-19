<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubservienceDocSeeder extends Seeder
{
    public function run(): void
    {
        $subserviences = DB::table('sub_services')->select('id')->get();

        if ($subserviences->isEmpty()) {
            return;
        }

        $disk = 's3';
        $baseDir = 'sub_service/docs';
        $now = now();

        $rows = [];

        // 2 docs لكل sub_service
        foreach ($subserviences as $s) {
            for ($i = 1; $i <= 2; $i++) {
                $title = "Doc {$i} for Sub_service {$s->id}";
                $fileName = Str::uuid()->toString() . '.pdf';
                $path = "{$baseDir}/{$s->id}/{$fileName}";

                // Minimal PDF bytes (valid enough for download/view)
                $pdfContent = $this->minimalPdf("{$title}");

                Storage::disk($disk)->put($path, $pdfContent);

                $rows[] = [
                    'sub_service_id' => $s->id,
                    'title' => $title,
                    'file_path' => $path,
                    'file_original_name' => "{$title}.pdf",
                    'file_size' => strlen($pdfContent),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('subservience_docs')->insert($rows);
    }

    private function minimalPdf(string $text): string
    {
        // Very small PDF (simple text). Good for seeding/testing.
        $text = str_replace(['(', ')', "\r", "\n"], ['\[', '\]', ' ', ' '], $text);

        return "%PDF-1.4\n"
            . "1 0 obj<<>>endobj\n"
            . "2 0 obj<< /Length 44 >>stream\n"
            . "BT /F1 12 Tf 50 750 Td ({$text}) Tj ET\n"
            . "endstream endobj\n"
            . "3 0 obj<< /Type /Page /Parent 4 0 R /Contents 2 0 R >>endobj\n"
            . "4 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n"
            . "5 0 obj<< /Type /Catalog /Pages 4 0 R >>endobj\n"
            . "xref\n0 6\n0000000000 65535 f \n"
            . "trailer<< /Size 6 /Root 5 0 R >>\nstartxref\n0\n%%EOF\n";
    }
}
