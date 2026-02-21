<x-public-layout>
    <x-slot name="title">Glossary of Acupuncture & Detoxification Terms - NADA</x-slot>

    {{-- Hero Banner --}}
    <div class="py-10 text-center text-white bg-brand-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">Glossary of Acupuncture & Detoxification Terms</h1>
            <p class="mt-2 text-green-100 text-lg">{{ $totalTerms }} terms across {{ $categories->count() }} categories</p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="glossaryApp()">

        {{-- Search Bar --}}
        <div class="mb-6">
            <div class="relative max-w-xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search terms..."
                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg text-sm focus:ring-brand-primary focus:border-brand-primary"
                />
                <button
                    x-show="search.length > 0"
                    @click="search = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p x-show="search.length > 0" class="text-center text-sm text-gray-500 mt-2">
                <span x-text="visibleCount"></span> matching term<span x-show="visibleCount !== 1">s</span>
            </p>
        </div>

        {{-- Category Filter Pills --}}
        <div class="flex flex-wrap justify-center gap-2 mb-6">
            <button
                @click="activeCategory = ''"
                :class="activeCategory === '' ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors duration-150"
            >
                All
            </button>
            @foreach ($categories as $category)
                <button
                    @click="activeCategory = activeCategory === '{{ $category->slug }}' ? '' : '{{ $category->slug }}'"
                    :class="activeCategory === '{{ $category->slug }}' ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors duration-150"
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        {{-- A-Z Letter Jump Bar --}}
        <div class="flex flex-wrap justify-center gap-1 mb-8 px-2">
            <template x-for="letter in letters" :key="letter">
                <button
                    @click="jumpToLetter(letter)"
                    :class="availableLetters.has(letter) ? 'text-brand-primary hover:bg-brand-primary hover:text-white' : 'text-gray-300 cursor-default'"
                    class="w-8 h-8 flex items-center justify-center rounded text-sm font-semibold transition-colors duration-150"
                    :disabled="!availableLetters.has(letter)"
                    x-text="letter"
                ></button>
            </template>
        </div>

        {{-- Categories & Terms --}}
        @foreach ($categories as $category)
            <div
                x-show="isCategoryVisible('{{ $category->slug }}')"
                class="mb-10"
                x-cloak
            >
                <div class="flex items-center gap-3 mb-4" id="category-{{ $category->slug }}">
                    <h2 class="text-lg font-bold text-gray-900 whitespace-nowrap">{{ $category->name }}</h2>
                    <div class="flex-1 border-t border-gray-200"></div>
                    <span class="text-sm text-gray-400 whitespace-nowrap" x-text="categoryVisibleCount('{{ $category->slug }}') + ' terms'"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($category->publishedTerms as $term)
                        <div
                            x-show="isTermVisible('{{ addslashes($term->term) }}', '{{ $category->slug }}')"
                            data-term="{{ strtolower($term->term) }}"
                            data-letter="{{ strtoupper(substr($term->term, 0, 1)) }}"
                            data-category="{{ $category->slug }}"
                            class="term-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow duration-150"
                            id="term-{{ $term->slug }}"
                            x-cloak
                        >
                            <h3 class="font-semibold text-gray-900">{{ $term->term }}</h3>
                            <p class="mt-1 text-sm text-gray-600 leading-relaxed">{{ $term->definition }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- No Results --}}
        <div x-show="visibleCount === 0 && search.length > 0" class="text-center py-12 text-gray-500" x-cloak>
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <p class="text-lg font-medium">No terms found</p>
            <p class="mt-1">Try a different search term or clear your filters.</p>
        </div>
    </div>

    @push('scripts')
    <script>
        function glossaryApp() {
            return {
                search: '',
                activeCategory: '',
                letters: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split(''),

                get availableLetters() {
                    const letters = new Set();
                    document.querySelectorAll('.term-card').forEach(card => {
                        if (this.isTermVisibleByElement(card)) {
                            letters.add(card.dataset.letter);
                        }
                    });
                    return letters;
                },

                get visibleCount() {
                    let count = 0;
                    document.querySelectorAll('.term-card').forEach(card => {
                        if (this.isTermVisibleByElement(card)) count++;
                    });
                    return count;
                },

                isTermVisibleByElement(card) {
                    const term = card.dataset.term;
                    const category = card.dataset.category;
                    const matchesCategory = this.activeCategory === '' || category === this.activeCategory;
                    const matchesSearch = this.search === '' || term.includes(this.search.toLowerCase());
                    return matchesCategory && matchesSearch;
                },

                isTermVisible(term, categorySlug) {
                    const matchesCategory = this.activeCategory === '' || categorySlug === this.activeCategory;
                    const matchesSearch = this.search === '' || term.toLowerCase().includes(this.search.toLowerCase());
                    return matchesCategory && matchesSearch;
                },

                isCategoryVisible(categorySlug) {
                    if (this.activeCategory !== '' && categorySlug !== this.activeCategory) return false;
                    return this.categoryVisibleCount(categorySlug) > 0 || this.search === '';
                },

                categoryVisibleCount(categorySlug) {
                    let count = 0;
                    document.querySelectorAll(`.term-card[data-category="${categorySlug}"]`).forEach(card => {
                        if (this.isTermVisibleByElement(card)) count++;
                    });
                    return count;
                },

                jumpToLetter(letter) {
                    const card = document.querySelector(`.term-card[data-letter="${letter}"]`);
                    if (card) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            };
        }
    </script>
    @endpush
</x-public-layout>
