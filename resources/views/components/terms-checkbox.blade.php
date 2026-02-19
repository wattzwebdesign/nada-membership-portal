@props(['terms'])

@if ($terms)
<div class="mt-4 mb-2">
    <label class="flex items-start gap-2 cursor-pointer">
        <input type="checkbox" name="accept_terms" value="1" required
               class="mt-1 rounded border-gray-300 text-brand-primary shadow-sm focus:ring-brand-primary">
        <span class="text-sm text-gray-700">
            I have read and agree to the
            <a href="{{ route('terms.show') }}" target="_blank"
               class="underline font-medium text-brand-primary hover:text-brand-accent">
                Terms & Conditions (v{{ $terms->version }})
            </a>
        </span>
    </label>
    @error('accept_terms')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
@endif
