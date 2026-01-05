<form action="{{ route('payments.store') }}" method="POST" id="paymentOffcanvasForm" class="space-y-4">
    @csrf

    @include('admin.payments._form')

    <div class="mt-4 flex justify-end gap-2">
        <button type="button" id="paymentOffcanvasCancel"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm">Отмена</button>
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2 text-sm">Сохранить</button>
    </div>

    <script>
        // кнопка отмены внутри канвы
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'paymentOffcanvasCancel') {
                const backdrop = document.getElementById('paymentOffcanvasBackdrop');
                if (backdrop) backdrop.click();
            }
        });
    </script>
</form>
