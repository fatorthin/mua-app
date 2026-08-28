<?php

namespace App\Livewire\Pricelists;

use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\PricelistSection;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class PricelistBuilder extends Component
{
    public ?int $pricelistId = null;

    // Builder Navigation & Preview Mode
    public string $activeTab = 'general'; // general, content, terms, footer
    public string $previewMode = 'desktop'; // desktop, mobile

    // General Settings
    public string $title = 'Pricelist & Paket Layanan';
    public string $slug = '';
    public string $description = 'Pilihan paket rias profesional untuk momen istimewa Anda.';
    public string $theme_template = 'rose_blush'; // rose_blush, luxury_gold, clean_nude, sage_botanical
    public string $primary_color = '#ec4899';
    public bool $is_public = true;
    public bool $show_logo = true;
    public bool $show_social_media = true;
    public bool $show_contact_button = true;

    // Content: Sections & Items
    public array $sections = [];

    // Terms & Conditions
    public array $terms_conditions = [];

    // Footer
    public string $custom_footer_notes = 'Pemesanan tanggal berlaku setelah konfirmasi DP. Terima kasih telah mempercayakan momen spesial Anda bersama kami.';

    // State for temporary inputs
    public string $newTermInput = '';

    public function mount(?Pricelist $pricelist = null): void
    {
        if ($pricelist && $pricelist->exists) {
            abort_unless($pricelist->user_id === auth()->id(), 403);

            $this->pricelistId          = $pricelist->id;
            $this->title                = $pricelist->title;
            $this->slug                 = $pricelist->slug;
            $this->description          = $pricelist->description ?? '';
            $this->theme_template       = $pricelist->theme_template ?? 'rose_blush';
            $this->primary_color        = $pricelist->primary_color ?? '#ec4899';
            $this->is_public            = (bool) $pricelist->is_public;
            $this->show_logo            = (bool) $pricelist->show_logo;
            $this->show_social_media    = (bool) $pricelist->show_social_media;
            $this->show_contact_button  = (bool) $pricelist->show_contact_button;
            $this->custom_footer_notes  = $pricelist->custom_footer_notes ?? '';
            $this->terms_conditions     = $pricelist->terms_conditions ?? [];

            // Load sections & items
            $loadedSections = [];
            foreach ($pricelist->sections()->with('items')->orderBy('order')->get() as $sec) {
                $items = [];
                foreach ($sec->items()->orderBy('order')->get() as $it) {
                    $items[] = [
                        'id'             => $it->id,
                        'service_id'     => $it->service_id,
                        'name'           => $it->name,
                        'price'          => (float) $it->price,
                        'duration_text'  => $it->duration_text ?? '',
                        'description'    => $it->description ?? '',
                        'features'       => is_array($it->features) ? $it->features : [],
                        'is_highlighted' => (bool) $it->is_highlighted,
                    ];
                }

                $loadedSections[] = [
                    'id'          => $sec->id,
                    'name'        => $sec->name,
                    'description' => $sec->description ?? '',
                    'items'       => $items,
                ];
            }

            $this->sections = $loadedSections;
        } else {
            // Default Initial Scaffolding for new pricelist
            $this->title = 'Pricelist ' . (auth()->user()->studio_name ?: 'Makeup Artist') . ' ' . date('Y');
            $this->slug  = Str::slug($this->title) . '-' . Str::lower(Str::random(5));
            $this->resetDefaultTerms();

            $this->sections = [
                [
                    'id'          => null,
                    'name'        => 'Wedding & Bridal Packages',
                    'description' => 'Paket tata rias eksklusif untuk hari pernikahan.',
                    'items'       => [
                        [
                            'id'             => null,
                            'service_id'     => null,
                            'name'           => 'Akad & Resepsi (Same Day)',
                            'price'          => 3500000,
                            'duration_text'  => '3-4 Jam',
                            'description'    => 'Rias lengkap pengantin wanita untuk akad nikah dan pesta resepsi di hari yang sama.',
                            'features'       => [
                                'Makeup pengantin premium (Longlasting 12 jam)',
                                'Free 1x Retouch sebelum resepsi',
                                'Hijabdo / Hairdo styling pengantin',
                                'Free Softlens Normal/Minus (S&K)',
                                'Free 1 Pasang Fake Eyelashes Premium',
                            ],
                            'is_highlighted' => true,
                        ],
                        [
                            'id'             => null,
                            'service_id'     => null,
                            'name'           => 'Akad Nikah Only',
                            'price'          => 2000000,
                            'duration_text'  => '2-3 Jam',
                            'description'    => 'Rias anggun & flawless khusus prosesi akad nikah.',
                            'features'       => [
                                'Makeup flawless & fresh',
                                'Hijabdo / Hairdo styling',
                                'Free Softlens & Bulu Mata',
                            ],
                            'is_highlighted' => false,
                        ],
                    ],
                ],
                [
                    'id'          => null,
                    'name'        => 'Engagement & Graduation',
                    'description' => 'Rias natural glam untuk lamaran, wisuda, atau photoshoot.',
                    'items'       => [
                        [
                            'id'             => null,
                            'service_id'     => null,
                            'name'           => 'Engagement Makeup',
                            'price'          => 1200000,
                            'duration_text'  => '2 Jam',
                            'description'    => 'Look natural romantic untuk acara pertunangan.',
                            'features'       => [
                                'Makeup look romantic flawless',
                                'Hijabdo / Hairdo styling',
                                'Free Bulu Mata Premium',
                            ],
                            'is_highlighted' => false,
                        ],
                        [
                            'id'             => null,
                            'service_id'     => null,
                            'name'           => 'Graduation / Wisuda Look',
                            'price'          => 600000,
                            'duration_text'  => '1.5 Jam',
                            'description'    => 'Makeup fresh & tahan lama seharian di hari kelulusan.',
                            'features'       => [
                                'Makeup natural fresh & tahan keringat',
                                'Hijabdo / Hairdo wisuda',
                            ],
                            'is_highlighted' => false,
                        ],
                    ],
                ],
            ];
        }
    }

    public function updatedThemeTemplate($value): void
    {
        // Set default primary color according to selected theme
        $this->primary_color = match ($value) {
            'luxury_gold'    => '#d97706',
            'clean_nude'      => '#b45309',
            'sage_botanical' => '#059669',
            default          => '#ec4899',
        };
    }

    // Section actions
    public function addSection(): void
    {
        $this->sections[] = [
            'id'          => null,
            'name'        => 'Kategori Baru',
            'description' => '',
            'items'       => [],
        ];
    }

    public function removeSection(int $secIndex): void
    {
        if (isset($this->sections[$secIndex])) {
            array_splice($this->sections, $secIndex, 1);
        }
    }

    public function moveSectionUp(int $secIndex): void
    {
        if ($secIndex > 0 && isset($this->sections[$secIndex])) {
            $temp = $this->sections[$secIndex - 1];
            $this->sections[$secIndex - 1] = $this->sections[$secIndex];
            $this->sections[$secIndex] = $temp;
        }
    }

    public function moveSectionDown(int $secIndex): void
    {
        if ($secIndex < count($this->sections) - 1 && isset($this->sections[$secIndex])) {
            $temp = $this->sections[$secIndex + 1];
            $this->sections[$secIndex + 1] = $this->sections[$secIndex];
            $this->sections[$secIndex] = $temp;
        }
    }

    // Item actions
    public function addItem(int $secIndex): void
    {
        if (!isset($this->sections[$secIndex])) return;

        $this->sections[$secIndex]['items'][] = [
            'id'             => null,
            'service_id'     => null,
            'name'           => 'Paket Layanan Baru',
            'price'          => 500000,
            'duration_text'  => '2 Jam',
            'description'    => '',
            'features'       => ['Termasuk Hijabdo/Hairdo'],
            'is_highlighted' => false,
        ];
    }

    public function removeItem(int $secIndex, int $itemIndex): void
    {
        if (isset($this->sections[$secIndex]['items'][$itemIndex])) {
            array_splice($this->sections[$secIndex]['items'], $itemIndex, 1);
        }
    }

    public function importServiceToItem(int $secIndex, int $serviceId): void
    {
        $service = Service::where('user_id', auth()->id())->find($serviceId);
        if (!$service || !isset($this->sections[$secIndex])) return;

        $this->sections[$secIndex]['items'][] = [
            'id'             => null,
            'service_id'     => $service->id,
            'name'           => $service->name,
            'price'          => (float) $service->price,
            'duration_text'  => $service->duration ? ($service->duration . ' Menit') : '',
            'description'    => $service->description ?? '',
            'features'       => [
                'Layanan terdaftar di katalog',
            ],
            'is_highlighted' => false,
        ];
    }

    // Feature / benefit checklist actions
    public function addFeature(int $secIndex, int $itemIndex): void
    {
        if (isset($this->sections[$secIndex]['items'][$itemIndex])) {
            if (!isset($this->sections[$secIndex]['items'][$itemIndex]['features']) || !is_array($this->sections[$secIndex]['items'][$itemIndex]['features'])) {
                $this->sections[$secIndex]['items'][$itemIndex]['features'] = [];
            }
            $this->sections[$secIndex]['items'][$itemIndex]['features'][] = 'Keunggulan / Fasilitas baru';
        }
    }

    public function removeFeature(int $secIndex, int $itemIndex, int $featIndex): void
    {
        if (isset($this->sections[$secIndex]['items'][$itemIndex]['features'][$featIndex])) {
            array_splice($this->sections[$secIndex]['items'][$itemIndex]['features'], $featIndex, 1);
        }
    }

    // Terms & Conditions actions
    public function addTerm(): void
    {
        $trimmed = trim($this->newTermInput);
        if ($trimmed !== '') {
            $this->terms_conditions[] = $trimmed;
            $this->newTermInput = '';
        }
    }

    public function removeTerm(int $termIndex): void
    {
        if (isset($this->terms_conditions[$termIndex])) {
            array_splice($this->terms_conditions, $termIndex, 1);
        }
    }

    public function resetDefaultTerms(): void
    {
        $this->terms_conditions = [
            'DP minimal 50% untuk penguncian tanggal (Non-refundable jika terjadi pembatalan sepihak).',
            'Pelunasan dilakukan paling lambat H-1 atau maksimal setelah sesi rias selesai.',
            'Biaya transportasi & akomodasi luar area studio ditanggung oleh pihak klien.',
            'Klien diharapkan siap tepat waktu sesuai jadwal yang telah disepakati bersama.',
            'Standby subuh sebelum jam 05:00 WIB dikenakan surcharge fee tambahan.',
        ];
    }

    public function save()
    {
        $this->validate([
            'title'          => 'required|string|max:150',
            'theme_template' => 'required|string|in:rose_blush,luxury_gold,clean_nude,sage_botanical',
            'primary_color'  => 'required|string|max:20',
            'sections'       => 'required|array|min:1',
            'sections.*.name'=> 'required|string|max:100',
        ], [
            'title.required'          => 'Judul pricelist wajib diisi.',
            'sections.min'            => 'Minimal buat 1 kategori layanan.',
            'sections.*.name.required'=> 'Nama kategori tidak boleh kosong.',
        ]);

        DB::transaction(function () {
            $data = [
                'user_id'             => auth()->id(),
                'title'               => $this->title,
                'slug'                => $this->slug ?: (Str::slug($this->title) . '-' . Str::lower(Str::random(5))),
                'description'         => $this->description,
                'theme_template'      => $this->theme_template,
                'primary_color'       => $this->primary_color,
                'is_public'           => $this->is_public,
                'show_logo'           => $this->show_logo,
                'show_social_media'   => $this->show_social_media,
                'show_contact_button' => $this->show_contact_button,
                'custom_footer_notes' => $this->custom_footer_notes,
                'terms_conditions'    => array_values(array_filter($this->terms_conditions)),
            ];

            if ($this->pricelistId) {
                $pricelist = Pricelist::where('user_id', auth()->id())->findOrFail($this->pricelistId);
                $pricelist->update($data);
            } else {
                $pricelist = Pricelist::create($data);
                $this->pricelistId = $pricelist->id;
            }

            // Sync Sections & Items
            // Get existing section IDs to delete removed ones
            $existingSectionIds = $pricelist->sections()->pluck('id')->toArray();
            $keptSectionIds = [];

            foreach ($this->sections as $secOrder => $secData) {
                $section = null;
                if (!empty($secData['id'])) {
                    $section = PricelistSection::where('pricelist_id', $pricelist->id)->find($secData['id']);
                }

                if (!$section) {
                    $section = new PricelistSection();
                    $section->pricelist_id = $pricelist->id;
                }

                $section->name        = $secData['name'];
                $section->description = $secData['description'] ?? null;
                $section->order       = $secOrder;
                $section->save();

                $keptSectionIds[] = $section->id;

                // Sync Items for this Section
                $existingItemIds = $section->items()->pluck('id')->toArray();
                $keptItemIds = [];

                if (isset($secData['items']) && is_array($secData['items'])) {
                    foreach ($secData['items'] as $itemOrder => $itemData) {
                        $item = null;
                        if (!empty($itemData['id'])) {
                            $item = PricelistItem::where('pricelist_section_id', $section->id)->find($itemData['id']);
                        }

                        if (!$item) {
                            $item = new PricelistItem();
                            $item->pricelist_section_id = $section->id;
                        }

                        $item->service_id     = !empty($itemData['service_id']) ? $itemData['service_id'] : null;
                        $item->name           = $itemData['name'];
                        $item->price          = (float) ($itemData['price'] ?? 0);
                        $item->duration_text  = $itemData['duration_text'] ?? null;
                        $item->description    = $itemData['description'] ?? null;
                        $item->features       = isset($itemData['features']) && is_array($itemData['features']) 
                                                    ? array_values(array_filter($itemData['features'])) 
                                                    : [];
                        $item->is_highlighted = (bool) ($itemData['is_highlighted'] ?? false);
                        $item->order          = $itemOrder;
                        $item->save();

                        $keptItemIds[] = $item->id;
                    }
                }

                // Delete removed items
                $itemsToDelete = array_diff($existingItemIds, $keptItemIds);
                if (!empty($itemsToDelete)) {
                    PricelistItem::whereIn('id', $itemsToDelete)->delete();
                }
            }

            // Delete removed sections
            $sectionsToDelete = array_diff($existingSectionIds, $keptSectionIds);
            if (!empty($sectionsToDelete)) {
                PricelistSection::whereIn('id', $sectionsToDelete)->delete();
            }
        });

        session()->flash('success', 'Dokumen pricelist berhasil disimpan!');
        return redirect()->route('pricelists.index');
    }

    public function getAvailableServicesProperty()
    {
        return Service::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.pricelists.pricelist-builder', [
            'availableServices' => $this->availableServices,
            'user' => auth()->user(),
        ]);
    }
}
