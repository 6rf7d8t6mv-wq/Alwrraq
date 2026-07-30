<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ResumeDraft;
use App\Models\ServiceDefinition;
use App\Services\CartPricingService;
use App\Services\ResumeDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResumeController extends Controller
{
    public function landing(Request $request)
    {
        $draft = ResumeDraft::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['draft', 'pending_payment'])
            ->latest()
            ->first();

        return response()
            ->view('resume.landing', compact('draft'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function start(Request $request)
    {
        $draft = ResumeDraft::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'draft')
            ->latest()
            ->first();

        if (! $draft) {
            $draft = ResumeDraft::query()->create([
                'user_id' => $request->user()->id,
                'template_id' => 'executive_classic',
                'language' => 'ar',
                'content' => $this->emptyContent(),
                'section_order' => ResumeDraft::DEFAULT_SECTION_ORDER,
                'hidden_sections' => [],
                'status' => 'draft',
            ]);
        }

        return redirect()->route('resume.edit', $draft);
    }

    public function edit(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizeDraft($request, $resumeDraft);
        $resumeDraft->load('order');

        return response()
            ->view('resume.editor', [
                'draft' => $resumeDraft,
                'paid' => $resumeDraft->isPaid(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function update(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizeEditableDraft($request, $resumeDraft);
        $data = $request->validate([
            'template_id' => ['required', Rule::in(['executive_classic'])],
            'language' => ['required', Rule::in(['ar', 'en'])],
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
            'content.*' => ['array'],
            'section_order' => ['required', 'array', 'size:9'],
            'section_order.*' => [Rule::in(ResumeDraft::DEFAULT_SECTION_ORDER), 'distinct'],
            'hidden_sections' => ['array'],
            'hidden_sections.*' => [Rule::in(ResumeDraft::DEFAULT_SECTION_ORDER), 'distinct'],
        ]);

        $resumeDraft->update($data);

        return response()->json([
            'success' => true,
            'saved_at' => now()->toIso8601String(),
        ]);
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

    public function preview(Request $request, ResumeDraft $resumeDraft)
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
        $paid = $resumeDraft->isPaid();

        return response()
            ->view('resume.preview', ['draft' => $resumeDraft, 'paid' => $paid])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    public function downloadPdf(Request $request, ResumeDraft $resumeDraft, ResumeDocumentService $documents)
    {
        $this->authorizePaidDraft($request, $resumeDraft);
        $path = $documents->ensurePdf($resumeDraft);

        return response()->download($path, 'professional-resume-'.$resumeDraft->id.'.pdf');
    }

    public function downloadImage(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizePaidDraft($request, $resumeDraft);
        abort_unless($resumeDraft->image_path && Storage::disk('local')->exists($resumeDraft->image_path), 404);

        return response()->download(
            Storage::disk('local')->path($resumeDraft->image_path),
            'professional-resume-'.$resumeDraft->id.'.png'
        );
    }

    public function storeFinalImage(Request $request, ResumeDraft $resumeDraft)
    {
        $this->authorizePaidDraft($request, $resumeDraft);
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:png', 'max:20480'],
        ]);

        if ($resumeDraft->image_path) {
            Storage::disk('local')->delete($resumeDraft->image_path);
        }
        $path = $data['image']->store('private/resumes/final', 'local');
        $resumeDraft->update(['image_path' => $path]);

        return response()->json(['success' => true, 'download_url' => route('resume.download.image', $resumeDraft)]);
    }

    private function authorizeDraft(Request $request, ResumeDraft $draft): void
    {
        $isOwner = (int) $draft->user_id === (int) $request->user()->id;
        $isAuthorizedAdmin = $request->user()->role === 'admin'
            && $request->user()->hasAdminPermission('files_download');
        abort_unless($isOwner || $isAuthorizedAdmin, 403);
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
        if ($draft->template_id !== 'executive_classic') {
            $errors['template_id'] = 'اختر تصميم السيرة الذاتية.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
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
