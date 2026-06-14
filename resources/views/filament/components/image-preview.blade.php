<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $record = $getRecord();
    @endphp
    @if (! $record || ! $record->attachment)
        <span class="text-gray-500 italic">Tidak ada lampiran</span>
    @else
        @php $url = asset('storage/' . $record->attachment); @endphp
        <a href="{{ $url }}" target="_blank">
            <img src="{{ $url }}" style="max-width: 300px; border-radius: 8px; border: 1px solid #e5e7eb;" />
        </a>
    @endif
</x-dynamic-component>
