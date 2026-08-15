@auth
<script>
    (() => {
        if (!window.AlwrraqBiometric || window.__alwrraqBiometricBridgeStarted) return;
        window.__alwrraqBiometricBridgeStarted = true;

        const send = (payload) => window.AlwrraqBiometric.postMessage(JSON.stringify(payload));
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        window.alwrraqIssueBiometricToken = async (device) => {
            try {
                const response = await fetch(@json(route('app.biometric.issue')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf()},
                    body: JSON.stringify(device),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'issue_failed');
                send({action: 'issued', ...data});
            } catch (_) {
                send({action: 'error', message: 'تعذر تفعيل الدخول بالبصمة. حاول مرة أخرى.'});
            }
        };

        window.alwrraqRevokeBiometricToken = async (deviceId) => {
            try {
                const response = await fetch(@json(route('app.biometric.revoke')), {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf()},
                    body: JSON.stringify({device_id: deviceId}),
                });
                if (!response.ok) throw new Error('revoke_failed');
                send({action: 'revoked'});
            } catch (_) {
                send({action: 'error', message: 'تعذر إيقاف الدخول بالبصمة. حاول مرة أخرى.'});
            }
        };

        window.alwrraqSetBiometricState = (state) => {
            const section = document.getElementById('biometricLoginSection');
            if (!section) return;
            section.hidden = !state.supported;
            section.querySelector('[data-biometric-enabled]').hidden = !state.enabled;
            section.querySelector('[data-biometric-disabled]').hidden = state.enabled;
            const label = section.querySelector('[data-biometric-label]');
            if (label && state.label) label.textContent = state.label;
        };

        send({
            action: 'session',
            authenticated: true,
            userId: {{ (int) auth()->id() }},
            userName: @json(auth()->user()->name),
        });
    })();
</script>
@endauth
