@php($fieldPrefix = $fieldPrefix ?? 'add_')
<div class="form-grid">
    <div>
        <label for="{{ $fieldPrefix }}service_title">عنوان الخدمة</label>
        <input id="{{ $fieldPrefix }}service_title" name="title" type="text" maxlength="255" required>
    </div>
    <div>
        <label for="{{ $fieldPrefix }}service_icon">رمز الخدمة</label>
        <input id="{{ $fieldPrefix }}service_icon" name="icon" type="text" maxlength="20" placeholder="مثال: 📚">
    </div>
    <div class="full">
        <label for="{{ $fieldPrefix }}service_description">وصف الخدمة</label>
        <input id="{{ $fieldPrefix }}service_description" name="description" type="text" maxlength="1000" required>
    </div>
    <div class="full">
        <label for="{{ $fieldPrefix }}service_workflow">نموذج صفحة الخدمة</label>
        <select id="{{ $fieldPrefix }}service_workflow" name="workflow_type" required>
            @foreach ($workflows as $workflow => $label)
                <option value="{{ $workflow }}">{{ $label }}</option>
            @endforeach
        </select>
        <p class="form-note">النموذج يحدد الحقول ورفع الملفات والتسعير المستخدم في الصفحة، من دون تغيير النموذج الأصلي.</p>
    </div>
</div>
