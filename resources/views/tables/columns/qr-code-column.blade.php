<div class="fi-ta-qr-code inline-flex items-center gap-2">
    @php
        $rendered = $renderQrCode();
        $payload = $getQrPayload();
        $format = $getQrFormat();
        $statePath = (isset($column) && method_exists($column, 'getStatePath')) ? $column->getStatePath() : (is_callable($getName ?? null) ? $getName() : ($name ?? 'qr'));
        $modalId = 'fi-qr-preview-' . str_replace(['.', '$', '#', '[', ']'], '-', $statePath);
        $isPreviewable = $isPreviewable();
        $isDownloadable = $isDownloadable();
        $previewHeading = (is_callable($getPreviewHeading ?? null) ? $getPreviewHeading() : null)
            ?? (is_callable($getLabel ?? null) ? $getLabel() : ($label ?? null))
            ?? __('QR Code Preview');
        $previewRendered = $isPreviewable ? $renderPreviewQrCode() : null;
        $recordKey = (isset($column) && method_exists($column, 'getRecordKey')) ? $column->getRecordKey() : null;
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
                        x-on:click.stop
                        title="{{ __('Click to zoom preview') }}"
                        class="inline-flex items-center justify-center p-1 rounded-md bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:scale-105 hover:shadow transition-all cursor-zoom-in focus:outline-none"
                    >
                        @if ($format === 'svg')
                            <div class="w-12 h-12 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                {!! $rendered !!}
                            </div>
                        @else
                            <img src="{{ $rendered }}" alt="QR Code" class="w-12 h-12 object-contain" />
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
            <div class="inline-flex items-center justify-center p-1 rounded-md bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700">
                @if ($format === 'svg')
                    <div class="w-12 h-12 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full" title="{{ $payload }}">
                        {!! $rendered !!}
                    </div>
                @else
                    <img src="{{ $rendered }}" alt="QR Code" class="w-12 h-12 object-contain" title="{{ $payload }}" />
                @endif
            </div>

            @if ($isDownloadable)
                <a
                    href="{{ $downloadHref }}"
                    download="{{ $downloadFilename }}"
                    title="{{ __('Download QR') }}"
                    class="p-1 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors"
                >
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                </a>
            @endif
        @endif
    @else
        <span class="text-xs text-gray-400 italic">No QR</span>
    @endif
</div>
