@php
    $headerPosMachine = function_exists('pos_machine') ? pos_machine() : null;
@endphp
@if($headerPosMachine && $headerPosMachine->isActive())
    <div
        class="me-1 flex max-w-[min(11rem,32vw)] shrink-0 items-center truncate rounded-md border border-gray-200 bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600 dark:border-gray-600 dark:bg-gray-700/80 dark:text-gray-300 sm:max-w-[14rem]"
        title="{{ $headerPosMachine->alias ?? __('multipos::messages.registration.device') }}"
    >
        <span class="shrink-0">@lang('multipos::messages.registration.active.label')</span>
        <span class="ms-1 min-w-0 truncate">{{ $headerPosMachine->alias ?? __('multipos::messages.registration.device') }}</span>
    </div>
@endif
