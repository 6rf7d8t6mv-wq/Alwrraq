@extends('admin.layout')

@section('title', 'إدارة الخدمات - لوحة المدير')

@section('content')
    <div class="page-title compact-page-title">
        <div>
            <h1>إدارة الخدمات</h1>
            <p class="subtitle">تعديل بيانات الخدمات الحالية أو إضافة خدمة جديدة من نموذج خدمة موجود.</p>
        </div>
        <button class="save" type="button" onclick="showServiceModal('addServiceModal')">إضافة خدمة جديدة</button>
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
                                <button
                                    class="ghost"
                                    type="button"
                                    data-service="{{ json_encode([
                                        'id' => $service->id,
                                        'title' => $service->title,
                                        'description' => $service->description,
                                        'icon' => $service->icon,
                                        'workflow_type' => $service->workflow_type,
                                    ], JSON_UNESCAPED_UNICODE) }}"
                                    onclick="openServiceEditor(JSON.parse(this.dataset.service))"
                                >تعديل</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="modal-backdrop" id="addServiceModal" onclick="hideServiceModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-head">
                <h2>إضافة خدمة جديدة</h2>
                <button class="modal-close" type="button" onclick="hideServiceModal()">إغلاق</button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('admin.services.store') }}">
                    @csrf
                    @include('admin.partials.service-fields')
                    <button class="save" type="submit">إضافة الخدمة</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="editServiceModal" onclick="hideServiceModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-head">
                <h2>تعديل الخدمة</h2>
                <button class="modal-close" type="button" onclick="hideServiceModal()">إغلاق</button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm" method="post" action="">
                    @csrf
                    @method('patch')
                    @include('admin.partials.service-fields', ['fieldPrefix' => 'edit_'])
                    <button class="save" type="submit">حفظ التعديل</button>
                </form>
            </div>
        </div>
    </div>

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
            showServiceModal('editServiceModal');
        }
    </script>
@endsection
