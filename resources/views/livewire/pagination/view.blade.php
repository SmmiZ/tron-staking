@if ($paginator->hasPages())
    <ul class="pagination" role="navigation">
        <li class="page-item">
            @if ($paginator->onFirstPage())
                <span class="page-link" aria-hidden="true">
                <span class="d-none d-md-block">‹</span>
                <span class="d-block d-md-none">Назад</span>
            </span>
            @else
                <button type="button" class="page-link" wire:click="previousPage" rel="prev" aria-label="Назад">
                    <span class="d-none d-md-block">‹</span>
                    <span class="d-block d-md-none">Назад</span>
                </button>
            @endif
        </li>
        @foreach ($elements as $element)
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($paginator->currentPage() > 3 && $page === 2)
                        <li class="page-item disabled d-none d-md-block" aria-disabled="true"><span class="page-link">...</span>
                        </li>
                    @endif
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active d-none d-md-block" aria-current="page"><span
                                    class="page-link">{{ $page }}</span></li>
                    @elseif ($page === $paginator->currentPage() + 1 || $page === $paginator->currentPage() + 2 || $page === $paginator->currentPage() - 1 || $page === $paginator->currentPage() - 2)
                        <li class="page-item d-none d-md-block">
                            <button type="button" class="page-link" wire:click="gotoPage({{$page}})">{{$page}}</button>
                        </li>
                    @endif
                    @if ($paginator->currentPage() < $paginator->lastPage() - 2  && $page === $paginator->lastPage() - 1)
                        <li class="page-item disabled d-none d-md-block" aria-disabled="true"><span class="page-link">...</span>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <button type="button" class="page-link" wire:click="gotoPage({{ $paginator->lastPage() }})" rel="next"
                        aria-label="Вперёд">
                    <span class="d-block d-md-none">Последняя</span>
                    <span class="d-none d-md-block">›</span>
                </button>
            </li>
        @endif
    </ul>
@endif