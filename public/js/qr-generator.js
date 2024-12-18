import QRCode from 'qrcode';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('qr-form');
    const input = document.getElementById('qr-input');
    const qrImage = document.getElementById('qr-image');

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const text = input.value;
        if (text) {
            QRCode.toDataURL(text, { width: 200 })
                .then(url => {
                    qrImage.src = url;
                    qrImage.style.display = 'block';
                })
                .catch(err => {
                    console.error('Error generando el código QR', err);
                });
        }
    });
});
