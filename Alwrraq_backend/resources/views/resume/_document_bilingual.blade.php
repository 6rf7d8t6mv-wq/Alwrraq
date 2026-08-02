@php
    $contentAr = $draft->content ?? [];
    $contentEn = $contentAr['content_en'] ?? [];
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
        @if($photoSource)<img class="cv-bilingual-photo" src="{{ $photoSource }}" alt="">@else<div class="cv-bilingual-photo cv-photo-placeholder">{{ mb_substr(data_get($contentAr, 'personal.full_name', 'CV'), 0, 1) }}</div>@endif
        <div><strong>السيرة الذاتية | CURRICULUM VITAE</strong><span>العربية وEnglish</span></div>
    </div>
    <div class="cv-bilingual-columns">
        @foreach(['en' => $contentEn, 'ar' => $contentAr] as $lang => $content)
            @php
                $isArabic = $lang === 'ar';
                $personal = $content['personal'] ?? [];
                $address = implode($isArabic ? '، ' : ', ', array_filter([$personal['city'] ?? null, $personal['country'] ?? null]));
                $contacts = array_filter([
                    $isArabic ? 'رقم الجوال' : 'Phone' => $personal['phone'] ?? null,
                    $isArabic ? 'البريد الإلكتروني' : 'Email' => $personal['email'] ?? null,
                    $isArabic ? 'العنوان' : 'Address' => $address ?: null,
                    $isArabic ? 'تاريخ الميلاد' : 'Birth date' => $personal['birth_date'] ?? null,
                    $isArabic ? 'الجنسية' : 'Nationality' => $personal['nationality'] ?? null,
                    $isArabic ? 'الحالة الاجتماعية' : 'Marital status' => $personal['marital_status'] ?? null,
                ]);
                $fullName = $personal['full_name'] ?? ($isArabic ? 'الاسم الكامل' : 'Full name');
                $compactNameLength = mb_strlen(preg_replace('/\s+/u', '', $fullName) ?? $fullName);
                $nameSizeClass = $compactNameLength > 22 ? 'cv-name-extra-long' : ($compactNameLength > 13 ? 'cv-name-long' : '');
            @endphp
            <div class="cv-language-column" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
                <header class="cv-language-heading">
                    <h1 class="{{ $nameSizeClass }}">{{ $fullName }}</h1>
                    <div class="cv-job">{{ $personal['job_title'] ?? ($isArabic ? 'المسمى الوظيفي' : 'Job title') }}</div>
                    @if($contacts)<div class="cv-bilingual-contacts">@foreach($contacts as $label => $value)<span><strong>{{ $label }}:</strong> {{ $value }}</span>@endforeach</div>@endif
                </header>
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
                        <h3><span class="cv-section-mark"></span>{{ $sectionNames[$lang][$section] }}</h3>
                        @if($available)<div class="cv-body">{{ $isArabic ? 'المراجع متاحة عند الطلب' : 'References available upon request' }}</div>@endif
                        <div class="cv-section-items">
                        @foreach($items as $item)
                            @php
                                $title = $item['qualification'] ?? $item['job_title'] ?? $item['name'] ?? $item['title'] ?? $item['role'] ?? $item['organization'] ?? '';
                                $organization = $item['institution'] ?? $item['company'] ?? $item['issuer'] ?? $item['organization'] ?? '';
                                $endDate = ($item['current'] ?? false) ? ($isArabic ? 'حتى الآن' : 'Present') : ($item['end_date'] ?? $item['date'] ?? null);
                                $date = implode(' — ', array_filter([$item['start_date'] ?? null, $endDate]));
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
