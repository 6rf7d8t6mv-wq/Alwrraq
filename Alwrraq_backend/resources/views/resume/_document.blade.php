@php
    $content = $draft->content ?? [];
    $personal = $content['personal'] ?? [];
    $order = $draft->section_order ?? \App\Models\ResumeDraft::DEFAULT_SECTION_ORDER;
    $hidden = $draft->hidden_sections ?? [];
    $isArabic = $draft->language === 'ar';
    $sectionNames = $isArabic ? [
        'education' => 'المؤهلات العلمية', 'experience' => 'الخبرات العملية',
        'skills' => 'المهارات', 'languages' => 'اللغات',
        'certificates' => 'الدورات والشهادات', 'projects' => 'المشاريع',
        'achievements' => 'الإنجازات', 'volunteering' => 'العمل التطوعي',
        'references' => 'المراجع',
    ] : [
        'education' => 'Education', 'experience' => 'Experience',
        'skills' => 'Skills', 'languages' => 'Languages',
        'certificates' => 'Courses & Certificates', 'projects' => 'Projects',
        'achievements' => 'Achievements', 'volunteering' => 'Volunteer Work',
        'references' => 'References',
    ];
    $titleFor = fn (array $item) => $item['qualification'] ?? $item['job_title'] ?? $item['name'] ?? $item['title'] ?? $item['organization'] ?? $item['role'] ?? '';
    $subtitleFor = fn (array $item) => $item['institution'] ?? $item['company'] ?? $item['issuer'] ?? '';
    $descriptionFor = fn (array $item) => $item['description'] ?? $item['achievements'] ?? $item['technologies'] ?? '';
    $sideSections = ['skills', 'languages', 'references'];
    $photoSource = null;
    if ($draft->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($draft->photo_path)) {
        $absolutePhoto = \Illuminate\Support\Facades\Storage::disk('local')->path($draft->photo_path);
        $mime = \Illuminate\Support\Facades\File::mimeType($absolutePhoto) ?: 'image/png';
        $photoSource = $pdfMode ?? false
            ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($absolutePhoto))
            : route('resume.preview', [$draft, 'photo' => 1]);
    }
@endphp
<div class="cv-sheet template-{{ $draft->template_id }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
    <div class="cv-layout">
        <aside class="cv-side">
            @if($photoSource)<img class="cv-photo" src="{{ $photoSource }}" alt="">@endif
            @php
                $contacts = array_filter([
                    $personal['phone'] ?? null,
                    $personal['email'] ?? null,
                    $personal['city'] ?? null,
                    $personal['country'] ?? null,
                    $personal['linkedin'] ?? null,
                    $personal['website'] ?? null,
                ]);
            @endphp
            @if($contacts)
                <section class="cv-section"><h3>{{ $isArabic ? 'التواصل' : 'Contact' }}</h3>
                    @foreach($contacts as $contact)<div class="cv-contact">{{ $contact }}</div>@endforeach
                </section>
            @endif
            @foreach($order as $section)
                @continue(in_array($section, $hidden, true) || !in_array($section, $sideSections, true))
                @php
                    $sectionData = $content[$section] ?? [];
                    $items = $section === 'references' ? ($sectionData['items'] ?? []) : $sectionData;
                    $available = $section === 'references' && ($sectionData['available_on_request'] ?? false);
                @endphp
                @if($available || count($items))
                    <section class="cv-section"><h3>{{ $sectionNames[$section] }}</h3>
                        @if($available)<div class="cv-body">{{ $isArabic ? 'المراجع متاحة عند الطلب' : 'References available upon request' }}</div>@endif
                        @foreach($items as $item)
                            <div class="cv-item"><strong>{{ $titleFor($item) }}</strong>
                                @if($subtitleFor($item))<small>{{ $subtitleFor($item) }}</small>@endif
                                @if($item['level'] ?? null)<small>{{ $item['level'] }}</small>@endif
                                @if($descriptionFor($item))<div class="cv-body">{{ $descriptionFor($item) }}</div>@endif
                            </div>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </aside>
        <main class="cv-main">
            <h1>{{ $personal['full_name'] ?? ($isArabic ? 'الاسم الكامل' : 'Full name') }}</h1>
            <div class="cv-job">{{ $personal['job_title'] ?? ($isArabic ? 'المسمى الوظيفي' : 'Job title') }}</div>
            @if($personal['summary'] ?? null)
                <section class="cv-section"><h3>{{ $isArabic ? 'نبذة مهنية' : 'Profile' }}</h3><div class="cv-body">{{ $personal['summary'] }}</div></section>
            @endif
            @foreach($order as $section)
                @continue(in_array($section, $hidden, true) || in_array($section, $sideSections, true))
                @php($items = $content[$section] ?? [])
                @if(count($items))
                    <section class="cv-section"><h3>{{ $sectionNames[$section] }}</h3>
                        @foreach($items as $item)
                            @php($date = implode(' — ', array_filter([$item['start_date'] ?? null, ($item['current'] ?? false) ? ($isArabic ? 'حتى الآن' : 'Present') : ($item['end_date'] ?? $item['date'] ?? null)])))
                            <div class="cv-item"><strong>{{ $titleFor($item) }}</strong>
                                @if($subtitleFor($item))<small>{{ $subtitleFor($item) }}</small>@endif
                                @if($date)<small>{{ $date }}</small>@endif
                                @if($descriptionFor($item))<div class="cv-body">{{ $descriptionFor($item) }}</div>@endif
                            </div>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </main>
    </div>
    @unless($paid)
        <div class="cv-watermark" aria-hidden="true">@for($i=0;$i<24;$i++)<span>معاينة غير مدفوعة — الورّاق</span>@endfor</div>
    @endunless
</div>
