@extends('admin.layout')

@section('title', 'إدارة الخدمات - لوحة المدير')

@section('content')
    <style>
        .service-image-field { display: grid; gap: 7px; }
        .service-image-preview { width: 84px; height: 84px; overflow: hidden; display: grid; place-items: center; border: 1px solid #dbe3ef; border-radius: 12px; background: #f8fafc; }
        .service-image-preview img { width: 100%; height: 100%; display: block; object-fit: contain; background: #ffffff; }
        .service-image-preview span { padding: 8px; color: #94a3b8; font-size: 9px; font-weight: 900; line-height: 1.4; text-align: center; }
    </style>

    <div class="page-title compact-page-title">
        <div>
            <h1>إدارة الخدمات</h1>
            <p class="subtitle">تعديل بيانات الخدمات الحالية أو إضافة خدمة جديدة من نموذج خدمة موجود.</p>
        </div>
        @if (auth()->user()->hasAdminPermission('services_create'))
            <button class="save" type="button" onclick="showServiceModal('addServiceModal')">إضافة خدمة جديدة</button>
        @endif
    </div>

    <section class="panel compact-management-panel blue-records-panel">
        <div class="management-table-wrap">
            <table class="management-table">
                <thead>
                    <tr>
                        <th>الخدمة</th>
                        <th>نموذج الصفحة</th>
                        <th>طلب ملف</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td>
                                <strong>{{ $service->icon }} {{ $service->title }}</strong>
                                <div class="muted">{{ $service->description }}</div>
                            </td>
                            <td>{{ $workflows[$service->workflow_type] }}</td>
                            <td>{{ $service->requires_file ? 'نعم' : 'لا' }}</td>
                            <td>
                                @if (auth()->user()->hasAdminPermission('services_update'))
                                    <button
                                        class="ghost"
                                        type="button"
                                        data-service="{{ json_encode([
                                            'id' => $service->id,
                                            'title' => $service->title,
                                            'description' => $service->description,
                                            'icon' => $service->icon,
                                            'image_url' => $service->image_path
                                                ? route('services.image', ['filename' => basename($service->image_path)], false)
                                                : null,
                                            'workflow_type' => $service->workflow_type,
                                        ], JSON_UNESCAPED_UNICODE) }}"
                                        onclick="openServiceEditor(JSON.parse(this.dataset.service))"
                                    >تعديل</button>
                                @else
                                    <span class="muted">عرض فقط</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if (auth()->user()->hasAdminPermission('services_create'))
    <div class="modal-backdrop" id="addServiceModal" onclick="hideServiceModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-head">
                <h2>إضافة خدمة جديدة</h2>
                <button class="modal-close" type="button" onclick="hideServiceModal()">إغلاق</button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('admin.partials.service-fields')
                    <button class="save" type="submit">إضافة الخدمة</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if (auth()->user()->hasAdminPermission('services_update'))
    <div class="modal-backdrop" id="editServiceModal" onclick="hideServiceModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-head">
                <h2>تعديل الخدمة</h2>
                <button class="modal-close" type="button" onclick="hideServiceModal()">إغلاق</button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm" method="post" action="" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    @include('admin.partials.service-fields', ['fieldPrefix' => 'edit_'])
                    <button class="save" type="submit">حفظ التعديل</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function showServiceModal(id) {
            document.getElementById(id)?.classList.add('active');
        }

        function hideServiceModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.querySelectorAll('#addServiceModal, #editServiceModal').forEach((modal) => {
                modal.classList.remove('active');
            });
        }

        function openServiceEditor(service) {
            const form = document.getElementById('editServiceForm');
            form.action = @js(url('/admin/services')) + '/' + service.id;
            document.getElementById('edit_service_title').value = service.title || '';
            document.getElementById('edit_service_description').value = service.description || '';
            document.getElementById('edit_service_icon').value = service.icon || '';
            document.getElementById('edit_service_workflow').value = service.workflow_type || '';
            const imageInput = document.getElementById('edit_service_image');
            if (imageInput) imageInput.value = '';
            showServiceImagePreview('edit_', service.image_url || '', service.image_url ? 'الصورة الحالية' : 'لا توجد صورة حالية');
            showServiceModal('editServiceModal');
        }

        function showServiceImagePreview(prefix, source, placeholderText = 'لم يتم اختيار صورة') {
            const preview = document.getElementById(`${prefix}service_image_preview`);
            const placeholder = document.getElementById(`${prefix}service_image_placeholder`);
            if (!preview || !placeholder) return;

            if (!source) {
                preview.hidden = true;
                preview.removeAttribute('src');
                placeholder.hidden = false;
                placeholder.textContent = placeholderText;
                return;
            }

            preview.onload = () => {
                preview.hidden = false;
                placeholder.hidden = true;
            };
            preview.onerror = () => {
                preview.hidden = true;
                placeholder.hidden = false;
                placeholder.textContent = 'تعذر عرض الصورة';
            };
            preview.src = source;
        }

        document.addEventListener('change', (event) => {
            const input = event.target.closest('[data-service-image-input]');
            const file = input?.files?.[0];
            if (!input || !file) return;

            const prefix = input.id.startsWith('edit_') ? 'edit_' : 'add_';
            const objectUrl = URL.createObjectURL(file);
            const preview = document.getElementById(`${prefix}service_image_preview`);
            if (preview) {
                const releaseObjectUrl = () => URL.revokeObjectURL(objectUrl);
                preview.addEventListener('load', releaseObjectUrl, { once: true });
                preview.addEventListener('error', releaseObjectUrl, { once: true });
            }
            showServiceImagePreview(prefix, objectUrl, 'تعذر معاينة الصورة المختارة');
        });
    </script>
@endsection
