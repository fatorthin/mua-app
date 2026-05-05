// Register Service Worker for PWA
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/sw.js").catch(() => {});
    });
}

// Gunakan event yang sudah ditangkap oleh inline script di <head>
// (window.__pwaInstallEvent), karena beforeinstallprompt dapat terpicu
// sebelum module JS ini dieksekusi.
let deferredInstallPrompt = window.__pwaInstallEvent || null;

const isPortableDevice = () => {
    const ua = navigator.userAgent || "";
    return (
        /Android|iPhone|iPad|iPod|Mobile|Tablet/i.test(ua) ||
        window.matchMedia("(pointer: coarse)").matches
    );
};

const isIOS = () => /iPhone|iPad|iPod/i.test(navigator.userAgent || "");

const isStandalone = () =>
    window.matchMedia("(display-mode: standalone)").matches ||
    window.navigator.standalone === true;

// Tetap pasang listener untuk kasus event belum terpicu saat module dieksekusi
window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    window.__pwaInstallEvent = event;
    const btn = document.getElementById("pwa-install-btn");
    if (btn) {
        btn.classList.remove("hidden");
    }
});

const bindPwaInstallButton = () => {
    const btn = document.getElementById("pwa-install-btn");
    if (!btn) {
        return;
    }

    const refreshVisibility = () => {
        if (isStandalone() || !isPortableDevice()) {
            btn.classList.add("hidden");
            return;
        }

        if (deferredInstallPrompt || isIOS()) {
            btn.classList.remove("hidden");
            return;
        }

        btn.classList.add("hidden");
    };

    btn.addEventListener("click", async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            window.__pwaInstallEvent = null;
            refreshVisibility();
            return;
        }

        if (isIOS()) {
            alert(
                "Untuk iPhone/iPad: tekan tombol Share di Safari, lalu pilih 'Add to Home Screen'.",
            );
            return;
        }

        alert(
            "Install belum tersedia saat ini. Buka menu browser lalu pilih 'Install app' atau 'Add to Home screen'.",
        );
    });

    // Tampilkan tombol jika event sudah ditangkap sebelum bindPwaInstallButton dipanggil
    refreshVisibility();
};

window.addEventListener("load", bindPwaInstallButton);
