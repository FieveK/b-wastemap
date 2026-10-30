const initializeAdminNotifications = () => {
    const root = document.getElementById('admin-report-notifications');
    if (!root) return;

    const poll = async () => {
        try {
            const response = await fetch(root.dataset.url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) return;
            const { notifications = [] } = await response.json();

            notifications.forEach((notification) => {
                const toast = document.createElement('a');
                toast.href = notification.url;
                toast.className = 'block w-[min(22rem,calc(100vw-2rem))] rounded-xl border border-[#CFE8E1] bg-white p-4 text-[#0A332D] shadow-xl';
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                const title = document.createElement('strong');
                title.className = 'block';
                title.textContent = notification.title || 'Pembaruan laporan';
                const body = document.createElement('span');
                body.className = 'mt-1 block text-sm text-[#58665c]';
                body.textContent = notification.body || 'Buka antrean laporan untuk melihat pembaruan.';
                toast.append(title, body);
                root.append(toast);
                window.setTimeout(() => toast.remove(), 10000);
            });
        } catch {
            // Notification polling can retry on the next interval.
        }
    };

    poll();
    window.setInterval(poll, 20000);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminNotifications, { once: true });
} else {
    initializeAdminNotifications();
}
