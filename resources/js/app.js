import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// 👇 Toggle de contraseña (vanilla JS)
window.addEventListener('DOMContentLoaded', () => {
    const pass = document.getElementById('password');
    const btn  = document.getElementById('togglePassword');
    const eyeO = document.getElementById('eyeOpen');
    const eyeC = document.getElementById('eyeClosed');

    if (pass && btn && eyeO && eyeC) {
        btn.addEventListener('click', () => {
            const show = pass.type === 'password';
            pass.type = show ? 'text' : 'password';
            eyeO.classList.toggle('hidden', show);
            eyeC.classList.toggle('hidden', !show);
            pass.focus();
        });
    }
});
