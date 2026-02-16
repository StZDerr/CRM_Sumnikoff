// Link cards: modal, edit, drag&drop handlers
const linkModal = document.getElementById('link-card-modal');
const linkOpen = document.getElementById('link-card-add');
const linkClose = document.getElementById('link-card-close');
const linkCancel = document.getElementById('link-card-cancel');
const linkOverlay = document.getElementById('link-card-overlay');
const linkForm = document.getElementById('link-card-form');
const linkModalTitle = document.getElementById('link-card-modal-title');
const linkModalSubmit = document.getElementById('link-card-submit');
const linkStoreAction = (linkForm?.dataset.storeAction || "{{ route('link-cards.store') }}");
const linkUpdateBase = (linkForm?.dataset.updateBase || "{{ url('link-cards') }}").replace(/\/$/, '');

function openLinkModal() {
if (!linkModal) return;
// default to create
linkForm.action = linkStoreAction;
linkForm.querySelector('input[name="title"]').value = '';
linkForm.querySelector('input[name="url"]').value = '';
linkForm.querySelector('input[name="icon"]').value = '';
const methodInput = linkForm.querySelector('input[name="_method"]');
if (methodInput) methodInput.remove();

linkModalTitle.textContent = 'Новая карточка';
linkModalSubmit.textContent = 'Добавить';

linkModal.classList.remove('hidden');
}

function closeLinkModal() {
if (!linkModal) return;
linkModal.classList.add('hidden');
}

if (linkOpen) linkOpen.addEventListener('click', openLinkModal);
if (linkClose) linkClose.addEventListener('click', closeLinkModal);
if (linkCancel) linkCancel.addEventListener('click', closeLinkModal);
if (linkOverlay) linkOverlay.addEventListener('click', closeLinkModal);

// Delegate edit button clicks to open edit modal (works even if function defined later)
document.addEventListener('click', function(e) {
const btn = e.target.closest('.edit-card-btn');
if (!btn) return;
const id = btn.dataset.id;
const title = btn.dataset.title || '';
const url = btn.dataset.url || '';
const icon = btn.dataset.icon || '';

if (typeof window.openEditModal === 'function') {
window.openEditModal(id, title, url, icon);
} else {
// fallback: populate form and show modal
linkForm.action = `${linkUpdateBase}/${id}`;
linkForm.querySelector('input[name="title"]').value = title;
linkForm.querySelector('input[name="url"]').value = url;
linkForm.querySelector('input[name="icon"]').value = icon;
let methodInput = linkForm.querySelector('input[name="_method"]');
if (!methodInput) {
methodInput = document.createElement('input');
methodInput.type = 'hidden';
methodInput.name = '_method';
linkForm.appendChild(methodInput);
}
methodInput.value = 'PUT';
linkModalTitle.textContent = 'Редактировать карточку';
linkModalSubmit.textContent = 'Сохранить';
linkModal.classList.remove('hidden');
}
});

// --- Edit functionality ---
window.openEditModal = function(id, title, url, icon) {
if (!linkModal) return;

linkForm.action = `${linkUpdateBase}/${id}`; // URL обновления
linkForm.querySelector('input[name="title"]').value = title;
linkForm.querySelector('input[name="url"]').value = url;
linkForm.querySelector('input[name="icon"]').value = icon;

let methodInput = linkForm.querySelector('input[name="_method"]');
if (!methodInput) {
methodInput = document.createElement('input');
methodInput.type = 'hidden';
methodInput.name = '_method';
linkForm.appendChild(methodInput);
}
methodInput.value = 'PUT';

linkModalTitle.textContent = 'Редактировать карточку';
linkModalSubmit.textContent = 'Сохранить';

linkModal.classList.remove('hidden');
};

// --- Drag & Drop ---
const grid = document.getElementById('link-cards-grid');
if (grid) {
const csrfToken = '{{ csrf_token() }}';
const reorderUrl = grid.dataset.reorderUrl || '{{ route('link-cards.reorder') }}';
const projectId = grid.dataset.projectId || '';
let dragSrc = null;

function persistOrder() {
const order = Array.from(grid.children)
.filter((el) => el.dataset.id)
.map((el) => Number(el.dataset.id));
fetch(reorderUrl, {
method: 'POST',
headers: {
'Content-Type': 'application/json',
'Accept': 'application/json',
'X-CSRF-TOKEN': csrfToken
},
body: JSON.stringify({
order: order,
project_id: projectId || null
})
}).catch(err => console.error('Reorder error', err));
}

function handleDragStart(e) {
dragSrc = this;
this.classList.add('opacity-60');
e.dataTransfer.effectAllowed = 'move';
e.dataTransfer.setData('text/plain', '');
}

function handleDragOver(e) {
e.preventDefault();
e.dataTransfer.dropEffect = 'move';
}

function handleDrop(e) {
e.preventDefault();
if (!dragSrc || dragSrc === this) return;

const items = Array.from(grid.children);
const srcIndex = items.indexOf(dragSrc);
const targetIndex = items.indexOf(this);

if (srcIndex < targetIndex) { grid.insertBefore(dragSrc, this.nextSibling); } else { grid.insertBefore(dragSrc, this); }
    persistOrder(); } function handleDragEnd() { this.classList.remove('opacity-60'); dragSrc=null; }
    Array.from(grid.children).forEach((card)=> {
    if (card.classList.contains('link-card-add')) return;
    card.addEventListener('dragstart', handleDragStart);
    card.addEventListener('dragover', handleDragOver);
    card.addEventListener('drop', handleDrop);
    card.addEventListener('dragend', handleDragEnd);
    });
    }
