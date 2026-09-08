@props(['label', 'nama', 'wajib' => false, 'petunjuk' => null])

<div>
    <label for="{{ $nama }}" class="mb-1.5 block text-[13px] font-semibold text-slate-700">
        {{ $label }}
        @if ($wajib)<span class="text-bata-600">*</span>@endif
    </label>

    {{ $slot }}

    @error($nama)
        <p class="mt-1.5 text-[12px] font-medium text-bata-600">{{ $message }}</p>
    @enderror

    @if ($petunjuk)
        <p class="mt-1.5 text-[12px] text-slate-500">{{ $petunjuk }}</p>
    @endif
</div>
