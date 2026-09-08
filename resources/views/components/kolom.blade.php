@props(['label', 'nama', 'wajib' => false, 'petunjuk' => null])

<div>
    <label for="{{ $nama }}" class="mb-1.5 block text-[13px] font-semibold text-teks-sedang">
        {{ $label }}
        @if ($wajib)<span class="text-bahaya-teks">*</span>@endif
    </label>

    {{ $slot }}

    @error($nama)
        <p class="mt-1.5 text-[12px] font-medium text-bahaya-teks">{{ $message }}</p>
    @enderror

    @if ($petunjuk)
        <p class="mt-1.5 text-[12px] text-teks-samar">{{ $petunjuk }}</p>
    @endif
</div>
