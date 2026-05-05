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

const bindPwaInstallButton = () => {
    const btn = document.getElementById("pwa-install-btn");
    if (!btn) {
        return;
    }

    const refreshVisibility = () => {
        if (deferredInstallPrompt && isPortableDevice()) {
            btn.classList.remove("hidden");
            return;
        }

        btn.classList.add("hidden");
    };

    btn.addEventListener("click", async () => {
        if (!deferredInstallPrompt) {
            return;
        }

        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        refreshVisibility();
    });

    window.addEventListener("beforeinstallprompt", (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        refreshVisibility();
    });

    refreshVisibility();
};

window.addEventListener("load", bindPwaInstallButton);
