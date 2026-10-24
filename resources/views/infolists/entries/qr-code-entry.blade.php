<div class="fi-in-qr-code flex flex-col items-center justify-center p-4 rounded-xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800">
    @php
        $rendered = $renderQrCode();
        $payload = $getQrPayload();
        $format = $getQrFormat();
    @endphp

    @if ($rendered)
        <div class="p-2 rounded-lg bg-white shadow-inner flex items-center justify-center">
            @if ($format === 'svg')
                <div class="max-w-full [&>svg]:w-full [&>svg]:h-auto">
                    {!! $rendered !!}
                </div>
            @else
                <img src="{{ $rendered }}" alt="QR Code" class="max-w-full h-auto object-contain" />
            @endif
        </div>
        @if ($payload)
            <div class="mt-3 text-center">
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400 break-all select-all">
                    {{ $payload }}
                </span>
            </div>
        @endif
    @else
        <span class="text-sm text-gray-400 italic">No QR Code Available</span>
    @endif
</div>
