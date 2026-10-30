import QRCode from 'qrcode';

const initializeTrackingQr = async () => {
    const canvas = document.getElementById('tracking-qr');
    if (!canvas) return;

    const status = document.getElementById('tracking-qr-status');
    const download = document.getElementById('download-tracking-qr');
    const url = canvas.dataset.url;

    try {
        await QRCode.toCanvas(canvas, url, { width: 280, margin: 2, errorCorrectionLevel: 'M' });
        status.textContent = 'QR siap dipindai untuk membuka progres laporan.';
        download.disabled = false;
    } catch {
        status.textContent = 'QR gagal dibuat. Simpan token laporan untuk melacak progres.';
    }

    download?.addEventListener('click', () => {
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = `${document.getElementById('tracking-token').textContent.trim()}-QR.png`;
        link.click();
    });

    document.getElementById('copy-tracking-token')?.addEventListener('click', async () => {
        const token = document.getElementById('tracking-token').textContent.trim();
        try {
            await navigator.clipboard.writeText(token);
            document.getElementById('tracking-token-status').textContent = 'Token disalin.';
        } catch {
            document.getElementById('tracking-token-status').textContent = 'Salin token secara manual.';
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTrackingQr, { once: true });
} else {
    initializeTrackingQr();
}
