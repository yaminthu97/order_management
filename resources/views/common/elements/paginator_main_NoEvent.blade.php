<nav class="u-mt--sl d-table">
	@if(isset($paginator))
		@if($paginator->count() > 0)
			<div class="d-table-cell">{{$paginator->firstItem()}} - {{$paginator->lastItem()}}（全 {{$paginator->total()}} 件 中 {{$paginator->count()}} 件）</div>
			<div class="d-table-cell"><ul class="pagination">
			{{-- 先頭ページ--}}
			@if($paginator->onFirstPage())
				@if($paginator->currentPage() == $paginator->lastPage())
					<li class="disabled"><span>最初へ</span></li>
					<li class="disabled"><span>前へ</span></li>
					<li class="active"><span>1</span></li>
					<li class="disabled"><span>次へ</span></li>
					<li class="disabled"><span>最後へ</span></li>
				@else
					<li class="disabled"><span>最初へ</span></li>
					<li class="disabled"><span>前へ</span></li>
					<li class="active"><span>1</span></li>
					@for($i = 2; $i <= $paginator->lastPage(); $i++)
						<li>
							<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $i }}">{{ $i }}</a></li>
						</li>
					@endfor
					<li>
						<li><a href="javascript:void(0);" class="next_page_link" page_no="2">次へ</a></li>
					</li>
					<li>
						<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->lastPage() }}">最後へ</a></li>
					</li>
				@endif
			{{-- 最終ページ--}}
			@elseif($paginator->currentPage() == $paginator->lastPage())
				<li><a href="javascript:void(0);" class="next_page_link" page_no="1">最初へ</a></li>
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->currentPage() - 1 }}">前へ</a></li>
				@for($i = 1; $i < $paginator->lastPage(); $i++)
					<li>
						<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $i }}">{{ $i }}</a></li>
					</li>
				@endfor
				<li class="active"><span>{{ $paginator->lastPage() }}</span></li>
				<li class="disabled"><span>次へ</span></li>
				<li class="disabled"><span>最後へ</span></li>
			{{-- 中間ページ--}}
			@else
				<li><a href="javascript:void(0);" class="next_page_link" page_no="1">最初へ</a></li>
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->currentPage() - 1 }}">前へ</a></li>
				@for($i = 1; $i <= $paginator->lastPage(); $i++)
					<li>
						@if($paginator->currentPage() == $i)
							<li class="active"><span>{{$i}}</span></li>
						@else
							<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $i }}">{{$i}}</a></li>
						@endif
					</li>
				@endfor
				<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->currentPage() + 1 }}">次へ</a></li>
				<li>
					<li><a href="javascript:void(0);" class="next_page_link" page_no="{{ $paginator->lastPage() }}">最後へ</a></li>
				</li>
			@endif
			</ul></div>
		@endif
	@endif
</nav>
		