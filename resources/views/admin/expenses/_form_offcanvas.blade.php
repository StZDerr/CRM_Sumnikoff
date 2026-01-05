<form action="{{ route('expenses.store') }}" method="POST" id="expenseOffcanvasForm" class="space-y-4"
    enctype="multipart/form-data">
    @csrf

    @include('admin.expenses._form')

    <div class="mt-4 flex justify-end gap-2">
        <button type="button" id="expenseOffcanvasCancel"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm">Отмена</button>
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-red-600 text-white px-4 py-2 text-sm">Сохранить</button>
    </div>

    <script>
        // кнопка отмены внутри канвы
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'expenseOffcanvasCancel') {
                const backdrop = document.getElementById('expenseOffcanvasBackdrop');
                if (backdrop) backdrop.click();
            }
        });
    </script>
</form>
