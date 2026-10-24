<div class="fi-in-qr-code flex flex-col items-center justify-center p-4 rounded-xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800">
    @php
        $rendered = $renderQrCode();
        $payload = $getQrPayload();
        $format = $getQrFormat();
        try {
            $statePath = (isset($entry) && method_exists($entry, "getStatePath")) ? $entry->getStatePath() : null;
        } catch (\Throwable) {
            $statePath = null;
        }
        $statePath ??= (is_callable($getName ?? null) ? $getName() : ($name ?? "qr"));
        $modalId = "fi-in-qr-preview-" . str_replace([".", "$", "#", "[", "]"], "-", (string) $statePath);
        $isPreviewable = $isPreviewable();
        $isDownloadable = $isDownloadable();
        $previewHeading = (is_callable($getPreviewHeading ?? null) ? $getPreviewHeading() : null)
            ?? (is_callable($getLabel ?? null) ? $getLabel() : ($label ?? null))
            ?? __("QR Code Preview");
        $previewRendered = $isPreviewable ? $renderPreviewQrCode() : null;
        try {
            $recordKey = (isset($entry) && method_exists($entry, "getRecord") && $entry->getRecord()) ? $entry->getRecord()->getKey() : null;
        } catch (\Throwable) {
            $recordKey = null;
        }
        $downloadHref = $format === 'svg'
            ? 'data:image/svg+xml;base64,' . base64_encode($rendered ?? '')
            : $rendered;
        $downloadFilename = 'qr-' . ($recordKey ?? 'code') . '.' . $format;
    @endphp

    @if ($rendered)
        @if ($isPreviewable)
            <x-filament::modal
                :id="$modalId"
                width="md"
                alignment="center"
            >
                <x-slot name="trigger">
                    <button
                        type="button"
                        class="p-2 rounded-lg bg-white shadow-inner flex items-center justify-center cursor-zoom-in hover:scale-105 transition-all focus:outline-none"
                        title="{{ __('Click to zoom preview') }}"
                    >
                        @if ($format === 'svg')
                            <div class="max-w-full [&>svg]:w-full [&>svg]:h-auto">
                                {!! $rendered !!}
                            </div>
                        @else
                            <img src="{{ $rendered }}" alt="QR Code" class="max-w-full h-auto object-contain" />
                        @endif
                    </button>
                </x-slot>

                <x-slot name="heading">
                    {{ $previewHeading }}
                </x-slot>

                <div class="fi-qr-modal-content flex flex-col items-center justify-center p-4 gap-4">
                    <div class="p-4 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center max-w-full">
                        @if ($format === 'svg')
                            <div class="w-64 h-64 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                {!! $previewRendered ?? $rendered !!}
                            </div>
                        @else
                            <img src="{{ $previewRendered ?? $rendered }}" alt="QR Code" class="w-64 h-64 object-contain" />
                        @endif
                    </div>

                    @if ($payload)
                        <div class="w-full text-center px-2">
                            <span class="text-xs font-mono text-gray-500 dark:text-gray-400 break-all select-all bg-gray-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 block">
                                {{ $payload }}
                            </span>
                        </div>
                    @endif

                    @if ($isDownloadable)
                        <div class="mt-2 flex items-center justify-center">
                            <a
                                href="{{ $downloadHref }}"
                                download="{{ $downloadFilename }}"
                                class="fi-btn fi-btn-size-sm inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-600 text-white shadow hover:bg-primary-500 transition-colors"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                                <span>{{ __('Download QR') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            </x-filament::modal>
        @else
            <div class="p-2 rounded-lg bg-white shadow-inner flex items-center justify-center">
                @if ($format === 'svg')
                    <div class="max-w-full [&>svg]:w-full [&>svg]:h-auto">
                        {!! $rendered !!}
                    </div>
                @else
                    <img src="{{ $rendered }}" alt="QR Code" class="max-w-full h-auto object-contain" />
                @endif
            </div>
        @endif

        @if ($payload)
            <div class="mt-3 text-center">
                <span class="text-xs font-mono text-gray-500 dark:text-gray-400 break-all select-all">
                    {{ $payload }}
                </span>
            </div>
        @endif

        @if ($isDownloadable && ! $isPreviewable)
            <div class="mt-3 flex items-center justify-center">
                <a
                    href="{{ $downloadHref }}"
                    download="{{ $downloadFilename }}"
                    class="fi-btn fi-btn-size-sm inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-600 text-white shadow hover:bg-primary-500 transition-colors"
                >
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                    <span>{{ __('Download QR') }}</span>
                </a>
            </div>
        @endif
    @else
        <span class="text-sm text-gray-400 italic">No QR Code Available</span>
    @endif
</div>
