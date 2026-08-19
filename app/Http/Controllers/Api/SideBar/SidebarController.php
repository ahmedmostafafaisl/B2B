<?php

namespace App\Http\Controllers\Api\SideBar;

use App\Http\Controllers\Controller;
use App\Http\Requests\SidBar\ActiveItemsRequest;
use App\Http\Resources\SideBar\MenuModuleResource;
use App\Models\Part;
use App\Models\Service;
use App\Repositories\Interfaces\SidebarRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SidebarController extends Controller
{
    public function __construct(private SidebarRepositoryInterface $sidebarRepo) {}

    public function sidebar(Request $request)
    {
        $result = $this->sidebarRepo->sidebar($request);

        return response()->json([
            'items' => MenuModuleResource::collection($result['items']),
            'pagination' => null,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 's3-debug-'.Str::uuid().'.'.$ext;
        $path = 'debug/'.$name;
        $disk = Storage::disk('s3');

        // ── 1. Raw file info ──────────────────────────────────────────
        $fileInfo = [
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $ext,
            'target_path' => $path,
        ];

        // ── 2. S3 config snapshot (safe fields only) ──────────────────
        $s3Config = [
            'driver' => config('filesystems.disks.s3.driver'),
            'bucket' => config('filesystems.disks.s3.bucket'),
            'region' => config('filesystems.disks.s3.region'),
            'endpoint' => config('filesystems.disks.s3.endpoint'),
            'url' => config('filesystems.disks.s3.url'),
            'key_set' => ! empty(config('filesystems.disks.s3.key')),
            'secret_set' => ! empty(config('filesystems.disks.s3.secret')),
        ];

        // ── 3. Attempt upload ─────────────────────────────────────────
        $stored = null;
        $url = null;
        $exists = false;
        $error = null;

        try {
            $stored = $file->storeAs('debug', $name, 's3');

            if ($stored) {
                $exists = $disk->exists($stored);
                $url = $disk->url($stored);
            }
        } catch (\Throwable $e) {
            $error = [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        // ── 4. Build full report ──────────────────────────────────────
        $report = [
            'file_info' => $fileInfo,
            's3_config' => $s3Config,
            'stored_path' => $stored,
            'exists_on_s3' => $exists,
            'url' => $url,
            'error' => $error,
            'success' => $stored && $exists,
        ];

        // ── 5. Log everything ─────────────────────────────────────────
        Log::channel('single')->info('[S3 Debug] Upload test', $report);

        return response()->json($report);
    }

    // active items for select options
    private array $modelMap = [
        'Service' => Service::class,
        'Part' => Part::class,
    ];

    private array $defaultRelations = [
        'Service' => [
            'images',
            'primaryImage',
            'serviceTypes',
            'faqs',
        ],
        'Part' => [
            'subParts',
            'subParts.children',
            'faqs',
            'banners',
        ],
    ];

    public function index(ActiveItemsRequest $request): JsonResponse
    {

        $models = collect($request->validated()['models'])
            ->map(fn ($entry) => [
                'model' => $this->modelMap[$entry['model']],
                'key' => $entry['model'],
                'relations' => $entry['relations'] ?? $this->defaultRelations[$entry['model']] ?? [],
                'filters' => $entry['filters'] ?? [],
                'columns' => $entry['columns'] ?? ['*'],
            ])
            ->toArray();
        // dd($models);
        // Example of fetching modules with active children
        $result = $this->sidebarRepo->getManyActive($models);

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);
    }
}
