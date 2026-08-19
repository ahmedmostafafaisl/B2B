<?php

namespace App\Console\Commands;

use App\Models\MenuItem;
use App\Models\MenuModule;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSidebarServices extends Command
{
    protected $signature = 'menu:sync-services {--keep-custom=0 : Keep manual items that are not linkable}';
    protected $description = 'Sync sidebar menu: Services -> SubServices into menu_modules/menu_items';

    public function handle(): int
    {
        $keepCustom = (int) $this->option('keep-custom') === 1;

        DB::transaction(function () use ($keepCustom) {

            $module = MenuModule::firstOrCreate(
                ['slug' => 'services'],
                ['title' => 'Services', 'is_active' => true, 'sort_order' => 1]
            );

            // Delete existing generated items (linkable ones)
            $itemsQuery = MenuItem::query()->where('menu_module_id', $module->id);

            if ($keepCustom) {
                $itemsQuery->whereNotNull('linkable_type')->delete();
            } else {
                $itemsQuery->delete();
            }

            $services = Service::query()
                ->with(['subServices' => fn($q) => $q->orderBy('sort_order')->orderBy('id')])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($services as $service) {
                $serviceItem = MenuItem::create([
                    'menu_module_id' => $module->id,
                    'parent_id' => null,
                    'title' => $service->title,
                    'link' => null,
                    'linkable_type' => Service::class,
                    'linkable_id' => $service->id,
                    'is_active' => true,
                    'sort_order' => (int)($service->sort_order ?? 0),
                ]);

                foreach ($service->subServices as $sub) {
                    MenuItem::create([
                        'menu_module_id' => $module->id,
                        'parent_id' => $serviceItem->id,
                        'title' => $sub->title,
                        'link' => null,
                        'linkable_type' => SubService::class,
                        'linkable_id' => $sub->id,
                        'is_active' => true,
                        'sort_order' => (int)($sub->sort_order ?? 0),
                    ]);
                }
            }
        });

        $this->info('✅ Sidebar menu synced: Services -> SubServices');
        return self::SUCCESS;
    }
}
