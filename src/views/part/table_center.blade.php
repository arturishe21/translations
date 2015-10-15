
   <script>
     $(".breadcrumb").html("<li><a href='/admin'>{{__cms('Главная')}}</a></li> <li>{{__cms($title)}}</li>");
     $("title").text("{{__cms($title)}} - {{{ __cms(Config::get('builder::admin.caption')) }}}");
   </script>

<!-- MAIN CONTENT -->
         <div class="jarviswidget jarviswidget-color-blue " id="wid-id-4" data-widget-editbutton="false" data-widget-colorbutton="false">
                        <header>
                            <span class="widget-icon"> <i class="fa  fa-file-text"></i> </span>
                            <h2> {{__cms($title)}} </h2>
                        </header>
                         <div class="table_center no-padding">

                  <div class="dt-toolbar">
                      <div class="col-xs-12 col-sm-6">
                          <div id="dt_basic_filter" class="dataTables_filter">
                           <form action="" method="get" id="search_form">
                              <label>
                                  <span class="input-group-addon">
                                  <i class="glyphicon glyphicon-search"></i>
                                  </span>
                                  <input class="form-control" name="search_q" type="search" value="{{$search_q}}" aria-controls="dt_basic">
                              </label>
                             </form>
                          </div>
                      </div>
                      <div class="col-sm-6 col-xs-12 hidden-xs">
                          <div id="dt_basic_length" class="dataTables_length">
                              <label>

                                  <select class="form-control" name="dt_basic_length" aria-controls="dt_basic">

                                  @foreach(Config::get('translations::config.show_count') as $val)
                                      <option value="{{$val}}"
                                        @if($val==$count_show)
                                         selected
                                        @endif
                                       >{{$val}}</option>
                                  @endforeach
                                  </select>
                              </label>
                          </div>
                     </div>
                </div>

                  <table class="table  table-hover table-bordered " id="sort_t">
                     <thead>
                         <tr>

                             <th style="width: 25%">{{__cms('Фраза')}}</th>
                             <th>{{__cms('Код')}}</th>
                             <th>{{__cms('Переводы')}}</th>
                             <th style="width: 50px">
                                 <a class="btn btn-sm btn-success" categor="0" onclick="Trans.getCreateForm(this);">
                                   <i class="fa fa-plus"></i> {{__cms("Создать")}}
                                 </a>

                             </th>
                         </tr>
                     </thead>
                     <tbody >

                   @forelse($data as $k=>$el )
                        <tr class="tr_{{$el->id}} " id_page="{{$el->id}}">

                            <td style="text-align: left;">
                                {{$el->phrase}}
                            </td>
                            <td>__("{{$el->phrase}}")</td>

                            <td style="text-align: left">
                                 <?
                                 $trans = $el->getTrans();
                                 ?>
                                  @foreach($langs as $k_lang=>$el_lang)
                                    <p>
                                         <img class="flag flag-{{$el_lang}}" style="margin-right: 5px">
                                         <a data-type="textarea" class="lang_change" data-pk="{{$el->id}}"  data-name="{{$el_lang}}" data-original-title="{{__cms('Язык')}}: {{$el_lang}}">{{$trans[$el_lang] or ""}}</a>
                                     </p>
                                  @endforeach
                            </td>
                            <td>
                                <div class="btn-group hidden-phone pull-right">
                                    <a class="btn dropdown-toggle btn-xs btn-default"  data-toggle="dropdown"><i class="fa fa-cog"></i> <i class="fa fa-caret-down"></i></a>
                                    <ul class="dropdown-menu pull-right" id_rec ="{{$el->id}}">
                                         <li>
                                             <a onclick="Trans.doDelete({{$el->id}});"><i class="fa red fa-times"></i> {{__cms('Удалить')}}</a>
                                         </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                  <tr>
                     <td colspan="5"  class="text-align-center">
                         {{__cms('Пусто')}}
                      </td>
                 </tr>
            @endforelse
            </tbody>
        </table>

              <div class="dt-toolbar-footer">
                  <div class="col-sm-6 col-xs-12 hidden-xs">
                      <div id="dt_basic_info" class="dataTables_info" role="status" aria-live="polite">
                        {{__cms('Показано')}}
                      <span class="txt-color-darken listing_from">{{$data->getFrom()}}</span>
                        -
                      <span class="txt-color-darken listing_to">{{$data->getTo()}}</span>
                        {{__cms('из')}}
                      <span class="text-primary listing_total">{{$data->getTotal()}}</span>
                        {{__cms('записей')}}
                      </div>
                  </div>
                  <div class="col-xs-12 col-sm-6">
                    <div id="dt_basic_paginate" class="dataTables_paginate paging_simple_numbers">
                        {{$data->links()}}
                    </div>
                  </div>
              </div>
            </div>
      </div>

    <!-- END MAIN CONTENT -->
<div id="modal_wrapper">
   @include("translations::part.pop_trans_add")
</div>
<div class='load_ajax'></div>
<script src="{{asset('packages/vis/translations/js/translations.js')}}"></script>
