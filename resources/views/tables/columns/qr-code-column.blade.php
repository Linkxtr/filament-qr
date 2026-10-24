<div class="fi-ta-qr-code inline-flex items-center justify-center p-1 rounded-md bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
    @php
        $rendered = $renderQrCode();
        $payload = $getQrPayload();
        $format = $getQrFormat();
    @endphp

    @if ($rendered)
        @if ($format === 'svg')
            <div class="w-12 h-12 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full" title="{{ $payload }}">
                {!! $rendered !!}
            </div>
        @else
            <img src="{{ $rendered }}" alt="QR Code" class="w-12 h-12 object-contain" title="{{ $payload }}" />
        @endif
    @else
        <span class="text-xs text-gray-400 italic">No QR</span>
    @endif
</div>
