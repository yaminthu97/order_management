<nav class="u-mt--sl d-table">
    <!-- ページャーメイン -->
    @if (isset($paginator))
        @if ($paginator->count() > 0)
            <div class="d-table-cell">{{ $paginator->firstItem() }} - {{ $paginator->lastItem() }}（全
                {{ $paginator->total() }} 件 中 {{ $paginator->count() }} 件）</div>

            <div class="d-table-cell">
                <ul class="pagination">
                    @if ($paginator->onFirstPage())
                        @if ($paginator->currentPage() == $paginator->lastPage())
                            <li class="disabled"><span>最初へ</span></li>
                            <li class="disabled"><span>前へ</span></li>
                            <li class="active"><span>1</span></li>
                            <li class="disabled"><span>次へ</span></li>
                            <li class="disabled"><span>最後へ</span></li>
                        @else
                            <li class="disabled"><span>最初へ</span></li>
                            <li class="disabled"><span>前へ</span></li>
                            <li class="active"><span>1</span></li>
                            @for ($i = 2; $i <= $paginator->lastPage(); $i++)
                                <li>
                                    <a href="javascript:void(0);" onClick="setNextPage({{ $i }})">
                                        {{ $i }}
                                    </a>
                                </li>
                            @endfor
                            <li>
                                <a href="javascript:void(0);" onClick="setNextPage(2)">
                                    次へ
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"
                                    onClick="setNextPage({{ $paginator->lastPage() }})">
                                    最後へ
                                </a>
                            </li>
                        @endif
                    @elseif($paginator->currentPage() == $paginator->lastPage())
                        <li><a href="javascript:void(0);" onClick="setNextPage(1)">最初へ</a></li>
                        <li><a href="javascript:void(0);"
                                onClick="setNextPage({{ $paginator->currentPage() - 1 }})">前へ</a></li>
                        @for ($i = 1; $i < $paginator->lastPage(); $i++)
                            <li>
                                <a href="javascript:void(0);"
                                    onClick="setNextPage({{ $i }})">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor
                        <li class="active"><span>{{ $paginator->lastPage() }}</span></li>
                        <li class="disabled"><span>次へ</span></li>
                        <li class="disabled"><span>最後へ</span></li>
                    @else
                        <li>
                            <a href="javascript:void(0);" onClick="setNextPage(1)">
                                最初へ
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);"
                                onClick="setNextPage({{ $paginator->currentPage() - 1 }})">
                                前へ
                            </a>
                        </li>
                        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                            <li @class([
                                "active" => $paginator->currentPage() == $i
                                ])>
                                @if ($paginator->currentPage() == $i)
                                    <span>
                                        {{ $i }}
                                    </span>
                                @else
                                    <a href="javascript:void(0);" onClick="setNextPage({{ $i }})">
                                        {{ $i }}
                                    </a>
                                @endif
                            </li>
                        @endfor
                        <li>
                            <a href="javascript:void(0);"
                                onClick="setNextPage({{ $paginator->currentPage() + 1 }})">
                                次へ
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);"
                                onClick="setNextPage({{ $paginator->lastPage() }})">
                                最後へ
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    @endif
</nav>
