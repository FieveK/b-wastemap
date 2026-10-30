import piexif from 'piexifjs';

const initializeReportCamera = () => {
    const startButton = document.getElementById('start-browser-camera');
    const panel = document.getElementById('browser-camera-panel');
    const video = document.getElementById('browser-camera-preview');
    const status = document.getElementById('browser-camera-status');
    const captureButton = document.getElementById('capture-browser-photo');
    const stopButton = document.getElementById('stop-browser-camera');
    const fileInput = document.getElementById('foto');

    if (!startButton || !panel || !video || !status || !captureButton || !stopButton || !fileInput) return;

    let stream = null;

    const stopCamera = () => {
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
        captureButton.disabled = true;
        panel.hidden = true;
        panel.classList.add('hidden');
        startButton.disabled = false;
    };

    const currentPosition = () => new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Browser tidak menyediakan akses lokasi.'));
            return;
        }

        navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 20000,
        });
    });

    const dms = (coordinate) => {
        let degrees = Math.floor(Math.abs(coordinate));
        const minutesFloat = (Math.abs(coordinate) - degrees) * 60;
        let minutes = Math.floor(minutesFloat);
        let seconds = Math.round((minutesFloat - minutes) * 60 * 10000);

        if (seconds >= 600000) {
            seconds = 0;
            minutes += 1;
        }
        if (minutes >= 60) {
            minutes = 0;
            degrees += 1;
        }

        return [[degrees, 1], [minutes, 1], [seconds, 10000]];
    };

    const stampPhoto = async (frame, position, capturedAt) => {
        const width = frame.width;
        const height = frame.height;
        const { latitude, longitude, accuracy, altitude } = position.coords;
        if (![latitude, longitude, accuracy].every(Number.isFinite)) {
            throw new Error('Browser tidak memberikan koordinat GPS yang valid.');
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext('2d');
        if (!context) throw new Error('Browser gagal menyiapkan foto.');
        context.drawImage(frame, 0, 0, width, height);

        const fontSize = Math.max(18, Math.round(width * 0.022));
        const lineHeight = Math.round(fontSize * 1.5);
        const padding = Math.round(fontSize * 0.75);
        const lines = [
            `GPS ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`,
            `${capturedAt.toLocaleString('id-ID')} · akurasi GPS ±${Math.round(accuracy)} m`,
        ];
        const boxHeight = lineHeight * lines.length + padding * 2;
        context.fillStyle = 'rgba(0, 0, 0, 0.72)';
        context.fillRect(0, height - boxHeight, width, boxHeight);
        context.fillStyle = '#fff';
        context.font = `600 ${fontSize}px sans-serif`;
        context.textBaseline = 'top';
        lines.forEach((line, index) => context.fillText(line, padding, height - boxHeight + padding + index * lineHeight));

        const captureTime = new Date(position.timestamp || capturedAt);
        const exifCaptureTime = `${capturedAt.getFullYear()}:${String(capturedAt.getMonth() + 1).padStart(2, '0')}:${String(capturedAt.getDate()).padStart(2, '0')} ${String(capturedAt.getHours()).padStart(2, '0')}:${String(capturedAt.getMinutes()).padStart(2, '0')}:${String(capturedAt.getSeconds()).padStart(2, '0')}`;
        const zeroth = { [piexif.ImageIFD.DateTime]: exifCaptureTime };
        const exif = { [piexif.ExifIFD.DateTimeOriginal]: exifCaptureTime };
        const gps = {
            [piexif.GPSIFD.GPSVersionID]: [2, 3, 0, 0],
            [piexif.GPSIFD.GPSLatitudeRef]: latitude < 0 ? 'S' : 'N',
            [piexif.GPSIFD.GPSLatitude]: dms(latitude),
            [piexif.GPSIFD.GPSLongitudeRef]: longitude < 0 ? 'W' : 'E',
            [piexif.GPSIFD.GPSLongitude]: dms(longitude),
            [piexif.GPSIFD.GPSMapDatum]: 'WGS-84',
            [piexif.GPSIFD.GPSDateStamp]: captureTime.toISOString().slice(0, 10).replaceAll('-', ':'),
            [piexif.GPSIFD.GPSTimeStamp]: [
                [captureTime.getUTCHours(), 1],
                [captureTime.getUTCMinutes(), 1],
                [captureTime.getUTCSeconds() * 1000 + captureTime.getUTCMilliseconds(), 1000],
            ],
            [piexif.GPSIFD.GPSHPositioningError]: [Math.max(1, Math.round(accuracy * 100)), 100],
        };

        if (Number.isFinite(altitude)) {
            gps[piexif.GPSIFD.GPSAltitudeRef] = altitude < 0 ? 1 : 0;
            gps[piexif.GPSIFD.GPSAltitude] = [Math.round(Math.abs(altitude) * 100), 100];
        }

        let quality = 0.92;
        let jpeg = canvas.toDataURL('image/jpeg', quality);
        while (jpeg.length * 0.75 > 10 * 1024 * 1024 && quality > 0.72) {
            quality -= 0.05;
            jpeg = canvas.toDataURL('image/jpeg', quality);
        }
        const withExif = piexif.insert(piexif.dump({ '0th': zeroth, Exif: exif, GPS: gps }), jpeg);
        const blob = await (await fetch(withExif)).blob();
        if (blob.size > 10 * 1024 * 1024) {
            throw new Error('Foto melebihi batas 10 MB. Turunkan resolusi kamera atau ambil ulang.');
        }

        return blob;
    };

    startButton.addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            status.textContent = 'Kamera tertanam membutuhkan browser yang mendukung kamera dan alamat HTTPS (localhost boleh).';
            panel.hidden = false;
            panel.classList.remove('hidden');
            return;
        }

        startButton.disabled = true;
        panel.hidden = false;
        panel.classList.remove('hidden');
        status.textContent = 'Meminta izin kamera. Izinkan akses kamera pada browser.';

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 3840 }, height: { ideal: 2160 } },
            });
            video.srcObject = stream;
            await video.play();
            captureButton.disabled = false;
            status.textContent = 'Kamera siap. Tekan Ambil foto; browser akan meminta izin lokasi GPS.';
        } catch (error) {
            stopCamera();
            status.textContent = error.name === 'NotAllowedError'
                ? 'Izin kamera ditolak. Izinkan kamera untuk website ini melalui pengaturan browser.'
                : 'Kamera tidak dapat dibuka. Pastikan perangkat memiliki kamera dan halaman memakai HTTPS.';
            panel.hidden = false;
            panel.classList.remove('hidden');
        }
    });

    captureButton.addEventListener('click', async () => {
        captureButton.disabled = true;
        const capturedAt = new Date();
        const frame = document.createElement('canvas');
        frame.width = video.videoWidth;
        frame.height = video.videoHeight;

        if (!frame.width || !frame.height) {
            status.textContent = 'Pratinjau kamera belum siap. Coba lagi.';
            captureButton.disabled = !stream;
            return;
        }

        const frameContext = frame.getContext('2d');
        if (!frameContext) {
            status.textContent = 'Browser gagal menyiapkan foto.';
            captureButton.disabled = !stream;
            return;
        }
        frameContext.drawImage(video, 0, 0, frame.width, frame.height);
        status.textContent = 'Foto diambil. Menunggu koordinat GPS akurat…';

        try {
            const position = await currentPosition();
            const blob = await stampPhoto(frame, position, capturedAt);
            const photo = new File([blob], `bwastemap-${Date.now()}.jpg`, {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });
            const transfer = new DataTransfer();
            transfer.items.add(photo);
            fileInput.files = transfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            status.textContent = `Foto GPS siap. Koordinat ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)} ditempel pada foto dan metadata EXIF. Periksa foto sebelum lanjut.`;
        } catch (error) {
            status.textContent = error.code === 1
                ? 'Izin lokasi ditolak. Izinkan lokasi untuk website ini, lalu coba lagi.'
                : (error.message || 'Foto gagal dibuat. Coba lagi.');
        } finally {
            captureButton.disabled = !stream;
        }
    });

    stopButton.addEventListener('click', stopCamera);
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) stopCamera();
    });
    window.addEventListener('pagehide', stopCamera);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeReportCamera, { once: true });
} else {
    initializeReportCamera();
}
