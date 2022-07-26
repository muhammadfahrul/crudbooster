@extends('crudbooster::admin_template')

@section('content')

@if($index_statistic)
<div id='box-statistic' class='row'>
    @foreach($index_statistic as $stat)
    @if (isset($stat['sum_row_key']))
        @php
            $stat['count'] = collect(collect($result)->all()['data'])->sum($stat['sum_row_key']);
            if (isset($stat['number_format'])) {
                $stat['count'] = number_format((int)$stat['count'],0,',','.');
            }
        @endphp
    @endif
    <div class="{{ ($stat['width'])?:'col-sm-3' }}">
        <div class="small-box bg-{{ $stat['color']?:'red' }}">
            <div class="inner">
                <h3>{{ $stat['count'] }}</h3>
                <p>{{ $stat['label'] }}</p>
            </div>
            <div class="icon">
                <i class="{{ $stat['icon'] }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(!is_null($pre_index_html) && !empty($pre_index_html))
{!! $pre_index_html !!}
@endif


@if(g('return_url'))
<p><a href='{{g("return_url")}}'><i class='fa fa-chevron-circle-{{ cbLang('left') }}'></i>
        &nbsp; {{cbLang('form_back_to_list',['module'=>urldecode(g('label'))])}}</a></p>
@endif

@if($parent_table)
<div class="box box-default">
    <div class="box-body table-responsive no-padding">
        <table class='table table-bordered'>
            <tbody>
                <tr class='active'>
                    <td colspan="2"><strong><i class='fa fa-bars'></i> {{ ucwords(urldecode(g('label'))) }}</strong></td>
                </tr>
                @foreach(explode(',',urldecode(g('parent_columns'))) as $c)
                <tr>
                    <td width="25%"><strong>
                            @if(urldecode(g('parent_columns_alias')))
                            {{explode(',',urldecode(g('parent_columns_alias')))[$loop->index]}}
                            @else
                            {{ ucwords(str_replace('_',' ',$c)) }}
                            @endif
                        </strong></td>
                    <td> {{ $parent_table->$c }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
<div class="box">
    @if($button_add && CRUDBooster::isCreate())
    <div style="padding-top: 10px; padding-left: 10px">
        <a href="{{ CRUDBooster::mainpath('add').'?return_url='.urlencode(Request::fullUrl()).'&parent_id='.g('parent_id').'&parent_field='.$parent_field }}" id='btn_add_new_data' class="btn btn-sm btn-primary" style="width: 100px; height: 30px;  border-radius: 16px;" title="{{cbLang('action_add_data')}}">
            <i class="fa fa-plus-circle"></i> {{cbLang('action_add_data')}}
        </a>
    </div>
    @endif
    <div class="box-header">
        <div class="box-tools pull-{{ cbLang('left') }}" style="position: relative; margin-left: 10px">
            @if($button_bulk_action && ( ($button_delete && CRUDBooster::isDelete()) || $button_selected) )
                <div class="pull-{{ cbLang('left') }}" style="margin-right: 3px;">
                    <div class="selected-action" style="display:inline-block;position:relative;">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i
                                    class='fa fa-check-square-o'></i> {{cbLang("button_selected_action")}}
                            <span class="fa fa-caret-down"></span></button>
                        <ul class="dropdown-menu">
                            @if($button_delete && CRUDBooster::isDelete())
                                <li><a href="javascript:void(0)" data-name='delete' title='{{cbLang('action_delete_selected')}}'><i
                                                class="fa fa-trash"></i> {{cbLang('action_delete_selected')}}</a></li>
                            @endif

                            @if($button_selected)
                                @foreach($button_selected as $button)
                                    <li><a href="javascript:void(0)" data-name='{{$button["name"]}}' title='{{$button["label"]}}'><i
                                                    class="fa fa-{{$button['icon']}}"></i> {{$button['label']}}</a></li>
                                @endforeach
                            @endif

                        </ul><!--end-dropdown-menu-->
                    </div><!--end-selected-action-->
                </div><!--end-pull-left-->
            @endif
            @if($button_filter)
            <a style="margin-top:-23px" href="javascript:void(0)" id='btn_advanced_filter' data-url-parameter='{{$build_query}}' title='{{cbLang('filter_dialog_title')}}' class="btn btn-sm btn-default {{(Request::get('filter_column'))?'active':''}}">
                <i class="fa fa-filter"></i> {{cbLang("button_filter")}}
            </a>
            @endif
            <form method='get' style="display:inline-block;width: 260px;" action='{{Request::url()}}'>
                <div class="input-group">
                    <input type="text" name="q" value="{{ Request::get('q') }}" class="form-control input-sm pull-{{ cbLang('left') }}" placeholder="{{cbLang('filter_search')}}" />
                    {!! CRUDBooster::getUrlParameters(['q']) !!}
                    <div class="input-group-btn">
                        @if(Request::get('q'))
                        <?php
                        $parameters = Request::all();
                        unset($parameters['q']);
                        $build_query = urldecode(http_build_query($parameters));
                        $build_query = ($build_query) ? "?" . $build_query : "";
                        $build_query = (Request::all()) ? $build_query : "";
                        ?>
                        <button type='button' onclick='location.href="{{ CRUDBooster::mainpath().$build_query}}"' title="{{cbLang('button_reset')}}" class='btn btn-sm btn-warning'><i class='fa fa-ban'></i></button>
                        @endif
                        <button type='submit' class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
            <form method='get' id='form-limit-paging' style="display:inline-block" action='{{Request::url()}}'>
                {!! CRUDBooster::getUrlParameters(['limit']) !!}
                <div class="input-group">
                    <select onchange="$('#form-limit-paging').submit()" name='limit' style="width: 90px;" class='form-control input-sm'>
                        <option {{($limit==5)?'selected':''}} value='5'>Show: 5</option>
                        <option {{($limit==10)?'selected':''}} value='10'>Show: 10</option>
                        <option {{($limit==20)?'selected':''}} value='20'>Show: 20</option>
                        <option {{($limit==25)?'selected':''}} value='25'>Show: 25</option>
                        <option {{($limit==50)?'selected':''}} value='50'>Show: 50</option>
                        <option {{($limit==100)?'selected':''}} value='100'>Show: 100</option>
                        <option {{($limit==200)?'selected':''}} value='200'>Show: 200</option>
                    </select>
                </div>
            </form>

        </div>
        <div class="box-tools pull-{{ cbLang('right') }}" style="position: relative;">

            @if($button_export && CRUDBooster::getCurrentMethod() == 'getIndex')
            <a href="javascript:void(0)" id='btn_export_data' data-url-parameter='{{$build_query}}' title='Export Data' class="btn btn-sm btn-primary btn-export-data">
                <i class="fa fa-upload"></i> {{cbLang("button_export")}}
            </a>
            @endif


        </div>

        <br style="clear:both" />

    </div>
    <div class="box-body table-responsive no-padding">
        @include("crudbooster::default.table")
    </div>
</div>

@if(!is_null($post_index_html) && !empty($post_index_html))
{!! $post_index_html !!}
@endif

@endsection
