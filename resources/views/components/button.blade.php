@php
$class = 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 inline-flex items-center rounded-md border border-transparent px-4 py-2 text-base font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2';
@endphp

@if ($href)
  <div {{ $attributes->merge(['class' => 'flex mt-6']) }}>
    <a
      href="{{ $href }}"
      target="{{ $external ? '_blank' : '' }}"
      rel="{{ $external ? 'noopener noreferrer' : '' }}"
      class="mx-auto"
    >
      <div class="{{ $class }}">
        {{ $slot }}
      </div>
    </a>
  </div>
@else
  <div {{ $attributes->merge(['class' => 'flex mt-6']) }}>
    <button
      type="{{ $type }}"
      class="mx-auto"
    >
      <div class="{{ $class }}">
        {{ $slot }}
      </div>
    </button>
  </div>
@endif
