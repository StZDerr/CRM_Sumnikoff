@if ($vacations->isEmpty())
    <div class="text-sm text-gray-500">У пользователя нет отпусков.</div>
@else
    <ul class="space-y-2">
        @foreach ($vacations as $vac)
            <li class="flex items-center justify-between">
                <div class="text-sm">
                    <div class="font-medium">{{ $vac->start_date->format('d.m.Y') }} —
                        {{ $vac->end_date->format('d.m.Y') }}</div>
                    <div class="text-gray-500 text-xs">{{ $vac->notes ?? '-' }}</div>
                </div>
                <div class="text-sm">
                    @if ($vac->active)
                        <span class="text-green-600">Активный</span>
                        <form action="{{ route('vacations.end', $vac) }}" method="POST" class="inline-block ms-3">
                            @csrf
                            <button type="submit"
                                class="px-2 py-1 bg-yellow-500 text-white rounded text-xs">Завершить</button>
                        </form>
                    @else
                        <span class="text-gray-500">Завершён</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@endif
