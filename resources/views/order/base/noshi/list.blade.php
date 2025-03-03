{{-- NEOFM0210:熨斗生成 --}}
@php
$ScreenCd='NEOFM0210';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '熨斗生成')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>熨斗生成</li>
@endsection

<style>
    th.row1_none {
        background-color:transparent !important;
        border-top-color:transparent!important;
        border-right-color:transparent!important;
    }
    th.row1_bg {
        background-color:#fff2cc !important;
    }
    .shared_flg1 {
        background-color:#bfbfbf !important;
    }
</style>

@section('content')
<form method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}
<input type="hidden" id="create_noshi_check_linkage_url" value="{{ route('order.create.noshi.check-linkage') }}">
<input type="hidden" id="create_noshi_check_create_url" value="{{ route('order.create.noshi.check-create') }}">
<input type="hidden" id="create_noshi_check_shared_url" value="{{ route('order.create.noshi.check-shared') }}">
<input type="hidden" id="create_noshi_create_url" value="{{ route('order.create.noshi.create') }}">
<input type="hidden" id="create_noshi_clear_url" value="{{ route('order.create.noshi.clear') }}">
<div>
    <div class="c-box--1600 u-mt--xs">
		<div id="line-01"></div>
		<p class="c-ttl--02">検索条件</p>
		<table class="table c-tbl c-tbl--1600">
			<tr>
				<th class="c-box--180">進捗区分</th>
				<td>
                    <select class="form-control u-input--mid" id="progress_type" name="progress_type">
                        <option value=""></option>
                    @foreach (\App\Enums\ProgressTypeEnum::cases() as $target)
    					<option value="{{ $target->value }}" {{(old('progress_type',$searchRow['progress_type']??'')) == $target->value?'selected':''}}>{{ $target->label() }}</option>
			        @endforeach
					</select>
                </td>
				<th class="c-box--180">出荷予定日</th>
				<td>
                    <div class="d-table">
						<div class="d-table-cell">
							<div class="input-group">
								<input type="text" class="form-control c-box--180 datetime-picker" name="deli_plan_date_from" id="deli_plan_date_from" value="{{old('deli_plan_date_from',$searchRow['deli_plan_date_from']??'')}}">
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
						<div class="d-table-cell">&nbsp;～&nbsp;</div>
						<div class="d-table-cell">
							<div class="input-group">
								<input type="text" class="form-control c-box--180 datetime-picker" name="deli_plan_date_to" id="deli_plan_date_to" value="{{old('deli_plan_date_to',$searchRow['deli_plan_date_to']??'')}}">
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</div>
                </td>
			</tr>
			<tr>
				<th class="c-box--180">受注ID</th>
				<td>
                    <input type="text" class="form-control c-box--180" name="t_order_hdr_id" id="t_order_hdr_id" value="{{old('t_order_hdr_id',$searchRow['t_order_hdr_id']??'')}}">
                </td>
				<th class="c-box--180">配送希望日</th>
				<td>
                    <div class="d-table">
                        <div class="d-table-cell">
                            <div class="input-group">
                                <input type="text" class="form-control c-box--180 datetime-picker" name="deli_hope_date_from" id="deli_hope_date_from" value="{{old('deli_hope_date_from',$searchRow['deli_hope_date_from']??'')}}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div class="d-table-cell">&nbsp;～&nbsp;</div>
                        <div class="d-table-cell">
                            <div class="input-group">
                                <input type="text" class="form-control c-box--180 datetime-picker" name="deli_hope_date_to" id="deli_hope_date_to" value="{{old('deli_hope_date_to',$searchRow['deli_hope_date_to']??'')}}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
			</tr>
			<tr>
				<th class="c-box--180">受注日時</th>
				<td>
                    <div class="d-table">
                        <div class="d-table-cell">
                            <div class="input-group">
                                <input type="text" class="form-control c-box--180 datetime-picker" name="order_date_from" id="order_date_from" value="{{old('order_date_from',$searchRow['order_date_from']??'')}}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        <div class="d-table-cell">&nbsp;～&nbsp;</div>
                        <div class="d-table-cell">
                            <div class="input-group">
                                <input type="text" class="form-control c-box--180 datetime-picker" name="order_date_to" id="order_date_to" value="{{old('order_date_to',$searchRow['order_date_to']??'')}}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
				<th class="c-box--180">ECサイト</th>
				<td>
                    <select class="form-control u-input--mid" id="ecs_name" name="ecs_name">
                        <option value=""></option>
                        @foreach($viewExtendData['ecs'] as $val=>$key)
                        <option value="{{$key}}" {{(old('ecs_name',$searchRow['ecs_name']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach                        
					</select>
                </td>
			</tr>
			<tr>
				<th class="c-box--180">注文主顧客ID</th>
				<td>
                    <input type="text" class="form-control c-box--180" name="m_cust_id" id="m_cust_id" value="{{old('m_cust_id',$searchRow['m_cust_id']??'')}}">
                </td>
				<th class="c-box--180">ECサイト注文ID</th>
				<td>
                    <input type="text" class="form-control c-box--180" name="ec_order_num" id="ec_order_num" value="{{old('ec_order_num',$searchRow['ec_order_num']??'')}}">
                </td>
			</tr>
			<tr>
				<th class="c-box--180">注文主氏名（前方一致）</th>
				<td>
                    <input type="text" class="form-control c-box--full" name="cust_name" id="cust_name" value="{{old('cust_name',$searchRow['cust_name']??'')}}">
                </td>
				<th class="c-box--180">熨斗 表書き</th>
				<td>
                    <input type="text" class="form-control c-box--180" name="omotegaki" id="omotegaki" value="{{old('omotegaki',$searchRow['omotegaki']??'')}}">
                </td>
			</tr>
			<tr>
				<th class="c-box--180">送付先氏名（前方一致）</th>
				<td>
                    <input type="text" class="form-control c-box--full" name="destination_name" id="destination_name" value="{{old('destination_name',$searchRow['destination_name']??'')}}">
                </td>
				<th class="c-box--180">名入れ</th>
				<td>
                    <input type="text" class="form-control c-box--full" name="naming" id="naming" value="{{old('naming',$searchRow['naming']??'')}}">
                </td>
			</tr>
			<tr>
				<th class="c-box--180">商品コード（複数指定可）</th>
				<td>
                    <input type="text" class="form-control c-box--full" name="item_cd" id="item_cd" value="{{old('item_cd',$searchRow['item_cd']??'')}}">
                </td>
				<th class="c-box--180">個別生成</th>
				<td>
                    <select class="form-control u-input--mid" id="output_counter" name="output_counter">
                        <option value=""></option>
                        <option value="1"  {{(old('output_counter',$searchRow['output_counter']??'')) === '1'?'selected':''}}>済</option>
                        <option value="0" {{(old('output_counter',$searchRow['output_counter']??'')) === '0'?'selected':''}}>未</option>
					</select>
                </td>
			</tr>
			<tr>
				<th class="c-box--180">熨斗タイプ</th>
				<td>
                    <select class="form-control u-input--mid" id="noshi_type" name="noshi_type">
                        <option value=""></option>
                        @foreach($viewExtendData['noshi_type'] as $key=>$val)
                        <option value="{{$key}}" {{(old('noshi_type',$searchRow['noshi_type']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach
					</select>
                </td>
				<th class="c-box--180">熨斗ファイル名</th>
				<td>
                    <input type="text" class="form-control c-box--full" name="noshi_file_name" id="noshi_file_name" value="{{$searchRow['noshi_file_name']??''}}">
                </td>
			</tr>
			<tr>
				<th class="c-box--180">種別</th>
				<td>
                    <select class="form-control u-input--mid" id="attachment_item_group_name" name="attachment_item_group_name">
                        <option value=""></option>
                        @foreach($viewExtendData['attachment_item_group_name'] as $key=>$val)
                        <option value="{{$key}}" {{(old('attachment_item_group_name',$searchRow['attachment_item_group_name']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach
					</select>
                </td>
				<th class="c-box--180">熨斗種類</th>
				<td>
                    <select class="form-control u-input--mid" id="noshi_format_name" name="noshi_format_name">
                        <option value=""></option>
                        @foreach($viewExtendData['noshi_format'] as $key=>$val)
                        <option value="{{$key}}" {{(old('noshi_format_name',$searchRow['noshi_format_name']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach
                        
					</select>
                </td>
			</tr>
			<tr>
				<th class="c-box--180">顧客ランク</th>
				<td>
                    <select class="form-control u-input--mid" id="m_cust_runk_name" name="m_cust_runk_name">
                        <option value=""></option>
                        @foreach($viewExtendData['m_cust_runk_name'] as $val=>$key)
                        <option value="{{$key}}" {{(old('m_cust_runk_name',$searchRow['m_cust_runk_name']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach
					</select>
                </td>
				<th class="c-box--180">名入れパターン</th>
				<td>
                    <select class="form-control u-input--mid" id="noshi_naming_pattern_name" name="noshi_naming_pattern_name">
                        <option value=""></option>
                        @foreach($viewExtendData['noshi_naming_pattern'] as $key=>$val)
                        <option value="{{$key}}" {{(old('noshi_naming_pattern',$searchRow['noshi_naming_pattern_name']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach
					</select>
                </td>
			</tr>
			<tr>
				<th class="c-box--180">受注方法</th>
				<td>
                    <select class="form-control u-input--mid" id="order_type_name" name="order_type_name">
                        <option value=""></option>
                        @foreach($viewExtendData['order_type'] as $val=>$key)
                        <option value="{{$key}}" {{(old('order_type_name',$searchRow['order_type_name']??'')) == $key?'selected':''}}>{{$val}}</option>
                        @endforeach
					</select>
                </td>
				<td></td>
				<td></td>
			</tr>
		</table>
        <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search" value="search">検索</button>
        <input type="hidden" name="{{config('define.session_key_id')}}" value="{{$searchRow[config('define.session_key_id')] ?? ''}}">

        @if ($paginator??null)
        <div>
            @include('common.elements.paginator_header')
            @include('common.elements.page_list_count')
            <br>
            <div class="table-responsive">
                <table class="table table-bordered c-tbl table-link nowrap">
                    <tr>
                    <th colspan="2" style="min-width:180px" class="text-center row1_bg">確認(連携)</th>
                    <th colspan="2" style="min-width:180px" class="text-center row1_bg">まとめて生成／共有</th>
                    <th colspan="20" class="row1_none"></th>
                    </tr>
                    <tr>
                    <th style="min-width:90px" class="text-center">確認済</th>
                    <th style="min-width:90px" class="text-center">全選択<br><input type="checkbox" class="shared_flg_all" value="1"></th>
                    <th style="min-width:90px" class="text-center">コピー元</th>
                    <th style="min-width:90px" class="text-center">全選択<br><input type="checkbox" class="copy_to_all" value="1"></th>
                    <th style="min-width:300px" class="text-center">ファイル名</th>
                    <th style="min-width:80px" class="text-center">個別生成</th>
                    <th style="min-width:80px" class="text-center">クリア</th>
                    <th style="min-width:100px" class="text-center">商品CD</th>
                    <th style="min-width:80px" class="text-center">数量</th>
                    <th style="min-width:200px" class="text-center">熨斗種類</th>
                    <th style="min-width:200px" class="text-center">表書き</th>
                    <th style="min-width:200px" class="text-center">名入れ</th>
                    <th style="min-width:300px" class="text-center">熨斗タイプ</th>
                    <th style="min-width:300px" class="text-center">名入れパターン</th>
                    <th style="min-width:80px" class="text-center">種別</th>
                    <th style="min-width:100px" class="text-center">受注ID</th>
                    <th style="min-width:80px" class="text-center">受注日</th>
                    <th style="min-width:100px" class="text-center">注文主顧客ID</th>
                    <th style="min-width:200px" class="text-center">注文主</th>
                    <th style="min-width:300px" class="text-center">送付先</th>
                    <th style="min-width:80px" class="text-center">送付先名同一</th>
                    <th style="min-width:100px" class="text-center">出荷予定日</th>
                    <th style="min-width:100px" class="text-center">配送希望日</th>
                    <th style="min-width:100px" class="text-center">ECサイト注文ID</th>
                    </tr>
                    @if(!empty($paginator->count()) > 0)
                		@foreach($paginator as $noshiDtl)
                        <tr id="noshiDtl-{{$noshiDtl['t_order_dtl_noshi_id']}}">
                            <td class="text-center shared_flg_text shared_flg{{empty($noshiDtl->shared_flg)?'0':'1'}}">{{\App\Enums\SharedFlgEnum::LINKED->value == $noshiDtl->shared_flg?'済':'未'}}</td>
                            <td class="text-center">
    							<input type="checkbox" class="shared_flg" name="shared_flg[]" value="{{$noshiDtl['t_order_dtl_noshi_id']}}"></label>
                            </td>
                            <td class="text-center">
                                <input type="radio" class="copy_from" name="copy_from" value="{{$noshiDtl['t_order_dtl_noshi_id']}}">
                            </td>
                            <td class="text-center">
    							<input type="checkbox" class="copy_to" name="copy_to[]" value="{{$noshiDtl['t_order_dtl_noshi_id']}}"></label>
                            </td>
                            <td class="noshi_file_name">
                                {{$noshiDtl->noshi_file_name}}
                            </td>
                            <td class="text-center">
                                @if(empty($noshiDtl->noshiNamingPattern) || (
                                        empty($noshiDtl->noshiNamingPattern->company_name_count) && 
                                        empty($noshiDtl->noshiNamingPattern->section_name_count) && 
                                        empty($noshiDtl->noshiNamingPattern->title_count) && 
                                        empty($noshiDtl->noshiNamingPattern->f_name_count) && 
                                        empty($noshiDtl->noshiNamingPattern->name_count) && 
                                        empty($noshiDtl->noshiNamingPattern->ruby_count)
                                    )
                                )
                                @else
                                <button class="btn btn-sm btn-default action-create" data-id="{{$noshiDtl['t_order_dtl_noshi_id']}}" type="button">生成</button>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-default action-clear" data-id="{{$noshiDtl['t_order_dtl_noshi_id']}}" type="button">クリア</button>
                            </td>
                            <td>
                                @if($noshiDtl->orderDtl)
                                {{$noshiDtl->orderDtl->sell_cd}}
                                @endif
                            </td>
                            <td class="text-right">
                                {{$noshiDtl->count}}
                            </td>
                            <td>
                                @if($noshiDtl->noshiDetail && $noshiDtl->noshiDetail->noshiFormat)
                                {{ $noshiDtl->noshiDetail->noshiFormat->noshi_format_name}}
                                @endif
                            </td>
                            <td>
                                {{$noshiDtl->omotegaki}}
                            </td>
                            <td>
                                @for($i=1;$i<=5;$i++)
                                @if($noshiDtl['company_name'.$i] != "")
                                {{$noshiDtl['company_name'.$i]}}&nbsp;
                                @endif
                                @if($noshiDtl['section_name'.$i] != "")
                                {{$noshiDtl['section_name'.$i]}}&nbsp;
                                @endif
                                @if($noshiDtl['title'.$i] != "")
                                {{$noshiDtl['title'.$i]}}&nbsp;
                                @endif
                                @if($noshiDtl['firstname'.$i] != "")
                                {{$noshiDtl['firstname'.$i]}}&nbsp;
                                @endif
                                @if($noshiDtl['name'.$i] != "")
                                {{$noshiDtl['name'.$i]}}&nbsp;
                                @endif
                                @if($noshiDtl['ruby'.$i] != "")
                                {{$noshiDtl['ruby'.$i]}}&nbsp;
                                @endif
                                @endfor
                            </td>
                            <td>
                                @if($noshiDtl->noshi && $noshiDtl->noshi)
                                {{$noshiDtl->noshi->noshi_type}}
                                @endif
                            </td>
                            <td>
                                @if($noshiDtl->noshiNamingPattern)
                                {{$noshiDtl->noshiNamingPattern->pattern_name}}
                                @endif
                            </td>
                            <td>
                                @if(!empty($viewExtendData['attachment_item_group_name'][$noshiDtl->attachment_item_group_id]))
                                {{$viewExtendData['attachment_item_group_name'][$noshiDtl->attachment_item_group_id]}}
                                @endif
                            </td>
                            <td>
                               {{$noshiDtl->t_order_hdr_id}}
                            </td>
                            <td>
                               {{empty($noshiDtl->orderHdr->order_datetime)?'':date('Y/m/d',strtotime($noshiDtl->orderHdr->order_datetime))}}
                            </td>
                            <td>
                               {{$noshiDtl->orderHdr->m_cust_id}}
                            </td>
                            <td>
                               {{$noshiDtl->orderHdr->order_name}}
                            </td>
                            <td>
                               {{$noshiDtl->orderDestination->destination_name}}
                            </td>
                            <td class="text-center" style="font-size:x-large;">
                               {{$noshiDtl->orderHdr->order_name == $noshiDtl->orderDestination->destination_name?'○':'×'}}
                            </td>
                            <td>
                               {{empty($noshiDtl->orderDestination->deli_plan_date)?'':date('Y/m/d',strtotime($noshiDtl->orderDestination->deli_plan_date))}}
                            </td>
                            <td>
                               {{empty($noshiDtl->orderDestination->deli_hope_date)?'':date('Y/m/d',strtotime($noshiDtl->orderDestination->deli_hope_date))}}
                            </td>
                            <td>
                               {{$noshiDtl->orderHdr->ec_order_num}}
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </table>
            </div>
            @include('common.elements.paginator_footer')
        </div>
        @endif
	</div>
    <button class="btn btn-success btn-lg u-mt--sm action-check-linkage" type="button">まとめて確認</button>
    <button class="btn btn-success btn-lg u-mt--sm action-check-create" type="button">まとめて生成</button>
    <button class="btn btn-success btn-lg u-mt--sm action-check-shared" type="button">まとめて共有</button>
</div>
</form>

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/order/base/app.css') }}">
@endpush

@push('js')
<script src="{{ esm_internal_asset('js/order/gfh_1207/NEOFM0210.js') }}"></script>
@endpush

@endsection