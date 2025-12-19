(function () {
    const commentsList = document.getElementById("comments-list");
    if (!commentsList) return;

    const commentsUrl = commentsList.dataset.commentsUrl;
    const storeUrl =
        commentsList.dataset.storeUrl ||
        (document.getElementById("comment-form")?.getAttribute("action") ??
            null);
    const commentForm = document.getElementById("comment-form");

    // GLightbox init (works for dynamically added elements)
    let glight = null;

    function initLightbox() {
        if (!window.GLightbox) return console.warn("GLightbox not loaded");
        try {
            if (glight) glight.destroy();
        } catch (e) {}
        glight = window.GLightbox({ selector: ".glightbox" });
    }

    function isLightboxOpen() {
        // Detect if GLightbox modal is currently visible in the DOM
        const el = document.querySelector(
            ".glightbox-overlay, .glightbox-container, .gslide"
        );
        if (!el) return false;
        try {
            const style = window.getComputedStyle(el);
            return (
                style &&
                style.display !== "none" &&
                style.visibility !== "hidden" &&
                parseFloat(style.opacity) > 0
            );
        } catch (e) {
            return true; // be conservative on error — assume open
        }
    }

    async function fetchComments() {
        if (document.hidden) return;
        // If the user is viewing a photo in the lightbox, skip refreshing comments
        if (isLightboxOpen()) return;
        try {
            const res = await fetch(commentsUrl, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            if (res.ok) {
                const html = await res.text();
                commentsList.innerHTML = html;
                // re-init lightbox for newly injected content
                initLightbox();
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Poll every 5 seconds
    fetchComments();
    const interval = setInterval(fetchComments, 5000);

    // AJAX submit
    if (commentForm) {
        commentForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);
            try {
                const res = await fetch(
                    storeUrl || commentForm.getAttribute("action"),
                    {
                        method: "POST",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: formData,
                    }
                );

                if (res.ok) {
                    const json = await res.json();
                    if (json.html) {
                        commentsList.insertAdjacentHTML(
                            "afterbegin",
                            json.html
                        );
                        // initialize lightbox for newly added content only if lightbox is not currently open
                        if (!isLightboxOpen()) {
                            initLightbox();
                        }
                        commentForm.reset();
                    } else {
                        fetchComments();
                        commentForm.reset();
                    }
                } else if (res.status === 422) {
                    const data = await res.json();
                    alert(Object.values(data.errors).flat().join("\n"));
                } else {
                    fetchComments();
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    // Delegate delete (AJAX)
    commentsList.addEventListener("click", async (e) => {
        const btn = e.target.closest(".delete-comment-form button");
        if (!btn) return;
        e.preventDefault();
        const form = btn.closest("form");
        const action = form.getAttribute("action");

        if (!confirm("Удалить комментарий?")) return;

        try {
            const res = await fetch(action, {
                method: "DELETE",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });
            if (res.ok) {
                form.closest(".comment-item")?.remove();
            } else {
                fetchComments();
            }
        } catch (err) {
            console.error(err);
        }
    });
})();
