<nav role="navigation" aria-label="Pagination">
    <div class="flex items-center gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-300 bg-white border border-gray-200 cursor-not-allowed select-none">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex items-center px-3 py-1.5 text-sm text-gray-400 select-none">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-blue-600 text-white border border-blue-600 select-none">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition"
                           aria-label="Page {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-300 bg-white border border-gray-200 cursor-not-allowed select-none">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </span>
        @endif

    </div>
</nav>
