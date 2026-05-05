// Register Service Worker for PWA
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/sw.js").catch(() => {});
    });
}

let deferredInstallPrompt = null;

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

    window.addEventListener("beforeinstallprompt", (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        refreshVisibility();
    });

    refreshVisibility();
};

window.addEventListener("load", bindPwaInstallButton);
