import "./bootstrap";
import Alpine from "alpinejs";
import "glightbox/dist/css/glightbox.min.css";
import GLightbox from "glightbox";

import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";

import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";

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

function loadWorkTimeIfNeeded() {
    if (document.getElementById("work-time-widget")) {
        import("./work-time");
    }
}

if (document.readyState === "loading") {
    document.addEventListener(
        "DOMContentLoaded",
        () => {
            loadSortableIfNeeded();
            loadProjectCommentsIfNeeded();
            loadWorkTimeIfNeeded();
        },
        {
            once: true,
        },
    );
} else {
    loadSortableIfNeeded();
    loadProjectCommentsIfNeeded();
    loadWorkTimeIfNeeded();
}

// Функция инициализации Tom Select (для использования после AJAX-загрузки)
window.initTomSelect = function (container = document) {
    container.querySelectorAll(".js-org-select").forEach((el) => {
        if (el.tomselect) return; // уже инициализирован
        new TomSelect(el, {
            allowEmptyOption: true,
            placeholder: "Выберите организацию",
            searchField: ["text"],
            maxOptions: 100,
        });
    });

    container.querySelectorAll(".js-project-select").forEach((select) => {
        if (select.tomselect) return; // уже инициализирован
        new TomSelect(select, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc",
            },
            placeholder: "— Выберите проект —",
        });
    });

    // Для селекта маркетологов
    const marketerSelect = container.querySelector("#marketer_id");
    if (marketerSelect && !marketerSelect.tomselect) {
        new TomSelect(marketerSelect, {
            allowEmptyOption: true,
            placeholder: "Выберите маркетолога",
            searchField: ["text"],
            maxOptions: 100,
        });
    }
};

// Инициализация (в конце файла)
document.addEventListener("DOMContentLoaded", () => {
    tippy("[data-tippy]", {
        allowHTML: true,
        theme: "light",
        animation: "shift-away",
    });

    // Инициализация Tom Select при загрузке страницы
    window.initTomSelect(document);

    // --- Unread notifications badge polling (AJAX) ---
    function initUnreadBadgePolling() {
        const badge = document.getElementById("unread-notifications-badge");
        if (!badge) return;

        const fetchCount = () => {
            window.axios
                .get("/notifications/unread-count")
                .then((resp) => {
                    const n = resp?.data?.unread || 0;
                    if (n > 0) {
                        badge.textContent = n > 99 ? "99+" : String(n);
                        badge.classList.remove("hidden");
                    } else {
                        badge.classList.add("hidden");
                    }
                })
                .catch(() => {
                    // silently ignore
                });
        };

        // initial
        fetchCount();
        // poll every 10s
        setInterval(fetchCount, 10000);

        // allow manual refresh via custom event
        document.addEventListener("notifications:changed", fetchCount);
    }

    initUnreadBadgePolling();
});
