import "./bootstrap";
import Alpine from "alpinejs";
import "glightbox/dist/css/glightbox.min.css";
import GLightbox from "glightbox";

import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";

window.GLightbox = GLightbox;

window.Alpine = Alpine;
Alpine.start();

function loadSortableIfNeeded() {
    if (document.querySelector("[data-reorder-url]")) {
        import("./sortable");
    }
}

function loadProjectCommentsIfNeeded() {
    if (document.getElementById("comments-list")) {
        import("./projects/comments");
    }
}

if (document.readyState === "loading") {
    document.addEventListener(
        "DOMContentLoaded",
        () => {
            loadSortableIfNeeded();
            loadProjectCommentsIfNeeded();
        },
        {
            once: true,
        }
    );
} else {
    loadSortableIfNeeded();
    loadProjectCommentsIfNeeded();
}

// Инициализация (в конце файла)
document.addEventListener("DOMContentLoaded", () => {
    tippy("[data-tippy]", {
        allowHTML: true,
        theme: "light",
        animation: "shift-away",
    });
});
