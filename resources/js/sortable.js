import Sortable from "sortablejs";

function initSortable() {
    const toastContainer = document.querySelector('[aria-live="polite"]');

    // Инициализируем Sortable для всех списков, где указано data-reorder-url
    const lists = document.querySelectorAll("[data-reorder-url]");
    if (!lists || lists.length === 0) return;

    lists.forEach((list) => {
        // пропустить, если уже инициализировано
        if (list.__sortableInitialized) return;
        list.__sortableInitialized = true;

        const sortable = Sortable.create(list, {
            handle: ".drag-handle",
            animation: 150,
            onEnd() {
                const ids = Array.from(list.children).map(
                    (el) => el.dataset.id
                );
                saveOrder(list, ids);
            },
        });

        async function saveOrder(listEl, ids) {
            const url = listEl.dataset.reorderUrl;
            if (!url) return;
            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ order: ids }),
                });
                const data = await res.json();
                if (data?.success) {
                    showTempToast("Порядок сохранён", "success");
                    // обновляем видимые номера позиции в пределах list
                    Array.from(listEl.children).forEach((el, idx) => {
                        const badge = el.querySelector(
                            ".text-sm.text-gray-400"
                        );
                        if (badge) badge.textContent = "#" + (idx + 1);
                    });
                } else {
                    showTempToast("Не удалось сохранить порядок", "error");
                }
            } catch (e) {
                console.error(e);
                showTempToast("Ошибка сети", "error");
            }
        }
    });

    function showTempToast(text, type = "success") {
        if (!toastContainer) {
            // fallback
            alert(text);
            return;
        }
        const colors = { success: "bg-green-600", error: "bg-red-600" };
        const div = document.createElement("div");
        div.className = `max-w-sm w-full ${
            colors[type] || colors.success
        } text-white rounded shadow p-3 flex items-start gap-3`;
        div.innerHTML = `<div class="flex-1 text-sm">${text}</div><button onclick="this.parentNode.remove()" class="text-white opacity-80 hover:opacity-100 ms-2">&times;</button>`;
        toastContainer.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
}

// Инициализация: учитываем состояние загрузки документа
function ensureInit() {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initSortable, {
            once: true,
        });
    } else {
        initSortable();
    }
}

ensureInit();
