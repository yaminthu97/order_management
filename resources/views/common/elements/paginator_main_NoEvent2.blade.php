<nav class="u-mt--sl d-table">
	@if(isset($paginator))
		@if($paginator->count() > 0)
			@php
			$disp_limit_max = \Config::get('Common.const.disp_limit_max'); // 100;
			$total = $paginator->total() > $disp_limit_max?$disp_limit_max:$paginator->total();
			$pagePage = floor(($paginator->currentPage() - 1) / 10);
			$lastPage = floor(($total - 1) / $paginator->perPage()) + 1;
			$maxPage = floor(($lastPage - 1) / 10);
			$fromPage = $pagePage * 10 + 1;
			$toPage = ($pagePage+1) * 10;
			$firstItem = $paginator->firstItem();
			$lastItem = $paginator->lastItem() > $disp_limit_max?$disp_limit_max:$paginator->lastItem();
			$count = $paginator->total() > $disp_limit_max?$disp_limit_max:$paginator->total();
			@endphp
			<div class="d-table-cell">{{$firstItem}} - {{$lastItem}}（全 {{$paginator->total()}} 件 中 {{$count}} 件）</div>
			<div class="d-table-cell"><ul class="pagination">
			{{-- 先頭ページ--}}
			@if($paginator->onFirstPage())
				@if($paginator->currentPage() == $lastPage)
					<li class="disabled"><span>最初へ</span></li>
					<li class="disabled"><span>前へ</span></li>
					<li class="active"><span>1</span></li>
					<li class="disabled"><span>次へ</span></li>
					<li class="disabled"><span>最後へ</span></li>
				@else
					<li class="disabled"><span>最初へ</span></li>
					<li class="disabled"><span>前へ</span></li>
					<li class="active"><span>1</span></li>
					@for($i = 2; $i <= $lastPage; $i++)
						@if($fromPage <= $i && $i <= $toPage)
						<li>
							<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $i }}">{{ $i }}</a></li>
						</li>
						@endif
					@endfor
					@if($maxPage != 0 && $pagePage != $maxPage)
					<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $toPage + 1}}">...</a></li>
					@endif
					<li>
						<li><a href="javascript:void(0);" class="next_page_link" page_no="2">次へ</a></li>
					</li>
					<li>
						<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $lastPage }}">最後へ</a></li>
					</li>
				@endif
			{{-- 最終ページ--}}
			@elseif($paginator->currentPage() == $lastPage)
				<li><a href="javascript:void(0);" class="next_page_link" page_no="1">最初へ</a></li>
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->currentPage() - 1 }}">前へ</a></li>
				@if($pagePage != 0)
					<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $fromPage - 1}}">...</a></li>
				@endif
				@for($i = 1; $i < $lastPage; $i++)
					@if($fromPage <= $i && $i <= $toPage)
					<li>
						<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $i }}">{{ $i }}</a></li>
					</li>
					@endif
				@endfor
				<li class="active"><span>{{ $lastPage }}</span></li>
				<li class="disabled"><span>次へ</span></li>
				<li class="disabled"><span>最後へ</span></li>
			{{-- 中間ページ--}}
			@else
				<li><a href="javascript:void(0);" class="next_page_link" page_no="1">最初へ</a></li>
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->currentPage() - 1 }}">前へ</a></li>
				@if($pagePage != 0)
					<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $fromPage - 1}}">...</a></li>
				@endif
				@for($i = 1; $i <= $lastPage; $i++)
					@if($fromPage <= $i && $i <= $toPage)
					<li>
						@if($paginator->currentPage() == $i)
							<li class="active"><span>{{$i}}</span></li>
						@else
							<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $i }}">{{$i}}</a></li>
						@endif
					</li>
					@endif
				@endfor
				@if($maxPage != 0 && $pagePage != $maxPage)
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $toPage + 1}}">...</a></li>
				@endif
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->currentPage() + 1 }}">次へ</a></li>
				<li>
					<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $lastPage }}">最後へ</a></li>
				</li>
			@endif
			</ul></div>
		@endif
	@endif
</nav>
		