<?php

namespace Tests\Feature;

use App\Http\Middleware\ReconcileMoyasarPayments;
use App\Http\Middleware\TrackUserPresence;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\User;
use App\Services\LivePageUpdateService;
use App\Services\WordPreviewService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;
use ZipArchive;

class DocumentPreviewTest extends TestCase
{
    private string $fixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            TrackUserPresence::class,
            ReconcileMoyasarPayments::class,
        ]);
        $this->app->instance(LivePageUpdateService::class, new class extends LivePageUpdateService
        {
            public function snapshot(User $user): array
            {
                return [
                    'revision' => 'document-preview-test',
                    'orders_count' => 1,
                    'unseen_count' => 0,
                    'role' => $user->role,
                    'pricing_revision' => 'document-preview-pricing-test',
                ];
            }
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_token')->nullable();
            $table->string('service_type');
            $table->string('status')->default('new');
            $table->string('payment_status')->default('unpaid');
            $table->timestamps();
        });
        Schema::create('order_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('file_type');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('pages')->default(1);
            $table->unsignedInteger('copies')->default(1);
            $table->timestamps();
        });

        $this->fixtureDirectory = storage_path('app/testing-document-preview');
        File::ensureDirectoryExists($this->fixtureDirectory);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->fixtureDirectory);
        parent::tearDown();
    }

    public function test_pdf_viewer_and_raw_pdf_are_available_to_the_owner(): void
    {
        [$user, $order] = $this->ownerAndOrder();
        $path = $this->storeFixture('sample.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");
        $file = $this->orderFile($order, 'pdf', 'sample.pdf', $path);

        $viewer = $this->actingAs($user)->get(route('orders.file.view', [$order, $file]));

        $viewer
            ->assertOk()
            ->assertSee('uploadedPdfPreview', false)
            ->assertSee('/document-viewer/pdf.min.js', false);

        $this->actingAs($user)
            ->get(route('orders.file.view', [$order, $file, 'raw' => 1]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_docx_text_is_rendered_inside_the_word_viewer(): void
    {
        [$user, $order] = $this->ownerAndOrder();
        $path = 'testing-document-preview/sample.docx';
        $this->createDocx(storage_path('app/'.$path), 'معاينة ملف الوورد تعمل بنجاح');
        $file = $this->orderFile($order, 'word', 'sample.docx', $path);

        $this->actingAs($user)
            ->get(route('orders.file.view', [$order, $file]))
            ->assertOk()
            ->assertSee('word-preview', false)
            ->assertSee('معاينة ملف الوورد تعمل بنجاح');
    }

    public function test_dependency_free_docx_reader_extracts_the_document_xml(): void
    {
        $path = $this->fixtureDirectory.'/fallback.docx';
        $this->createDocx($path, 'قارئ احتياطي');
        $method = new ReflectionMethod(WordPreviewService::class, 'readZipEntry');

        $xml = $method->invoke(new WordPreviewService, $path, 'word/document.xml');

        $this->assertIsString($xml);
        $this->assertStringContainsString('قارئ احتياطي', $xml);
    }

    public function test_document_viewer_scripts_are_served_by_laravel(): void
    {
        $this->get(route('document-viewer.asset', ['asset' => 'pdf.min.js']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');
        $this->get(route('document-viewer.asset', ['asset' => 'pdf.worker.min.js']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');
        $this->get('/document-viewer/not-allowed.js')->assertNotFound();
    }

    private function ownerAndOrder(): array
    {
        $user = User::query()->create([
            'name' => 'Document Owner',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'service_type' => 'formatting',
            'status' => 'new',
            'payment_status' => 'unpaid',
        ]);

        return [$user, $order];
    }

    private function orderFile(Order $order, string $type, string $name, string $path): OrderFile
    {
        return OrderFile::query()->create([
            'order_id' => $order->id,
            'file_type' => $type,
            'original_name' => $name,
            'stored_name' => basename($path),
            'path' => $path,
            'size' => File::size(storage_path('app/'.$path)),
            'pages' => 1,
            'copies' => 1,
        ]);
    }

    private function storeFixture(string $name, string $contents): string
    {
        $path = 'testing-document-preview/'.$name;
        File::put(storage_path('app/'.$path), $contents);

        return $path;
    }

    private function createDocx(string $path, string $text): void
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/></Types>');
        $zip->addFromString(
            'word/document.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body><w:p><w:r><w:t>'.htmlspecialchars($text, ENT_XML1).'</w:t></w:r></w:p></w:body></w:document>'
        );
        $zip->close();
    }
}
