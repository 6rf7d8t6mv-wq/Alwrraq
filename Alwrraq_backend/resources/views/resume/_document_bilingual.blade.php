@php
    $contentOriginal = $draft->content ?? [];
    $contentAr = $contentOriginal['content_ar'] ?? $contentOriginal;
    $contentEn = $contentOriginal['content_en'] ?? [];
    $personalOriginal = $contentOriginal['personal'] ?? [];
    // The top identity follows the application's active UI language. The two
    // resume columns keep their own fixed Arabic/English directions below.
    $personalIsArabic = session('ui_locale', 'ar') !== 'en';
    $personalAddress = implode($personalIsArabic ? '، ' : ', ', array_filter([$personalOriginal['city'] ?? null, $personalOriginal['country'] ?? null]));
    $personalContacts = array_filter([
        $personalIsArabic ? 'رقم الجوال' : 'Phone' => $personalOriginal['phone'] ?? null,
        $personalIsArabic ? 'البريد الإلكتروني' : 'Email' => $personalOriginal['email'] ?? null,
        $personalIsArabic ? 'العنوان' : 'Address' => $personalAddress ?: null,
        $personalIsArabic ? 'تاريخ الميلاد' : 'Birth date' => $personalOriginal['birth_date'] ?? null,
        $personalIsArabic ? 'الجنسية' : 'Nationality' => $personalOriginal['nationality'] ?? null,
        $personalIsArabic ? 'الحالة الاجتماعية' : 'Marital status' => $personalOriginal['marital_status'] ?? null,
        'LinkedIn' => $personalOriginal['linkedin'] ?? null,
        $personalIsArabic ? 'الموقع الشخصي' : 'Website' => $personalOriginal['website'] ?? null,
    ]);
    $personalName = $personalOriginal['full_name'] ?? ($personalIsArabic ? 'الاسم الكامل' : 'Full name');
    $personalNameLength = mb_strlen(preg_replace('/\s+/u', '', $personalName) ?? $personalName);
    $personalNameClass = $personalNameLength > 22 ? 'cv-name-extra-long' : ($personalNameLength > 13 ? 'cv-name-long' : '');
    $order = $draft->section_order ?? \App\Models\ResumeDraft::DEFAULT_SECTION_ORDER;
    $hidden = $draft->hidden_sections ?? [];
    $photoSource = null;
    if ($draft->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($draft->photo_path)) {
        $absolutePhoto = \Illuminate\Support\Facades\Storage::disk('local')->path($draft->photo_path);
        $mime = \Illuminate\Support\Facades\File::mimeType($absolutePhoto) ?: 'image/png';
        $photoSource = $pdfMode ?? false
            ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($absolutePhoto))
            : route('resume.preview', [$draft, 'photo' => 1]);
    }
    $sectionNames = [
        'ar' => ['education'=>'التعليم والمؤهلات','experience'=>'الخبرات العملية','skills'=>'المهارات','languages'=>'اللغات','certificates'=>'الدورات والشهادات','projects'=>'المشاريع','achievements'=>'الإنجازات','volunteering'=>'العمل التطوعي','references'=>'المراجع'],
        'en' => ['education'=>'Education','experience'=>'Professional Experience','skills'=>'Skills','languages'=>'Languages','certificates'=>'Courses & Certificates','projects'=>'Projects','achievements'=>'Achievements','volunteering'=>'Volunteer Work','references'=>'References'],
    ];
    $visibleItemCount = collect($order)->reject(fn ($section) => in_array($section, $hidden, true))->sum(function ($section) use ($contentAr) {
        $data = $contentAr[$section] ?? [];
        return $section === 'references' ? count($data['items'] ?? []) : count(is_array($data) ? $data : []);
    });
    $density = $visibleItemCount >= 13 || mb_strlen(json_encode($contentAr, JSON_UNESCAPED_UNICODE) ?: '') > 7000 ? 'dense' : 'balanced';
@endphp
<div class="cv-sheet cv-bilingual-sheet template-{{ $draft->template_id }} content-{{ $density }}" dir="ltr">
    <div class="cv-bilingual-top">
        @if($photoSource)<img class="cv-bilingual-photo" src="{{ $photoSource }}" alt="">@else<div class="cv-bilingual-photo cv-photo-placeholder">{{ mb_substr($personalOriginal['full_name'] ?? 'CV', 0, 1) }}</div>@endif
        <div class="cv-header-personal" dir="{{ $personalIsArabic ? 'rtl' : 'ltr' }}">
            <small>{{ $personalIsArabic ? 'المعلومات الشخصية' : 'Personal Information' }}</small>
            <h1 class="{{ $personalNameClass }}">{{ $personalName }}</h1>
            @if($personalOriginal['job_title'] ?? null)<div class="cv-header-job">{{ $personalOriginal['job_title'] }}</div>@endif
            @if($personalContacts)<div class="cv-header-contacts">@foreach($personalContacts as $label => $value)<span><strong>{{ $label }}:</strong> {{ $value }}</span>@endforeach</div>@endif
        </div>
    </div>
    <div class="cv-bilingual-columns">
        @foreach(['en' => $contentEn, 'ar' => $contentAr] as $lang => $content)
            @php
                $isArabic = $lang === 'ar';
                $personal = $content['personal'] ?? [];
            @endphp
            <div class="cv-language-column" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
                @if($personal['summary'] ?? null)
                    <section class="cv-section cv-summary"><h3>{{ $isArabic ? 'الهدف الوظيفي' : 'Career Objective' }}</h3><div class="cv-body">{{ $personal['summary'] }}</div></section>
                @endif
                @foreach($order as $section)
                    @continue(in_array($section, $hidden, true))
                    @php
                        $sectionData = $content[$section] ?? [];
                        $items = $section === 'references' ? ($sectionData['items'] ?? []) : $sectionData;
                        $available = $section === 'references' && ($sectionData['available_on_request'] ?? false);
                    @endphp
                    @continue(!$available && !count($items))
                    <section class="cv-section cv-section-{{ $section }}">
                        <h3><span class="cv-section-mark">@if($pdfMode ?? false)<span class="cv-section-mark-core"></span>@endif</span>{{ $sectionNames[$lang][$section] }}</h3>
                        @if($available)<div class="cv-body">{{ $isArabic ? 'المراجع متاحة عند الطلب' : 'References available upon request' }}</div>@endif
                        <div class="cv-section-items">
                        @foreach($items as $item)
                            @php
                                $title = $item['qualification'] ?? $item['job_title'] ?? $item['name'] ?? $item['title'] ?? $item['role'] ?? $item['organization'] ?? '';
                                $organization = $item['institution'] ?? $item['company'] ?? $item['issuer'] ?? $item['organization'] ?? '';
                                $yearOnly = static fn ($value) => preg_match('/^(\d{4})/', (string) $value, $matches) ? $matches[1] : null;
                                if ($section === 'education') {
                                    $date = $yearOnly($item['graduation_year'] ?? $item['end_date'] ?? $item['date'] ?? null);
                                } elseif ($section === 'experience') {
                                    $endDate = ($item['current'] ?? false) ? ($isArabic ? 'حتى الآن' : 'Present') : $yearOnly($item['end_year'] ?? $item['end_date'] ?? null);
                                    $date = implode(' — ', array_filter([$yearOnly($item['start_year'] ?? $item['start_date'] ?? null), $endDate]));
                                } else {
                                    $endDate = ($item['current'] ?? false) ? ($isArabic ? 'حتى الآن' : 'Present') : ($item['end_date'] ?? $item['date'] ?? null);
                                    $date = implode(' — ', array_filter([$item['start_date'] ?? null, $endDate]));
                                }
                                $meta = array_filter([$item['major'] ?? null, $item['location'] ?? null, $item['grade'] ?? null, $item['technologies'] ?? null]);
                                $description = $item['description'] ?? $item['achievements'] ?? '';
                            @endphp
                            <article class="cv-item">
                                <div class="cv-item-heading"><strong>{{ $title }}</strong>@if($date)<time>{{ $date }}</time>@endif @if($item['level'] ?? null)<span class="cv-level">{{ $item['level'] }}</span>@endif</div>
                                @if($organization && $organization !== $title)<small class="cv-organization">{{ $organization }}</small>@endif
                                @if($meta)<div class="cv-item-meta">@foreach($meta as $value)<span>{{ $value }}</span>@endforeach</div>@endif
                                @if($description)<div class="cv-body">{{ $description }}</div>@endif
                                @if($item['url'] ?? null)<div class="cv-link">{{ $item['url'] }}</div>@endif
                            </article>
                        @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endforeach
    </div>
    @unless($paid)<div class="cv-watermark" aria-hidden="true">@for($i=0;$i<28;$i++)<span>معاينة غير مدفوعة — الورّاق</span>@endfor</div>@endunless
</div>
