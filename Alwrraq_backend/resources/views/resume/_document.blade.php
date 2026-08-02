@if($draft->language === 'bilingual')
    @include('resume._document_bilingual')
@else
@php
    $content = $draft->content ?? [];
    $personal = $content['personal'] ?? [];
    $order = $draft->section_order ?? \App\Models\ResumeDraft::DEFAULT_SECTION_ORDER;
    $hidden = $draft->hidden_sections ?? [];
    $isArabic = $draft->language === 'ar';
    $sectionNames = $isArabic ? [
        'education' => 'التعليم والمؤهلات', 'experience' => 'الخبرات العملية',
        'skills' => 'المهارات', 'languages' => 'اللغات',
        'certificates' => 'الدورات والشهادات', 'projects' => 'المشاريع',
        'achievements' => 'الإنجازات', 'volunteering' => 'العمل التطوعي',
        'references' => 'المراجع',
    ] : [
        'education' => 'Education', 'experience' => 'Professional Experience',
        'skills' => 'Skills', 'languages' => 'Languages',
        'certificates' => 'Courses & Certificates', 'projects' => 'Projects',
        'achievements' => 'Achievements', 'volunteering' => 'Volunteer Work',
        'references' => 'References',
    ];
    $labels = $isArabic ? [
        'phone' => 'رقم الجوال', 'email' => 'البريد الإلكتروني', 'address' => 'العنوان',
        'birth_date' => 'تاريخ الميلاد', 'nationality' => 'الجنسية',
        'marital_status' => 'الحالة الاجتماعية', 'linkedin' => 'LinkedIn',
        'website' => 'الموقع الشخصي', 'summary' => 'الهدف الوظيفي',
        'major' => 'التخصص', 'grade' => 'المعدل', 'location' => 'الموقع',
        'date' => 'التاريخ', 'present' => 'حتى الآن',
    ] : [
        'phone' => 'Phone', 'email' => 'Email', 'address' => 'Address',
        'birth_date' => 'Birth date', 'nationality' => 'Nationality',
        'marital_status' => 'Marital status', 'linkedin' => 'LinkedIn',
        'website' => 'Website', 'summary' => 'Career Objective',
        'major' => 'Major', 'grade' => 'Grade', 'location' => 'Location',
        'date' => 'Date', 'present' => 'Present',
    ];
    $address = implode($isArabic ? '، ' : ', ', array_filter([
        $personal['city'] ?? null,
        $personal['country'] ?? null,
    ]));
    $personalRows = array_values(array_filter([
        ['icon' => '☎', 'label' => $labels['phone'], 'value' => $personal['phone'] ?? null],
        ['icon' => '✉', 'label' => $labels['email'], 'value' => $personal['email'] ?? null],
        ['icon' => '⌂', 'label' => $labels['address'], 'value' => $address ?: null],
        ['icon' => '◫', 'label' => $labels['birth_date'], 'value' => $personal['birth_date'] ?? null],
        ['icon' => '◆', 'label' => $labels['nationality'], 'value' => $personal['nationality'] ?? null],
        ['icon' => '●', 'label' => $labels['marital_status'], 'value' => $personal['marital_status'] ?? null],
        ['icon' => 'in', 'label' => $labels['linkedin'], 'value' => $personal['linkedin'] ?? null],
        ['icon' => '↗', 'label' => $labels['website'], 'value' => $personal['website'] ?? null],
    ], fn (array $row) => filled($row['value'])));
    $sideSections = ['skills', 'languages', 'references'];
    $visibleItemCount = 0;
    foreach ($order as $section) {
        if (in_array($section, $hidden, true)) {
            continue;
        }
        $sectionData = $content[$section] ?? [];
        $visibleItemCount += $section === 'references'
            ? count($sectionData['items'] ?? [])
            : count(is_array($sectionData) ? $sectionData : []);
    }
    $textLength = mb_strlen(json_encode($content, JSON_UNESCAPED_UNICODE) ?: '');
    $fullName = trim((string) ($personal['full_name'] ?? ($isArabic ? 'الاسم الكامل' : 'Full name')));
    $compactNameLength = mb_strlen(preg_replace('/\s+/u', '', $fullName) ?? $fullName);
    $nameSizeClass = $compactNameLength > 22
        ? 'cv-name-extra-long'
        : ($compactNameLength > 13 ? 'cv-name-long' : '');
    $density = $visibleItemCount <= 5 && $textLength < 1500
        ? 'sparse'
        : ($visibleItemCount >= 15 || $textLength > 5000 ? 'dense' : 'balanced');
    $photoSource = null;
    if ($draft->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($draft->photo_path)) {
        $absolutePhoto = \Illuminate\Support\Facades\Storage::disk('local')->path($draft->photo_path);
        $mime = \Illuminate\Support\Facades\File::mimeType($absolutePhoto) ?: 'image/png';
        $photoSource = $pdfMode ?? false
            ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($absolutePhoto))
            : route('resume.preview', [$draft, 'photo' => 1]);
    }
@endphp
<div class="cv-sheet template-{{ $draft->template_id }} content-{{ $density }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
    <div class="cv-layout">
        <aside class="cv-side">
            <div class="cv-profile">
                @if($photoSource)
                    <img class="cv-photo" src="{{ $photoSource }}" alt="">
                @else
                    <div class="cv-photo cv-photo-placeholder">{{ mb_substr($personal['full_name'] ?? 'CV', 0, 1) }}</div>
                @endif
            </div>

            <section class="cv-section cv-personal-section">
                <div class="cv-personal-identity">
                    <h1 class="{{ $nameSizeClass }}">{{ $fullName }}</h1>
                    <div class="cv-job">{{ $personal['job_title'] ?? ($isArabic ? 'المسمى الوظيفي' : 'Job title') }}</div>
                </div>
                <h3>{{ $isArabic ? 'المعلومات الشخصية' : 'Personal Information' }}</h3>
                @if($personalRows)
                    <div class="cv-personal-list">
                        @foreach($personalRows as $row)
                            <div class="cv-personal-row {{ $row['label'] === $labels['email'] ? 'cv-personal-row-email' : '' }}">
                                <span class="cv-personal-icon">{{ $row['icon'] }}</span>
                                <div><strong>{{ $row['label'] }}</strong><span>{{ $row['value'] }}</span></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            @foreach($order as $section)
                @continue(in_array($section, $hidden, true) || !in_array($section, $sideSections, true))
                @php
                    $sectionData = $content[$section] ?? [];
                    $items = $section === 'references' ? ($sectionData['items'] ?? []) : $sectionData;
                    $available = $section === 'references' && ($sectionData['available_on_request'] ?? false);
                @endphp
                @if($available || count($items))
                    <section class="cv-section cv-section-{{ $section }}">
                        <h3>{{ $sectionNames[$section] }}</h3>
                        @if($available)
                            <div class="cv-body">{{ $isArabic ? 'المراجع متاحة عند الطلب' : 'References available upon request' }}</div>
                        @endif
                        @foreach($items as $item)
                            <div class="cv-item">
                                <div class="cv-item-heading">
                                    <strong>{{ $item['name'] ?? '' }}</strong>
                                    @if($item['level'] ?? null)<span class="cv-level">{{ $item['level'] }}</span>@endif
                                </div>
                                @if($section === 'references')
                                    <small>{{ implode(' • ', array_filter([$item['job_title'] ?? null, $item['company'] ?? null])) }}</small>
                                    @if($item['phone'] ?? null)<div class="cv-body">{{ $item['phone'] }}</div>@endif
                                    @if($item['email'] ?? null)<div class="cv-body">{{ $item['email'] }}</div>@endif
                                @endif
                            </div>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </aside>

        <main class="cv-main">
            @if($personal['summary'] ?? null)
                <section class="cv-section cv-summary">
                    <h3>{{ $labels['summary'] }}</h3>
                    <div class="cv-body">{{ $personal['summary'] }}</div>
                </section>
            @endif

            @foreach($order as $section)
                @continue(in_array($section, $hidden, true) || in_array($section, $sideSections, true))
                @php
                    $items = $content[$section] ?? [];
                @endphp
                @if(count($items))
                    <section class="cv-section cv-section-{{ $section }}">
                        <h3><span class="cv-section-mark"></span>{{ $sectionNames[$section] }}</h3>
                        <div class="cv-section-items">
                            @foreach($items as $item)
                                @php
                                    $title = $item['qualification'] ?? $item['job_title'] ?? $item['name'] ?? $item['title'] ?? $item['role'] ?? '';
                                    $organization = $item['institution'] ?? $item['company'] ?? $item['issuer'] ?? $item['organization'] ?? '';
                                    $endDate = ($item['current'] ?? false) ? $labels['present'] : ($item['end_date'] ?? $item['date'] ?? null);
                                    $date = implode(' — ', array_filter([$item['start_date'] ?? null, $endDate]));
                                    $description = $item['description'] ?? $item['achievements'] ?? '';
                                @endphp
                                <article class="cv-item">
                                    <div class="cv-item-heading">
                                        <strong>{{ $title }}</strong>
                                        @if($date)<time>{{ $date }}</time>@endif
                                    </div>
                                    @if($organization)<small class="cv-organization">{{ $organization }}</small>@endif
                                    <div class="cv-item-meta">
                                        @if($item['major'] ?? null)<span>{{ $labels['major'] }}: {{ $item['major'] }}</span>@endif
                                        @if($item['location'] ?? null)<span>{{ $labels['location'] }}: {{ $item['location'] }}</span>@endif
                                        @if($item['grade'] ?? null)<span>{{ $labels['grade'] }}: {{ $item['grade'] }}</span>@endif
                                        @if($item['technologies'] ?? null)<span>{{ $item['technologies'] }}</span>@endif
                                    </div>
                                    @if($description)<div class="cv-body">{{ $description }}</div>@endif
                                    @if($item['url'] ?? null)<div class="cv-link">{{ $item['url'] }}</div>@endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </main>
    </div>
    @unless($paid)
        <div class="cv-watermark" aria-hidden="true">@for($i=0;$i<28;$i++)<span>معاينة غير مدفوعة — الورّاق</span>@endfor</div>
    @endunless
</div>
@endif
