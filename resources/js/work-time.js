(function () {
    const container = document.getElementById("work-time-widget");
    if (!container) return;

    const routes = {
        state: container.dataset.stateUrl,
        startDay: container.dataset.startDayUrl,
        startBreak: container.dataset.startBreakUrl,
        endBreak: container.dataset.endBreakUrl,
        saveReport: container.dataset.saveReportUrl,
        endDay: container.dataset.endDayUrl,
        editDayEnd: container.dataset.editDayUrl,
        addBreak: container.dataset.addBreakUrl,
        updateBreak: container.dataset.updateBreakUrl,
        deleteBreak: container.dataset.deleteBreakUrl,
    };

    const csrf =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";

    const statusEl = document.getElementById("wt-status");
    const workTimeEl = document.getElementById("wt-work-time");
    const breakTimeEl = document.getElementById("wt-break-time");

    const btnStart = document.getElementById("wt-btn-start");
    const btnPause = document.getElementById("wt-btn-pause");
    const btnResume = document.getElementById("wt-btn-resume");
    const btnEnd = document.getElementById("wt-btn-end");
    const btnEdit = document.getElementById("wt-btn-edit");

    const endModal = document.getElementById("wt-end-modal");
    const endAtInput = document.getElementById("wt-end-at");
    const endReportInput = document.getElementById("wt-end-report");
    const saveReportBtn = document.getElementById("wt-save-report");
    const endSaveBtn = document.getElementById("wt-end-save");

    const confirmModal = document.getElementById("wt-confirm-modal");
    const confirmYesBtn = document.getElementById("wt-confirm-yes");
    const confirmCloseBtns = document.querySelectorAll(
        '[data-wt-close-confirm="1"]',
    );

    const editModal = document.getElementById("wt-edit-modal");
    const editDayEndedAtInput = document.getElementById("wt-edit-day-ended-at");
    const editDayCommentInput = document.getElementById("wt-edit-day-comment");
    const editDaySaveBtn = document.getElementById("wt-edit-day-save");

    const breaksList = document.getElementById("wt-breaks-list");
    const addBreakStart = document.getElementById("wt-add-break-start");
    const addBreakEnd = document.getElementById("wt-add-break-end");
    const addBreakComment = document.getElementById("wt-add-break-comment");
    const addBreakSaveBtn = document.getElementById("wt-add-break-save");

    const editsList = document.getElementById("wt-edits-list");

    let currentState = null;
    let workSeconds = 0;
    let breakSeconds = 0;

    function formatSeconds(seconds) {
        const total = Math.max(0, Math.floor(Number(seconds || 0)));
        const h = String(Math.floor(total / 3600)).padStart(2, "0");
        const m = String(Math.floor((total % 3600) / 60)).padStart(2, "0");
        const s = String(total % 60).padStart(2, "0");

        return `${h}:${m}:${s}`;
    }

    function setButtonVisibility(mode, hasDay) {
        btnStart.classList.add("hidden");
        btnPause.classList.add("hidden");
        btnResume.classList.add("hidden");
        btnEnd.classList.add("hidden");
        btnEdit.classList.add("hidden");

        if (!hasDay || mode === "idle") {
            btnStart.classList.remove("hidden");
            return;
        }

        btnEnd.classList.remove("hidden");
        btnEdit.classList.remove("hidden");

        if (mode === "working") {
            btnPause.classList.remove("hidden");
        } else if (mode === "paused") {
            btnResume.classList.remove("hidden");
        } else {
            btnStart.classList.remove("hidden");
        }
    }

    function renderBreaks() {
        const items = currentState?.breaks || [];
        breaksList.innerHTML = "";

        if (!items.length) {
            breaksList.innerHTML =
                '<div class="text-sm text-gray-500">Паузы отсутствуют.</div>';
            return;
        }

        items.forEach((item) => {
            const row = document.createElement("div");
            row.className = "rounded border p-3";
            row.dataset.id = String(item.id);
            row.innerHTML = `
                <div class="grid gap-2 md:grid-cols-4">
                    <input type="datetime-local" data-field="started_at" class="rounded border-gray-300 text-sm" value="${item.started_at || ""}">
                    <input type="datetime-local" data-field="ended_at" class="rounded border-gray-300 text-sm" value="${item.ended_at || ""}">
                    <input type="text" data-field="comment" class="rounded border-gray-300 text-sm" placeholder="Комментарий к изменению">
                    <div class="flex gap-2">
                        <button type="button" data-action="save" class="rounded bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Сохранить</button>
                        <button type="button" data-action="delete" class="rounded bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500">Удалить</button>
                    </div>
                </div>
                <div class="mt-1 text-xs text-gray-500">Минут: ${item.minutes ?? "—"}</div>
            `;
            breaksList.appendChild(row);
        });
    }

    function renderEdits() {
        const edits = currentState?.edits || [];
        editsList.innerHTML = "";

        if (!edits.length) {
            editsList.innerHTML =
                '<div class="text-sm text-gray-500">Изменений пока нет.</div>';
            return;
        }

        edits.forEach((item) => {
            const row = document.createElement("div");
            row.className = "rounded bg-gray-50 p-2";
            row.innerHTML = `<span class="font-semibold">${item.type}</span> · ${item.created_at}<br>${item.comment}`;
            editsList.appendChild(row);
        });
    }

    function renderState(state) {
        currentState = state;
        workSeconds = Number(state?.work_seconds || 0);
        breakSeconds = Number(state?.break_seconds || 0);

        workTimeEl.textContent = formatSeconds(workSeconds);
        breakTimeEl.textContent = formatSeconds(breakSeconds);

        const mode = state?.mode || "idle";
        if (mode === "working") {
            statusEl.textContent = "В работе";
            statusEl.className = "mt-1 text-sm font-semibold text-emerald-300";
        } else if (mode === "paused") {
            statusEl.textContent = "На паузе";
            statusEl.className = "mt-1 text-sm font-semibold text-amber-300";
        } else if (state?.work_day) {
            statusEl.textContent = "День открыт";
            statusEl.className = "mt-1 text-sm font-semibold text-indigo-300";
        } else {
            statusEl.textContent = "Не начат";
            statusEl.className = "mt-1 text-sm font-semibold text-white/80";
        }

        setButtonVisibility(mode, Boolean(state?.work_day));

        endAtInput.value = state?.work_day?.suggested_end_at || "";
        endReportInput.value = state?.work_day?.report || "";

        editDayEndedAtInput.value = state?.work_day?.suggested_end_at || "";
        renderBreaks();
        renderEdits();
    }

    async function apiCall(url, method = "GET", payload = null) {
        const response = await fetch(url, {
            method,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrf,
            },
            body: payload ? JSON.stringify(payload) : null,
        });

        const json = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = json?.message || "Не удалось выполнить действие.";
            throw new Error(message);
        }

        return json;
    }

    async function refreshState() {
        try {
            const state = await apiCall(routes.state);
            renderState(state);
        } catch (e) {
            console.error(e);
        }
    }

    function openEndModal() {
        if (!currentState?.work_day) return;
        endAtInput.value = currentState.work_day.suggested_end_at || "";
        endModal.classList.remove("hidden");
    }

    function closeEndModal() {
        endModal.classList.add("hidden");
    }

    function openEditModal() {
        if (!currentState?.work_day) return;
        editModal.classList.remove("hidden");
        renderBreaks();
        renderEdits();
    }

    function closeEditModal() {
        editModal.classList.add("hidden");
    }

    function openConfirmModal() {
        confirmModal.classList.remove("hidden");
    }

    function closeConfirmModal() {
        confirmModal.classList.add("hidden");
    }

    // Wire close buttons for confirm modal
    confirmCloseBtns.forEach((btn) =>
        btn.addEventListener("click", closeConfirmModal),
    );

    // Actual end-day action (triggered from confirm 'Yes')
    async function performEndDay() {
        if (!endReportInput.value.trim()) {
            alert("Заполните, что сделано за день.");
            return;
        }

        try {
            confirmYesBtn.disabled = true;
            const state = await apiCall(routes.endDay, "POST", {
                ended_at: endAtInput.value,
                report: endReportInput.value.trim(),
            });
            closeConfirmModal();
            closeEndModal();
            renderState(state);
        } catch (e) {
            alert(e.message);
        } finally {
            confirmYesBtn.disabled = false;
        }
    }

    confirmYesBtn.addEventListener("click", performEndDay);

    btnStart.addEventListener("click", async () => {
        try {
            const state = await apiCall(routes.startDay, "POST");
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    btnPause.addEventListener("click", async () => {
        try {
            const state = await apiCall(routes.startBreak, "POST");
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    btnResume.addEventListener("click", async () => {
        try {
            const state = await apiCall(routes.endBreak, "POST");
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    btnEnd.addEventListener("click", openEndModal);
    btnEdit.addEventListener("click", openEditModal);

    document.querySelectorAll('[data-wt-close-end="1"]').forEach((btn) => {
        btn.addEventListener("click", closeEndModal);
    });

    document.querySelectorAll('[data-wt-close-edit="1"]').forEach((btn) => {
        btn.addEventListener("click", closeEditModal);
    });

    endSaveBtn.addEventListener("click", () => {
        if (!endReportInput.value.trim()) {
            alert("Заполните, что сделано за день.");
            return;
        }

        // show custom confirmation modal (replaces browser confirm)
        openConfirmModal();
    });

    saveReportBtn.addEventListener("click", async () => {
        if (!endReportInput.value.trim()) {
            alert("Заполните, что сделано за день.");
            return;
        }

        try {
            const state = await apiCall(routes.saveReport, "POST", {
                report: endReportInput.value.trim(),
            });
            renderState(state);
            alert("Информация сохранена");
        } catch (e) {
            alert(e.message);
        }
    });

    editDaySaveBtn.addEventListener("click", async () => {
        if (!currentState?.work_day?.id) return;

        const comment = editDayCommentInput.value.trim();
        if (!comment) {
            alert("Добавьте комментарий к изменению.");
            return;
        }

        try {
            const state = await apiCall(
                `${routes.editDayEnd}/${currentState.work_day.id}/end-time`,
                "PATCH",
                {
                    ended_at: editDayEndedAtInput.value,
                    comment,
                },
            );
            editDayCommentInput.value = "";
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    addBreakSaveBtn.addEventListener("click", async () => {
        if (!currentState?.work_day?.id) return;

        const comment = addBreakComment.value.trim();
        if (!comment) {
            alert("Добавьте комментарий для новой паузы.");
            return;
        }

        try {
            const state = await apiCall(
                `${routes.addBreak}/${currentState.work_day.id}/breaks`,
                "POST",
                {
                    started_at: addBreakStart.value,
                    ended_at: addBreakEnd.value,
                    comment,
                },
            );
            addBreakStart.value = "";
            addBreakEnd.value = "";
            addBreakComment.value = "";
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    breaksList.addEventListener("click", async (event) => {
        const actionBtn = event.target.closest("button[data-action]");
        if (!actionBtn) return;

        const row = event.target.closest("[data-id]");
        if (!row) return;
        const breakId = row.dataset.id;
        const startedAt =
            row.querySelector('[data-field="started_at"]')?.value || "";
        const endedAt =
            row.querySelector('[data-field="ended_at"]')?.value || "";
        const comment = (
            row.querySelector('[data-field="comment"]')?.value || ""
        ).trim();

        if (!comment) {
            alert("Комментарий обязателен.");
            return;
        }

        try {
            let state;
            if (actionBtn.dataset.action === "save") {
                state = await apiCall(
                    `${routes.updateBreak}/${breakId}`,
                    "PATCH",
                    {
                        started_at: startedAt,
                        ended_at: endedAt,
                        comment,
                    },
                );
            } else {
                state = await apiCall(
                    `${routes.deleteBreak}/${breakId}`,
                    "DELETE",
                    {
                        comment,
                    },
                );
            }
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    setInterval(() => {
        if (!currentState) return;
        if (currentState.mode === "working") {
            workSeconds += 1;
        }
        if (currentState.mode === "paused") {
            breakSeconds += 1;
        }

        workTimeEl.textContent = formatSeconds(workSeconds);
        breakTimeEl.textContent = formatSeconds(breakSeconds);
    }, 1000);

    refreshState();
    setInterval(refreshState, 30000);
})();
