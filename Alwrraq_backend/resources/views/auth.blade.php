<!DOCTYPE html>
<html lang="{{ session('ui_locale', 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ session('ui_locale', 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الدخول - الورّاق</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; display: grid; place-items: center; padding: clamp(14px, 4vw, 24px); }
        .auth-card { width: min(430px, 100%); background: #ffffff; border: 1px solid #e5e7eb; border-radius: clamp(12px, 3vw, 14px); padding: clamp(18px, 4vw, 26px); box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10); }
        .web-back { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-bottom: 16px; padding: 11px 14px; border-radius: 12px; background: linear-gradient(135deg, #0f4c81, #1d6fa5); color: #fff; text-decoration: none; font-size: 14px; font-weight: 900; border: 1px solid rgba(96, 165, 250, 0.35); box-shadow: 0 12px 24px rgba(15, 76, 129, 0.18); transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease; }
        .web-back:hover { background: linear-gradient(135deg, #123f68, #0f4c81); transform: translateY(-1px); box-shadow: 0 14px 28px rgba(15, 76, 129, 0.24); }
        .brand { margin-bottom: 22px; text-align: center; }
        .brand-logo { width: 92px; height: 92px; display: block; margin: 0 auto 12px; border-radius: 22px; object-fit: cover; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12); }
        h1 { margin: 0; font-size: clamp(24px, 7vw, 28px); }
        h2 { margin: 0 0 6px; font-size: clamp(20px, 5vw, 22px); }
        p { margin: 0; color: #64748b; line-height: 1.7; }
        label { display: block; margin: 14px 0 6px; font-weight: 700; font-size: 13px; color: #334155; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 16px; }
        button { width: 100%; margin-top: 18px; padding: 12px 16px; border: 0; border-radius: 9px; background: #0f172a; color: #ffffff; font-weight: 800; cursor: pointer; }
        .secondary-action { margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; }
        .switch-button { width: auto; margin-top: 10px; padding: 9px 13px; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .error { margin: 0 0 16px; padding: 12px; background: #fef2f2; color: #b91c1c; border-radius: 8px; }
        .input-note { margin: 7px 0 0; color: #64748b; font-size: 11px; font-weight: 800; line-height: 1.6; }
        .english-number-warning { display: none; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 800; }
        .english-number-warning.active { display: block; }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .form-grid label { margin-top: 14px; }
        .optional-note { margin-top: 14px; padding: 10px 12px; border-radius: 9px; background: #eff6ff; color: #0f4c81; font-size: 12px; font-weight: 800; line-height: 1.7; }
        .check-row { display: flex; align-items: center; gap: 8px; margin-top: 10px; color: #334155; font-size: 13px; font-weight: 800; }
        .check-row input { width: auto; }
        .institution-box.disabled { opacity: 0.45; pointer-events: none; }
        .institution-results { display: none; margin-top: 8px; border: 1px solid #cbd5e1; border-radius: 9px; background: #ffffff; overflow: hidden; max-height: 210px; overflow-y: auto; }
        .institution-results.active { display: block; }
        .institution-result { width: 100%; margin: 0; padding: 11px 12px; border: 0; border-bottom: 1px solid #e5e7eb; border-radius: 0; background: #ffffff; color: #111827; text-align: right; font-size: 13px; font-weight: 800; cursor: pointer; }
        .institution-result:hover { background: #f8fafc; }
        .institution-result:last-child { border-bottom: 0; }
        .institution-meta { display: block; margin-top: 4px; color: #64748b; font-size: 11px; font-weight: 700; }
        .institution-empty { padding: 11px 12px; color: #64748b; font-size: 12px; font-weight: 800; line-height: 1.7; }
        .institution-dropdown { display: none; margin-top: 8px; }
        .institution-dropdown.active { display: block; }
        .institution-dropdown-search { margin: 0 0 8px; }
        .institution-saved { background: #f8fafc; }
        #institutionSearch,
        #institutionName { -webkit-appearance: none; appearance: none; background-image: none !important; }
        #institutionSearch::-webkit-search-decoration,
        #institutionSearch::-webkit-search-cancel-button,
        #institutionSearch::-webkit-search-results-button,
        #institutionSearch::-webkit-search-results-decoration,
        #institutionName::-webkit-contacts-auto-fill-button,
        #institutionName::-webkit-credentials-auto-fill-button,
        #institutionName::-webkit-calendar-picker-indicator,
        #institutionSearch::-webkit-contacts-auto-fill-button,
        #institutionSearch::-webkit-credentials-auto-fill-button,
        #institutionSearch::-webkit-calendar-picker-indicator { display: none !important; visibility: hidden; pointer-events: none; opacity: 0; }
        .institution-label-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 14px; }
        .institution-label-row label { margin: 0; }
        .institution-manual-note { color: #64748b; font-size: 11px; font-weight: 800; line-height: 1.5; text-align: left; }
        .institution-save-row { display: none; align-items: center; gap: 8px; margin-top: 8px; }
        .institution-save-row.active { display: flex; }
        .institution-save-row button { width: auto; margin-top: 0; padding: 9px 12px; font-size: 12px; }
        .institution-save-status { color: #047857; font-size: 12px; font-weight: 800; }
        @media (max-width: 520px) {
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .institution-label-row { align-items: flex-start; flex-direction: column; }
            .institution-manual-note { text-align: right; }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="auth-card">
            <a class="web-back" href="{{ route('public.home') }}"><span aria-hidden="true">←</span><span>الصفحة الرئيسية</span></a>
            @include('shared.language-switcher')
            <div class="brand">
                <img class="brand-logo" src="{{ asset('images/alwrraq-logo.jpeg') }}" alt="شعار الورّاق">
                <h1>الورّاق</h1>
                <p>خدمات الطباعة والتجليد</p>
            </div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div id="loginPanel" class="auth-panel active">
                <h2>تسجيل الدخول</h2>
                <p>ادخل رقم جوالك أو بريدك الإلكتروني وكلمة المرور للمتابعة.</p>

                <form method="post" action="{{ route('login.store') }}">
                    @csrf
                    <label for="loginIdentifier">رقم الجوال أو البريد الإلكتروني</label>
                    <input id="loginIdentifier" name="login_identifier" value="{{ old('login_identifier') }}" autocomplete="username" required>
                    <p class="input-note">يقبل رقم الجوال أو البريد بحروف وأرقام إنجليزية ورموز البريد فقط، والأرقام العربية تتحول تلقائيًا إلى إنجليزية.</p>

                    <label for="loginPassword">كلمة المرور</label>
                    <input id="loginPassword" name="password" type="password" autocomplete="current-password" required>
                    <p class="input-note">كلمة المرور تقبل الحروف الإنجليزية والأرقام والرموز فقط.</p>

                    <button type="submit">دخول</button>
                </form>

                <div class="secondary-action">
                    <p>ليس لديك حساب؟</p>
                    <button class="switch-button" type="button" onclick="showPanel('register')">إنشاء حساب جديد</button>
                </div>
            </div>

            <div id="registerPanel" class="auth-panel">
                <h2>إنشاء حساب</h2>

                <form method="post" action="{{ route('register.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div>
                            <label for="firstName">الاسم الأول</label>
                            <input id="firstName" name="first_name" value="{{ old('first_name') }}" required>
                        </div>
                        <div>
                            <label for="secondName">الاسم الثاني</label>
                            <input id="secondName" name="second_name" value="{{ old('second_name') }}" required>
                        </div>
                    </div>

                    <label for="phone">رقم الجوال</label>
                    <input id="phone" name="phone" inputmode="numeric" autocomplete="tel" required>
                    <p class="input-note">اكتب الرقم بأي صيغة أرقام عربية أو إنجليزية، وسيتم تحويله تلقائيًا إلى أرقام إنجليزية.</p>

                    <label for="email">البريد الإلكتروني (اختياري)</label>
                    <input id="email" name="email" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" title="اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com">
                    <p class="input-note">البريد يقبل الحروف الإنجليزية والأرقام والرموز الخاصة بالبريد فقط.</p>

                    <div class="optional-note">اختياري: اختر جامعتك أو معهدك أو مدرستك من القائمة، وإذا لم تجدها اكتب اسمها كاملًا يدويًا وسيتم حفظه. إذا ما تبغى تضيفها اختر غير مهتم.</div>
                    <label class="check-row" for="institutionNotInterested">
                        <input id="institutionNotInterested" name="institution_not_interested" type="checkbox" value="1" @checked(old('institution_not_interested'))>
                        <span>غير مهتم</span>
                    </label>
                    <div id="institutionBox" class="institution-box">
                        <label for="institutionToggle">جامعتك / معهدك / مدرستك</label>
                        <button id="institutionToggle" type="button">اختيار من القائمة</button>
                        <div id="institutionDropdown" class="institution-dropdown">
                            <input id="institutionSearch" class="institution-dropdown-search" type="text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" inputmode="text" role="combobox" placeholder="ابحث داخل القائمة">
                            <div id="institutionResults" class="institution-results" aria-live="polite"></div>
                        </div>

                        <div class="institution-label-row">
                            <label for="institutionName">الجهة المختارة</label>
                            <span class="institution-manual-note">إذا ما حصلت جامعتك / معهدك / مدرستك ادخلها يدويًا</span>
                        </div>
                        <input id="institutionName" class="institution-saved" name="institution_name" value="{{ old('institution_name') }}" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" inputmode="text" placeholder="اختر من القائمة أو اكتب اسم الجهة هنا">
                        <div id="institutionSaveRow" class="institution-save-row">
                            <button id="institutionSaveManual" type="button">احفظ الجهة التعليمية</button>
                            <span id="institutionSaveStatus" class="institution-save-status"></span>
                        </div>
                    </div>

                    <label for="password">كلمة المرور</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    <p class="input-note">كلمة المرور تقبل الحروف الإنجليزية والأرقام والرموز فقط.</p>

                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

                    <button type="submit">إنشاء الحساب</button>
                </form>

                <div class="secondary-action">
                    <p>لديك حساب بالفعل؟</p>
                    <button class="switch-button" type="button" onclick="showPanel('login')">العودة لتسجيل الدخول</button>
                </div>
            </div>
        </section>
    </main>

    <script>
        function showPanel(panel) {
            document.getElementById('loginPanel').classList.toggle('active', panel === 'login');
            document.getElementById('registerPanel').classList.toggle('active', panel === 'register');
        }

        if (window.location.hash === '#register') {
            showPanel('register');
        }

        function convertArabicDigits(value) {
            return value
                .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)))
                .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)));
        }

        function bindInputRule(input, pattern, message, sanitizer = null) {
            const showWarning = () => {
                if (sanitizer) {
                    const cursor = input.selectionStart;
                    const oldLength = input.value.length;
                    input.value = sanitizer(input.value);
                    const diff = oldLength - input.value.length;
                    if (document.activeElement === input && cursor !== null) {
                        input.setSelectionRange(Math.max(0, cursor - diff), Math.max(0, cursor - diff));
                    }
                }

                let warning = input.nextElementSibling;
                while (warning && warning.classList.contains('input-note')) {
                    warning = warning.nextElementSibling;
                }
                if (!warning || !warning.classList.contains('english-number-warning')) {
                    warning = document.createElement('div');
                    warning.className = 'english-number-warning';
                    let insertAfter = input.nextElementSibling;
                    while (insertAfter && insertAfter.classList.contains('input-note')) {
                        input.parentNode.insertBefore(warning, insertAfter.nextElementSibling);
                        insertAfter = null;
                    }
                    if (insertAfter !== null) {
                        input.insertAdjacentElement('afterend', warning);
                    }
                }

                const invalid = input.value !== '' && !pattern.test(input.value);
                warning.textContent = message;
                warning.classList.toggle('active', invalid);
                input.setCustomValidity(invalid ? message : '');
            };

            input.addEventListener('input', showWarning);
            showWarning();
        }

        const asciiPrintable = (value) => convertArabicDigits(value).replace(/[^\x21-\x7E]/g, '');
        const phoneOnly = (value) => convertArabicDigits(value).replace(/[^0-9]/g, '').slice(0, 10);
        const emailOnly = (value) => convertArabicDigits(value).replace(/[^A-Za-z0-9._%+\-@]/g, '');

        document.querySelectorAll('input[name="login_identifier"]').forEach((input) => {
            bindInputRule(input, /^[\x21-\x7E]+$/, 'تنبيه: تسجيل الدخول يقبل الحروف الإنجليزية والأرقام والرموز فقط.', asciiPrintable);
        });

        document.querySelectorAll('#registerPanel input[name="phone"]').forEach((input) => {
            bindInputRule(input, /^05[0-9]{8}$/, 'تنبيه: رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام إنجليزية فقط.', phoneOnly);
        });

        document.querySelectorAll('#registerPanel input[name="email"]').forEach((input) => {
            bindInputRule(input, /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/, 'تنبيه: اكتب بريدًا إلكترونيًا صحيحًا مثل name@example.com.', emailOnly);
        });

        document.querySelectorAll('input[name="password"], input[name="password_confirmation"]').forEach((input) => {
            bindInputRule(input, /^[\x21-\x7E]+$/, 'تنبيه: كلمة المرور تقبل الحروف الإنجليزية والأرقام والرموز فقط.', asciiPrintable);
        });

        const institutionCheckbox = document.getElementById('institutionNotInterested');
        const institutionBox = document.getElementById('institutionBox');
        const institutionSearch = document.getElementById('institutionSearch');
        const institutionToggle = document.getElementById('institutionToggle');
        const institutionDropdown = document.getElementById('institutionDropdown');
        const institutionInput = document.getElementById('institutionName');
        const institutionResults = document.getElementById('institutionResults');
        const institutionSaveRow = document.getElementById('institutionSaveRow');
        const institutionSaveManual = document.getElementById('institutionSaveManual');
        const institutionSaveStatus = document.getElementById('institutionSaveStatus');
        const institutionSearchUrl = @json(route('educational-institutions.index'));
        let institutionSearchTimer = null;
        let institutionSearchController = null;
        let selectedInstitutionName = institutionInput.value.trim();

        function syncInstitutionInterest() {
            const notInterested = institutionCheckbox.checked;
            institutionBox.classList.toggle('disabled', notInterested);
            institutionSearch.disabled = notInterested;
            institutionToggle.disabled = notInterested;
            institutionInput.disabled = notInterested;
            institutionInput.required = !notInterested;

            if (notInterested) {
                institutionSearch.value = '';
                institutionInput.value = '';
                institutionInput.setCustomValidity('');
                selectedInstitutionName = '';
                institutionSaveStatus.textContent = '';
                institutionSaveRow.classList.remove('active');
                institutionDropdown.classList.remove('active');
                institutionResults.classList.remove('active');
            }
        }

        function syncManualInstitutionSave() {
            const currentValue = institutionInput.value.trim();
            const needsSave = currentValue !== '' && currentValue !== selectedInstitutionName;
            institutionSaveRow.classList.toggle('active', needsSave);

            if (needsSave) {
                institutionSaveStatus.textContent = '';
            }
        }

        function debounceInstitutionSearch() {
            clearTimeout(institutionSearchTimer);
            institutionSearchTimer = setTimeout(loadInstitutionOptions, 250);
        }

        function loadInstitutionOptions() {
            if (institutionInput.disabled) {
                return;
            }

            if (institutionSearchController) {
                institutionSearchController.abort();
            }

            institutionSearchController = new AbortController();

            const url = new URL(institutionSearchUrl, window.location.origin);
            url.searchParams.set('q', institutionSearch.value.trim());
            url.searchParams.set('per_page', '100');

            fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: institutionSearchController.signal,
            })
                .then((response) => response.ok ? response.json() : Promise.reject())
                .then((payload) => {
                    institutionResults.innerHTML = '';

                    if (payload.data.length === 0) {
                        institutionResults.innerHTML = '';

                        const empty = document.createElement('div');
                        empty.className = 'institution-empty';
                        empty.textContent = 'ما لقينا نتيجة مطابقة. تقدر تحفظ الاسم اللي كتبته.';
                        institutionResults.appendChild(empty);

                        if (institutionSearch.value.trim() !== '') {
                            const manual = document.createElement('button');
                            manual.type = 'button';
                            manual.className = 'institution-result';
                            manual.textContent = `استخدام: ${institutionSearch.value.trim()}`;
                            manual.addEventListener('click', () => {
                                institutionInput.value = institutionSearch.value.trim();
                                selectedInstitutionName = institutionInput.value.trim();
                                institutionInput.setCustomValidity('');
                                institutionSaveStatus.textContent = 'تم حفظ الجهة التعليمية';
                                institutionSaveRow.classList.remove('active');
                                institutionDropdown.classList.remove('active');
                                institutionResults.classList.remove('active');
                            });
                            institutionResults.appendChild(manual);
                        }

                        institutionResults.classList.add('active');
                        return;
                    }

                    payload.data.forEach((institution) => {
                        const result = document.createElement('button');
                        result.type = 'button';
                        result.className = 'institution-result';
                        result.textContent = institution.name_ar;

                        const meta = document.createElement('span');
                        meta.className = 'institution-meta';
                        meta.textContent = [institution.city, institution.region, institution.institution_type].filter(Boolean).join(' - ');
                        result.appendChild(meta);

                        result.addEventListener('click', () => {
                            institutionInput.value = institution.name_ar;
                            institutionSearch.value = institution.name_ar;
                            selectedInstitutionName = institution.name_ar;
                            institutionInput.setCustomValidity('');
                            institutionSaveStatus.textContent = '';
                            institutionSaveRow.classList.remove('active');
                            institutionDropdown.classList.remove('active');
                            institutionResults.classList.remove('active');
                        });
                        institutionResults.appendChild(result);
                    });

                    institutionResults.classList.add('active');
                })
                .catch((error) => {
                    if (! error || error.name !== 'AbortError') {
                        institutionResults.innerHTML = '';
                        institutionResults.classList.remove('active');
                    }
                });
        }

        institutionCheckbox.addEventListener('change', syncInstitutionInterest);
        institutionToggle.addEventListener('click', () => {
            const isOpen = institutionDropdown.classList.toggle('active');

            if (isOpen) {
                institutionSearch.value = '';
                institutionResults.classList.add('active');
                loadInstitutionOptions();
                institutionSearch.focus();
            } else {
                institutionResults.classList.remove('active');
            }
        });
        institutionSearch.addEventListener('input', debounceInstitutionSearch);
        institutionInput.addEventListener('input', syncManualInstitutionSave);
        institutionSaveManual.addEventListener('click', () => {
            selectedInstitutionName = institutionInput.value.trim();
            institutionInput.value = selectedInstitutionName;
            institutionInput.setCustomValidity('');
            institutionSaveStatus.textContent = selectedInstitutionName === '' ? '' : 'تم حفظ الجهة التعليمية';
            institutionSaveRow.classList.remove('active');
        });
        document.addEventListener('click', (event) => {
            if (! institutionBox.contains(event.target)) {
                institutionDropdown.classList.remove('active');
                institutionResults.classList.remove('active');
            }
        });
        syncInstitutionInterest();
        syncManualInstitutionSave();
    </script>
    @include('shared.language-tools')
</body>
</html>
