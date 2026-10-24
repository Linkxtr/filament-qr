@php
    $payload = $getQrPayload();
    $qrSvg = $renderQrCode();
    $previewQrSvg = $isPreviewable() ? $renderPreviewQrCode() : null;
    $size = $getQrSize();
    $isDownloadable = $isDownloadable();
    $isPreviewable = $isPreviewable();
    $isCopyable = $isCopyable();
    $copyMessage = $getCopyMessage();
    $downloadFilename = $getDownloadFilename();
    $format = $getQrFormat();
    $heading = $getPreviewHeading();
    $state = $getState();
@endphp

<div
    x-data="{
        showModal: false,
        copied: false,
        copyPayload() {
            if (!navigator.clipboard) return;
            navigator.clipboard.writeText(@js($payload));
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
        download() {
            const link = document.createElement('a');
            link.download = @js($downloadFilename . '.' . $format);
            link.href = @js($qrSvg);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }"
    class="filament-qr-column inline-flex items-center gap-2"
>
    @if ($qrSvg)
        <div class="relative group inline-block">
            <div
                @if ($isPreviewable)
                    @click="showModal = true"
                    role="button"
                    tabindex="0"
                    title="Click to preview QR code"
                @endif
                class="inline-flex items-center justify-center p-1 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-md hover:border-gray-300 dark:hover:border-gray-700 @if ($isPreviewable) cursor-pointer @endif"
            >
                <img
                    src="{{ $qrSvg }}"
                    alt="QR Code"
                    width="{{ $size }}"
                    height="{{ $size }}"
                    class="block rounded object-contain"
                />
            </div>
        </div>

        @if ($isPreviewable)
            <div
                x-show="showModal"
                x-cloak
                @keydown.escape.window="showModal = false"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/50 backdrop-blur-sm"
            >
                <div
                    @click.away="showModal = false"
                    class="relative w-full max-w-md p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 text-center"
                >
                    <button
                        type="button"
                        @click="showModal = false"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                    </button>

                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                        {{ $heading }}
                    </h3>

                    <div class="inline-flex items-center justify-center p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 mb-4">
                        <img
                            src="{{ $previewQrSvg ?? $qrSvg }}"
                            alt="QR Code Preview"
                            class="max-w-full h-auto object-contain rounded-lg"
                        />
                    </div>

                    <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200/50 dark:border-gray-700/50 text-xs font-mono text-gray-600 dark:text-gray-300 break-all select-all mb-4 text-left">
                        {{ $payload }}
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        @if ($isDownloadable)
                            <button
                                type="button"
                                @click="download()"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-500 transition shadow-sm"
                            >
                                <x-filament::icon icon="heroicon-m-arrow-down-tray" class="w-4 h-4" />
                                <span>Save QR Image</span>
                            </button>
                        @endif

                        @if ($isCopyable)
                            <button
                                type="button"
                                @click="copyPayload()"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                            >
                                <x-filament::icon icon="heroicon-m-clipboard-document" class="w-4 h-4" />
                                <span x-text="copied ? @js($copyMessage) : 'Copy Payload'"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @else
        <span class="text-xs text-gray-400 dark:text-gray-500 italic">&mdash;</span>
    @endif
</div>
