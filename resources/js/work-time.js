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
        editDayStart: container.dataset.editDayStartUrl,
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
    const editDayStartedAtInput = document.getElementById(
        "wt-edit-day-started-at",
    );
    const editDayCommentStartInput = document.getElementById(
        "wt-edit-day-comment-start",
    );
    const editDayStartSaveBtn = document.getElementById(
        "wt-edit-day-start-save",
    );
    const editDayEndedAtInput = document.getElementById("wt-edit-day-ended-at");
    const editDayCommentInput = document.getElementById("wt-edit-day-comment");
    const editDaySaveBtn = document.getElementById("wt-edit-day-save");

    const breaksList = document.getElementById("wt-breaks-list");
    const addBreakStart = document.getElementById("wt-add-break-start");
    const addBreakEnd = document.getElementById("wt-add-break-end");
    const addBreakComment = document.getElementById("wt-add-break-comment");
    const addBreakSaveBtn = document.getElementById("wt-add-break-save");

    const editsList = document.getElementById("wt-edits-list");

    // inline error node for edit-end validation
    const editDayEndError = document.getElementById("wt-edit-end-error");

    let currentState = null;
    let workSeconds = 0;
    let breakSeconds = 0;
    // remaining time until (first session started_at + 9 hours) in seconds
    let remainingSeconds = 9 * 3600;

    // track unsaved local edits for the "Что сделано за день" field
    let reportDirty = false;
    // remember previous work_day id so we can reset dirty when day changes
    let previousWorkDayId = null;

    // modal-wide dirty flag: when true, do NOT overwrite any inputs inside the Edit modal
    let modalDirty = false;

    if (endReportInput) {
        endReportInput.addEventListener("input", () => {
            reportDirty = true;
        });
    }

    // mark modal as dirty when any input inside it changes (delegated)
    if (editModal) {
        editModal.addEventListener("input", (e) => {
            modalDirty = true;
            // clear specific end-time inline error when user edits the end or start field
            if (
                (e.target === editDayEndedAtInput ||
                    e.target === editDayStartedAtInput) &&
                editDayEndError
            ) {
                editDayEndError.classList.add("hidden");
                editDayEndError.textContent = "";
            }
        });
    }

    // mark modal as dirty for dynamic breaks inputs
    if (breaksList) {
        breaksList.addEventListener("input", (e) => {
            const row = e.target.closest("[data-id]");
            if (row) modalDirty = true;
        });
    }

    // add-break inputs should also mark modal as dirty
    if (addBreakStart)
        addBreakStart.addEventListener("input", () => (modalDirty = true));
    if (addBreakEnd)
        addBreakEnd.addEventListener("input", () => (modalDirty = true));
    if (addBreakComment)
        addBreakComment.addEventListener("input", () => (modalDirty = true));

    // server polling control — keep UI smooth locally but re-sync from API frequently when active
    let serverPollId = null;
    const FAST_POLL_MS = 5000; // poll every 5s while working/paused
    const SLOW_POLL_MS = 30000; // poll every 30s when idle

    function setPollingIntervalForMode(mode) {
        if (serverPollId) {
            clearInterval(serverPollId);
            serverPollId = null;
        }
        const ms =
            mode === "working" || mode === "paused"
                ? FAST_POLL_MS
                : SLOW_POLL_MS;
        serverPollId = setInterval(refreshState, ms);
    }

    function formatSeconds(seconds) {
        const total = Math.max(0, Math.floor(Number(seconds || 0)));
        const h = String(Math.floor(total / 3600)).padStart(2, "0");
        const m = String(Math.floor((total % 3600) / 60)).padStart(2, "0");
        const s = String(total % 60).padStart(2, "0");

        return `${h}:${m}:${s}`;
    }

    function updateRemainingDisplay(sec) {
        const txt = formatSeconds(sec ?? 0);
        document
            .querySelectorAll("#wt-remaining-time")
            .forEach((el) => (el.textContent = txt));
    }

    function setButtonVisibility(mode, hasDay) {
        btnStart?.classList.add("hidden");
        btnPause?.classList.add("hidden");
        btnResume?.classList.add("hidden");
        btnEnd?.classList.add("hidden");
        btnEdit?.classList.add("hidden");

        if (!hasDay || mode === "idle") {
            btnStart?.classList.remove("hidden");
            return;
        }

        btnEnd?.classList.remove("hidden");
        btnEdit?.classList.remove("hidden");

        if (mode === "working") {
            btnPause?.classList.remove("hidden");
        } else if (mode === "paused") {
            btnResume?.classList.remove("hidden");
        } else {
            btnStart?.classList.remove("hidden");
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

        document.dispatchEvent(
            new CustomEvent("work-time:state-changed", {
                detail: {
                    mode,
                    hasWorkDay: Boolean(state?.work_day),
                },
            }),
        );

        setButtonVisibility(mode, Boolean(state?.work_day));

        // ensure server polling matches current mode (work/paused -> faster polling)
        setPollingIntervalForMode(mode);

        endAtInput.value = state?.work_day?.suggested_end_at || "";

        // reset dirty if the open work_day changed
        if (state?.work_day?.id !== previousWorkDayId) {
            reportDirty = false;
        }

        // do NOT overwrite user's unsaved input
        if (!reportDirty) {
            endReportInput.value = state?.work_day?.report || "";
        }

        const editOpen = editModal && !editModal.classList.contains("hidden");
        // when edit modal is open and user is actively editing, skip overwriting modal inputs/breaks
        if (!editOpen || !modalDirty) {
            editDayStartedAtInput &&
                (editDayStartedAtInput.value =
                    state?.work_day?.started_at || "");
            editDayEndedAtInput.value = state?.work_day?.suggested_end_at || "";
            renderBreaks();
            renderEdits();
        }

        // remaining time: use first-session start (if provided by server) + 9 hours
        const DAY_TARGET_SECONDS = 9 * 3600;
        if (state?.work_day?.started_at) {
            const startedAt = new Date(state.work_day.started_at);
            if (!isNaN(startedAt)) {
                const targetMs =
                    startedAt.getTime() + DAY_TARGET_SECONDS * 1000;
                remainingSeconds = Math.max(
                    0,
                    Math.ceil((targetMs - Date.now()) / 1000),
                );
            } else {
                remainingSeconds = DAY_TARGET_SECONDS;
            }
        } else {
            remainingSeconds = DAY_TARGET_SECONDS;
        }
        updateRemainingDisplay(remainingSeconds);

        // remember currently open work_day id
        previousWorkDayId = state?.work_day?.id || null;
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
            const err = new Error(message);
            // attach status and validation errors (if any) for callers
            err.status = response.status;
            err.errors = json?.errors || null;
            throw err;
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
        // ensure modal is fresh when opened
        modalDirty = false;
        editModal.classList.remove("hidden");
        renderBreaks();
        renderEdits();
    }

    function closeEditModal() {
        editModal.classList.add("hidden");
        // clear edit flags so polling can update modal next time
        modalDirty = false;
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
            // saved/ended the day — clear local dirty flag so server value can be applied
            reportDirty = false;
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
            // report saved — clear dirty so server value will be used
            reportDirty = false;
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
        // client-side validation: ended_at must not be earlier than start
        if (
            editDayEndedAtInput &&
            editDayStartedAtInput &&
            editDayEndedAtInput.value &&
            editDayStartedAtInput.value
        ) {
            const ended = new Date(editDayEndedAtInput.value);
            const started = new Date(editDayStartedAtInput.value);
            if (ended < started) {
                if (editDayEndError) {
                    editDayEndError.textContent =
                        "Время окончания не может быть раньше начала рабочего дня.";
                    editDayEndError.classList.remove("hidden");
                } else {
                    alert(
                        "Время окончания не может быть раньше начала рабочего дня.",
                    );
                }
                return;
            }
        }
        // clear previous inline error
        if (typeof editDayEndError !== "undefined" && editDayEndError) {
            editDayEndError.classList.add("hidden");
            editDayEndError.textContent = "";
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
            // saved — clear modal dirty so server response can be applied
            modalDirty = false;
            renderState(state);
            // if day was closed by this action, close the edit modal
            if (!state?.work_day || state?.work_day?.is_closed) {
                closeEditModal();
            }
        } catch (e) {
            // show server-side validation for ended_at inside modal (if present)
            if (e?.status === 422 && e?.errors && e.errors.ended_at) {
                if (typeof editDayEndError !== "undefined" && editDayEndError) {
                    editDayEndError.textContent = e.errors.ended_at.join(", ");
                    editDayEndError.classList.remove("hidden");
                    return;
                }
            }

            alert(e.message);
        }
    });

    // save start-of-day edit
    if (editDayStartSaveBtn) {
        editDayStartSaveBtn.addEventListener("click", async () => {
            if (!currentState?.work_day?.id) return;

            const comment = editDayCommentStartInput.value.trim();
            if (!comment) {
                alert("Добавьте комментарий к изменению.");
                return;
            }

            try {
                const state = await apiCall(
                    `${routes.editDayStart}/${currentState.work_day.id}/start-time`,
                    "PATCH",
                    {
                        started_at: editDayStartedAtInput.value,
                        comment,
                    },
                );
                editDayCommentStartInput.value = "";
                // saved — clear modal dirty so server response can be applied
                modalDirty = false;
                renderState(state);
            } catch (e) {
                alert(e.message);
            }
        });
    }

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
            // saved new break — clear modal dirty so server value is rendered
            modalDirty = false;
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
            // user made changes to breaks — clear modal dirty so server response is applied
            modalDirty = false;
            renderState(state);
        } catch (e) {
            alert(e.message);
        }
    });

    // local per-second UI update (keeps display smooth) — actual values are periodically synced from server
    setInterval(() => {
        if (!currentState) return;
        if (currentState.mode === "working") {
            workSeconds += 1;
        }
        if (currentState.mode === "paused") {
            breakSeconds += 1;
        }

        // decrement remaining counter every second
        if (typeof remainingSeconds !== "undefined" && remainingSeconds > 0) {
            remainingSeconds = Math.max(0, remainingSeconds - 1);
        }

        workTimeEl.textContent = formatSeconds(workSeconds);
        breakTimeEl.textContent = formatSeconds(breakSeconds);
        updateRemainingDisplay(remainingSeconds);
    }, 1000);

    // initial state fetch and start polling
    refreshState();

    // refresh immediately when page becomes visible or gains focus (handles sleep/resume)
    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible") {
            refreshState();
        }
    });
    window.addEventListener("focus", refreshState);

    // clear polling on unload
    window.addEventListener("beforeunload", () => {
        if (serverPollId) clearInterval(serverPollId);
    });
})();
