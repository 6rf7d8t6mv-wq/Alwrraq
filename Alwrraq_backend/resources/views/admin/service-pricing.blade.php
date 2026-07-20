@extends('admin.layout')

@section('title', 'أسعار الخدمات - لوحة المدير')

@section('content')
    <style>
        .pricing-page-head { align-items: center; }
        .pricing-last-update { color: #64748b; font-size: 11px; font-weight: 800; }
        .pricing-groups { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
        .pricing-group { min-width: 0; margin: 0; padding: 10px; border-inline-start: 4px solid #2563eb; }
        .pricing-group h2 { margin: 0 0 8px; color: #0f172a; font-size: 15px; }
        .pricing-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
        .pricing-field { min-width: 0; display: grid; grid-template-columns: minmax(0, 1fr) 88px; align-items: center; gap: 6px; padding: 7px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
        .pricing-field label { min-width: 0; margin: 0; color: #334155; font-size: 10px; font-weight: 900; line-height: 1.35; }
        .pricing-input-wrap { position: relative; min-width: 0; }
        .pricing-input-wrap input { min-width: 0; height: 34px; padding: 6px 7px 6px 34px; font-weight: 900; text-align: center; }
        .pricing-input-wrap span { position: absolute; left: 6px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 8px; font-weight: 900; pointer-events: none; }
        .pricing-save-row { position: sticky; bottom: 8px; z-index: 5; display: flex; justify-content: flex-start; margin-top: 10px; }
        .pricing-save-row .save { width: auto; min-width: 150px; margin: 0; box-shadow: 0 10px 24px rgba(15, 23, 42, .2); }
        @media (max-width: 980px) {
            .pricing-groups { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px; }
            .pricing-group { padding: 6px; border-inline-start-width: 3px; }
            .pricing-group h2 { margin-bottom: 5px; font-size: 10px; }
            .pricing-fields { gap: 4px; }
            .pricing-field { grid-template-columns: minmax(0, 1fr) 62px; gap: 3px; padding: 4px; border-radius: 6px; }
            .pricing-field label { font-size: 7px; line-height: 1.25; }
            .pricing-input-wrap input { height: 28px; padding: 4px 3px 4px 22px; border-radius: 6px; font-size: 16px; }
            .pricing-input-wrap span { left: 3px; font-size: 6px; }
            .pricing-last-update { font-size: 7px; }
            .pricing-save-row .save { min-width: 110px; min-height: 29px; padding: 6px 9px; font-size: 9px; }
        }
        @media (max-width: 560px) {
            .pricing-groups { grid-template-columns: 1fr; }
            .pricing-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1100px) {
            .pricing-group h2 { font-size: 18px; }
            .pricing-field label { font-size: 12px; }
            .pricing-last-update { font-size: 13px; }
        }
    </style>

    <div class="page-title compact-page-title pricing-page-head">
        <div>
            <h1>أسعار الخدمات</h1>
            @if ($lastUpdate)
                <div class="pricing-last-update">آخر تعديل: {{ $lastUpdate->updater?->name ?: 'مستخدم محذوف' }} — {{ $lastUpdate->updated_at->format('Y/m/d H:i') }}</div>
            @endif
        </div>
    </div>

    <form method="post" action="{{ route('admin.service-pricing.update') }}">
        @csrf
        @method('patch')

        <div class="pricing-groups">
            @foreach ($priceGroups as $groupName => $fields)
                <section class="panel pricing-group">
                    <h2>{{ $groupName }}</h2>
                    <div class="pricing-fields">
                        @foreach ($fields as $key => $field)
                            <div class="pricing-field">
                                <label for="price-{{ $key }}">{{ $field['label'] }}</label>
                                <div class="pricing-input-wrap">
                                    <input
                                        id="price-{{ $key }}"
                                        name="prices[{{ $key }}]"
                                        type="number"
                                        min="{{ ($field['integer'] ?? false) ? 1 : 0 }}"
                                        step="{{ ($field['integer'] ?? false) ? 1 : '0.01' }}"
                                        inputmode="decimal"
                                        value="{{ old('prices.'.$key, rtrim(rtrim(number_format($field['value'], 4, '.', ''), '0'), '.')) }}"
                                        required
                                    >
                                    <span>{{ $field['suffix'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="pricing-save-row">
            <button class="save" type="submit">حفظ جميع الأسعار</button>
        </div>
    </form>
@endsection
