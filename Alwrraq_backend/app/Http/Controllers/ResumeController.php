<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ResumeDraft;
use App\Models\ServiceDefinition;
use App\Services\AutomaticTranslationService;
use App\Services\CartPricingService;
use App\Services\ResumeDocumentService;
use App\Services\ServicePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResumeController extends Controller
{
    private const TRANSLATION_VERSION = 2;

    private const EXPORT_VERSION = 3;

    public function landing(Request $request, ServicePricingService $pricing)
    {
        $draft = $this->latestEditableDraft($request);
        $resumePrice = $this->resumePrice($pricing);

        return response()
            ->view('resume.landing', compact('draft', 'resumePrice'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function start(Request $request)
    {
        $draft = $this->latestEditableDraft($request);

        if (! $draft) {
            $draft = ResumeDraft::query()->create([
                'user_id' => $request->user()->id,
                'template_id' => 'executive_classic',
                'language' => 'bilingual',
                'content' => $this->emptyContent(),
                'section_order' => ResumeDraft::DEFAULT_SECTION_ORDER,
                'hidden_sections' => [],
                'status' => 'draft',
            ]);
        }

        return redirect()->route('resume.edit', $draft);
    }

    public function edit(Request $request, ResumeDraft $resumeDraft, ServicePricingService $pricing)
    {
        $this->authorizeDraft($request, $resumeDraft);
        $resumeDraft->load('order');
        if ($resumeDraft->isPaid()) {
            return redirect()
                ->route('resume.preview', $resumeDraft)
                ->with('status', 'تم إقفال التعديل بعد الدفع، وهذه هي النسخة النهائية.');
        }

        if ($resumeDraft->language !== 'bilingual') {
            $resumeDraft->forceFill(['language' => 'bilingual'])->save();
        }

        return response()
            ->view('resume.editor', [
                'draft' => $resumeDraft,
                'paid' => false,
                'resumePrice' => $this->resumePrice($pricing),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function update(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizeEditableDraft($request, $resumeDraft);
        $request->merge([
            'content' => $this->normalizeResumeYears($request->input('content', [])),
        ]);
        $data = $request->validate([
            'template_id' => ['required', Rule::in(array_keys(ResumeDraft::TEMPLATES))],
            'language' => ['required', Rule::in(['ar', 'en', 'bilingual'])],
            'content' => ['required', 'array'],
            'content.personal.full_name' => ['nullable', 'string', 'max:150'],
            'content.personal.job_title' => ['nullable', 'string', 'max:150'],
            'content.personal.summary' => ['nullable', 'string', 'max:2000'],
            'content.personal.phone' => ['nullable', 'string', 'max:60'],
            'content.personal.email' => ['nullable', 'email:rfc', 'max:190'],
            'content.personal.city' => ['nullable', 'string', 'max:100'],
            'content.personal.country' => ['nullable', 'string', 'max:100'],
            'content.personal.birth_date' => ['nullable', 'date'],
            'content.personal.nationality' => ['nullable', 'string', 'max:100'],
            'content.personal.marital_status' => ['nullable', 'string', 'max:100'],
            'content.personal.linkedin' => ['nullable', 'url', 'max:500'],
            'content.personal.website' => ['nullable', 'url', 'max:500'],
            'content.education.*.graduation_year' => ['nullable', 'regex:/^\d{4}$/'],
            'content.experience.*.start_year' => ['nullable', 'regex:/^\d{4}$/'],
            'content.experience.*.end_year' => ['nullable', 'regex:/^\d{4}$/'],
            'content.*' => ['array'],
            'section_order' => ['required', 'array', 'size:9'],
            'section_order.*' => [Rule::in(ResumeDraft::DEFAULT_SECTION_ORDER), 'distinct'],
            'hidden_sections' => ['array'],
            'hidden_sections.*' => [Rule::in(ResumeDraft::DEFAULT_SECTION_ORDER), 'distinct'],
        ]);

        $this->deleteGeneratedDocuments($resumeDraft);
        $data['language'] = 'bilingual';
        $data['image_path'] = null;
        $data['pdf_path'] = null;
        $resumeDraft->update($data);

        return response()->json([
            'success' => true,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function translate(Request $request, ResumeDraft $resumeDraft, AutomaticTranslationService $translator)
    {
        // Translation only fills the generated language copies. It does not
        // modify the customer's original content, so an older paid resume may
        // safely generate missing translations when it is opened again.
        $this->authorizeDraft($request, $resumeDraft);
        $translation = $this->generateBilingualContent($resumeDraft, $translator);

        return response()->json([
            'success' => true,
            'content_ar' => $translation['content_ar'],
            'content_en' => $translation['content_en'],
            'configured' => $translator->isConfigured(),
            'translated' => $translation['translated'],
        ]);
    }

    /** @return array{content_ar: array, content_en: array, translated: bool} */
    private function generateBilingualContent(ResumeDraft $resumeDraft, AutomaticTranslationService $translator): array
    {
        $content = $resumeDraft->content ?? [];
        unset($content['content_ar'], $content['content_en']);

        $nonTranslatableKeys = [
            'phone', 'email', 'birth_date', 'linkedin', 'website', 'url',
            'start_date', 'end_date', 'date', 'graduation_year', 'start_year', 'end_year', 'current',
        ];
        $shouldTranslate = static fn ($value, $key): bool => is_string($value)
            && trim($value) !== ''
            && ! in_array((string) $key, $nonTranslatableKeys, true)
            && ! filter_var($value, FILTER_VALIDATE_URL)
            && ! filter_var($value, FILTER_VALIDATE_EMAIL);

        $arabicTexts = [];
        $englishTexts = [];
        $collectTexts = function ($value, $key = null) use (&$collectTexts, &$arabicTexts, &$englishTexts, $shouldTranslate): void {
            if (is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    $collectTexts($childValue, $childKey);
                }

                return;
            }
            if (! $shouldTranslate($value, $key)) {
                return;
            }

            $text = trim($value);
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
                $arabicTexts[] = $text;
            } elseif (preg_match('/[A-Za-z]/', $text)) {
                $englishTexts[] = $text;
            }
        };
        $collectTexts($content);

        $arToEn = [];
        foreach (array_chunk(array_values(array_unique($arabicTexts)), 100) as $batch) {
            $arToEn += $translator->translate($batch, 'ar', 'en');
        }
        $enToAr = [];
        foreach (array_chunk(array_values(array_unique($englishTexts)), 100) as $batch) {
            $enToAr += $translator->translate($batch, 'en', 'ar');
        }

        $translateValue = function ($value, string $targetLanguage, $key = null) use (&$translateValue, $arToEn, $enToAr, $shouldTranslate) {
            if (is_array($value)) {
                $translated = [];
                foreach ($value as $childKey => $childValue) {
                    $translated[$childKey] = $translateValue($childValue, $targetLanguage, $childKey);
                }

                return $translated;
            }
            if (! $shouldTranslate($value, $key)) {
                return $value;
            }

            $text = trim($value);
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
                return $targetLanguage === 'en' ? ($arToEn[$text] ?? $value) : $value;
            }
            if (preg_match('/[A-Za-z]/', $text)) {
                return $targetLanguage === 'ar' ? ($enToAr[$text] ?? $value) : $value;
            }

            return $value;
        };

        $contentAr = $translateValue($content, 'ar');
        $contentEn = $translateValue($content, 'en');

        $translationSucceeded = count($arToEn) === count(array_unique($arabicTexts))
            && count($enToAr) === count(array_unique($englishTexts));

        if ($translationSucceeded) {
            $contentAr['_translation_version'] = self::TRANSLATION_VERSION;
            $contentEn['_translation_version'] = self::TRANSLATION_VERSION;
            $content['content_ar'] = $contentAr;
            $content['content_en'] = $contentEn;
            $this->deleteGeneratedDocuments($resumeDraft);
            $resumeDraft->forceFill([
                'content' => $content,
                'language' => 'bilingual',
                'image_path' => null,
                'pdf_path' => null,
            ])->save();
        }

        return [
            'content_ar' => $contentAr,
            'content_en' => $contentEn,
            'translated' => $translationSucceeded,
        ];
    }

    public function photo(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizeEditableDraft($request, $resumeDraft);
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($resumeDraft->photo_path) {
            Storage::disk('local')->delete($resumeDraft->photo_path);
        }

        $path = $data['photo']->store('private/resumes/photos', 'local');
        $resumeDraft->update(['photo_path' => $path]);

        return response()->json([
            'success' => true,
            'photo_url' => route('resume.preview', [$resumeDraft, 'photo' => 1]),
        ]);
    }

    public function destroyPhoto(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizeEditableDraft($request, $resumeDraft);
        if ($resumeDraft->photo_path) {
            Storage::disk('local')->delete($resumeDraft->photo_path);
        }
        $resumeDraft->update(['photo_path' => null]);

        return response()->json(['success' => true]);
    }

    public function checkout(Request $request, ResumeDraft $resumeDraft, CartPricingService $cartPricing)
    {
        $this->authorizeEditableDraft($request, $resumeDraft);
        $this->validateRequiredContent($resumeDraft);

        $order = DB::transaction(function () use ($request, $resumeDraft, $cartPricing): Order {
            $lockedDraft = ResumeDraft::query()->lockForUpdate()->findOrFail($resumeDraft->id);
            if ($lockedDraft->order_id) {
                return Order::query()->findOrFail($lockedDraft->order_id);
            }

            $service = ServiceDefinition::query()
                ->where('code', 'resume')
                ->where('is_active', true)
                ->firstOrFail();

            $order = Order::query()->create([
                'user_id' => $request->user()->id,
                'service_type' => 'resume',
                'service_definition_id' => $service->id,
                'status' => 'new',
                'payment_status' => 'unpaid',
                'print_total' => 0,
                'binding_total' => 0,
                'discount_amount' => 0,
                'delivery_fee' => 0,
                'grand_total' => 0,
            ]);
            $lockedDraft->update(['order_id' => $order->id, 'status' => 'pending_payment']);
            $order->setRelation('resumeDraft', $lockedDraft);
            $cartPricing->refreshOrderTotals($order);

            return $order->refresh();
        }, 3);

        return redirect()->route('cart.index')->with(
            'status',
            'تمت إضافة خدمة السيرة الذاتية إلى السلة. يمكنك تطبيق الخصم ثم إتمام الطلب.'
        );
    }

    public function preview(Request $request, ResumeDraft $resumeDraft, AutomaticTranslationService $translator)
    {
        $this->authorizeDraft($request, $resumeDraft);

        if ($request->boolean('photo')) {
            abort_unless($resumeDraft->photo_path && Storage::disk('local')->exists($resumeDraft->photo_path), 404);

            return response()->file(Storage::disk('local')->path($resumeDraft->photo_path), [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $resumeDraft->load('order');
        $translationReady = $this->hasCurrentTranslations($resumeDraft);
        if (! $translationReady) {
            $translationReady = $this->generateBilingualContent($resumeDraft, $translator)['translated'];
            $resumeDraft->refresh()->load('order');
        }
        $paid = $resumeDraft->isPaid();
        $finalImageReady = $paid && $translationReady && $this->hasCurrentFinalImage($resumeDraft);
        $imageDownloadUrl = $finalImageReady ? $this->versionedDownloadUrl($resumeDraft, 'image') : null;
        $pdfDownloadUrl = $finalImageReady ? $this->versionedDownloadUrl($resumeDraft, 'pdf') : null;
        $isAdminViewer = (int) $resumeDraft->user_id !== (int) $request->user()->id
            && $request->user()->role === 'admin';
        $source = $request->string('from')->toString();
        [$backUrl, $backLabel] = match (true) {
            $isAdminViewer => [
                route('admin.orders', ['open_order' => $resumeDraft->order_id]),
                'الرجوع لطلبات الإدارة',
            ],
            $source === 'cart' => [route('cart.index'), 'الرجوع للسلة'],
            $source === 'orders' => [route('orders.index'), 'الرجوع للطلبات'],
            default => [route('home'), 'الرجوع للخدمات'],
        };

        return response()
            ->view('resume.preview', [
                'draft' => $resumeDraft,
                'paid' => $paid,
                'backUrl' => $backUrl,
                'backLabel' => $backLabel,
                'isAdminViewer' => $isAdminViewer,
                'translationReady' => $translationReady,
                'finalImageReady' => $finalImageReady,
                'imageDownloadUrl' => $imageDownloadUrl,
                'pdfDownloadUrl' => $pdfDownloadUrl,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    public function downloadPdf(Request $request, ResumeDraft $resumeDraft, ResumeDocumentService $documents)
    {
        $this->authorizePaidDraft($request, $resumeDraft);
        if (! $this->hasCurrentTranslations($resumeDraft) || ! $this->hasCurrentFinalImage($resumeDraft)) {
            return redirect()->route('resume.preview', [
                'resumeDraft' => $resumeDraft,
                'from' => $request->user()->role === 'admin' ? 'admin' : 'orders',
                'auto_download' => 'pdf',
            ]);
        }
        $path = $documents->ensurePdf($resumeDraft);

        return $this->freshDownloadResponse($path, $this->downloadFilename($resumeDraft, 'pdf'));
    }

    public function downloadImage(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizePaidDraft($request, $resumeDraft);
        if (! $this->hasCurrentTranslations($resumeDraft) || ! $this->hasCurrentFinalImage($resumeDraft)) {
            return redirect()->route('resume.preview', [
                'resumeDraft' => $resumeDraft,
                'from' => $request->user()->role === 'admin' ? 'admin' : 'orders',
            ]);
        }

        return $this->freshDownloadResponse(
            Storage::disk('local')->path($resumeDraft->image_path),
            $this->downloadFilename($resumeDraft, 'png')
        );
    }

    public function storeFinalImage(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizePaidDraft($request, $resumeDraft);
        abort_unless($this->hasCurrentTranslations($resumeDraft), 409, 'يجب إكمال ترجمة السيرة قبل إنشاء النسخة النهائية.');
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:png', 'dimensions:width=2382,height=3369', 'max:20480'],
        ]);

        if ($resumeDraft->image_path) {
            Storage::disk('local')->delete($resumeDraft->image_path);
        }
        if ($resumeDraft->pdf_path) {
            Storage::disk('local')->delete($resumeDraft->pdf_path);
        }
        $path = $data['image']->storeAs(
            'private/resumes/final',
            'resume-'.$resumeDraft->id.'-v'.self::EXPORT_VERSION.'-'.now()->format('YmdHisv').'-'.str()->random(8).'.png',
            'local'
        );
        $resumeDraft->update(['image_path' => $path, 'pdf_path' => null]);

        $resumeDraft->refresh();

        return response()->json([
            'success' => true,
            'download_url' => $this->versionedDownloadUrl($resumeDraft, 'image'),
            'image_download_url' => $this->versionedDownloadUrl($resumeDraft, 'image'),
            'pdf_download_url' => $this->versionedDownloadUrl($resumeDraft, 'pdf'),
        ]);
    }

    private function authorizeDraft(Request $request, ResumeDraft $draft): void
    {
        $isOwner = (int) $draft->user_id === (int) $request->user()->id;
        $isAuthorizedAdmin = $request->user()->role === 'admin'
            && $request->user()->hasAdminPermission('files_download');
        abort_unless($isOwner || $isAuthorizedAdmin, 403);
    }

    private function deleteGeneratedDocuments(ResumeDraft $draft): void
    {
        foreach (array_filter([$draft->image_path, $draft->pdf_path]) as $path) {
            Storage::disk('local')->delete($path);
        }
    }

    private function hasCurrentFinalImage(ResumeDraft $draft): bool
    {
        return filled($draft->image_path)
            && str_contains(basename($draft->image_path), 'resume-'.$draft->id.'-v'.self::EXPORT_VERSION.'-')
            && Storage::disk('local')->exists($draft->image_path);
    }

    private function versionedDownloadUrl(ResumeDraft $draft, string $format): string
    {
        $extension = $format === 'image' ? 'png' : 'pdf';
        $version = pathinfo((string) $draft->image_path, PATHINFO_FILENAME);

        return route('resume.download.'.$format, [
            'resumeDraft' => $draft,
            'download' => 1,
            'filename' => $this->downloadFilename($draft, $extension),
            'v' => $version,
        ]);
    }

    private function downloadFilename(ResumeDraft $draft, string $extension): string
    {
        $version = pathinfo((string) $draft->image_path, PATHINFO_FILENAME);
        $version = $version !== '' ? $version : 'resume-'.$draft->id.'-'.now()->format('YmdHis');

        return 'professional-'.$version.'.'.$extension;
    }

    private function freshDownloadResponse(string $path, string $filename)
    {
        $response = response()->download($path, $filename, [
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'Accept-Ranges' => 'bytes',
        ]);
        $response->headers->set(
            'Cache-Control',
            request()->filled('v')
                ? 'private, max-age=31536000, immutable'
                : 'private, no-store, no-cache, must-revalidate, max-age=0'
        );

        return $response;
    }

    private function hasCurrentTranslations(ResumeDraft $draft): bool
    {
        return data_get($draft->content, 'content_ar._translation_version') === self::TRANSLATION_VERSION
            && data_get($draft->content, 'content_en._translation_version') === self::TRANSLATION_VERSION;
    }

    private function authorizeEditableDraft(Request $request, ResumeDraft $draft): void
    {
        abort_unless((int) $draft->user_id === (int) $request->user()->id, 403);
        $draft->loadMissing('order');
        abort_if($draft->isPaid(), 409, 'لا يمكن تعديل السيرة الذاتية بعد الدفع.');
    }

    private function authorizePaidDraft(Request $request, ResumeDraft $draft): void
    {
        $this->authorizeDraft($request, $draft);
        $draft->loadMissing('order');
        abort_unless($draft->isPaid(), 403);
    }

    private function validateRequiredContent(ResumeDraft $draft): void
    {
        $personal = data_get($draft->content, 'personal', []);
        $errors = [];
        if (blank($personal['full_name'] ?? null)) {
            $errors['full_name'] = 'الاسم الكامل مطلوب.';
        }
        if (blank($personal['job_title'] ?? null)) {
            $errors['job_title'] = 'المسمى الوظيفي أو التخصص مطلوب.';
        }
        if (blank($personal['phone'] ?? null) && blank($personal['email'] ?? null)) {
            $errors['contact'] = 'أدخل رقم الجوال أو البريد الإلكتروني على الأقل.';
        }
        if (! array_key_exists($draft->template_id, ResumeDraft::TEMPLATES)) {
            $errors['template_id'] = 'اختر تصميم السيرة الذاتية.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function latestEditableDraft(Request $request): ?ResumeDraft
    {
        return ResumeDraft::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['draft', 'pending_payment'])
            ->where(function ($query): void {
                $query->whereNull('order_id')
                    ->orWhereHas('order', fn ($order) => $order->whereNotIn(
                        'payment_status',
                        ['paid', 'voided', 'refunded']
                    ));
            })
            ->latest()
            ->first();
    }

    private function normalizeResumeYears(array $content): array
    {
        foreach ($content['education'] ?? [] as $index => $item) {
            $content['education'][$index]['graduation_year'] = $this->yearFrom(
                $item['graduation_year'] ?? $item['end_date'] ?? $item['date'] ?? null
            );
            unset($content['education'][$index]['start_date'], $content['education'][$index]['end_date'], $content['education'][$index]['date']);
        }

        foreach ($content['experience'] ?? [] as $index => $item) {
            $content['experience'][$index]['start_year'] = $this->yearFrom(
                $item['start_year'] ?? $item['start_date'] ?? null
            );
            $content['experience'][$index]['end_year'] = $this->yearFrom(
                $item['end_year'] ?? $item['end_date'] ?? null
            );
            unset($content['experience'][$index]['start_date'], $content['experience'][$index]['end_date']);
        }

        return $content;
    }

    private function yearFrom(mixed $value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^(\d{4})/', $value, $matches) ? $matches[1] : null;
    }

    private function resumePrice(ServicePricingService $pricing): float
    {
        $service = ServiceDefinition::query()
            ->where('code', 'resume')
            ->where('is_active', true)
            ->first();

        return $service
            ? $pricing->customServicePrice($service)
            : ServicePricingService::RESUME_SERVICE_DEFAULT_PRICE;
    }

    private function emptyContent(): array
    {
        return [
            'personal' => [],
            'education' => [],
            'experience' => [],
            'skills' => [],
            'languages' => [],
            'certificates' => [],
            'projects' => [],
            'achievements' => [],
            'volunteering' => [],
            'references' => ['available_on_request' => false, 'items' => []],
        ];
    }
}
