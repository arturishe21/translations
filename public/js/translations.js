

$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);

    if (results==null){
        return null;
    }
    else{
        return results[1] || 0;
    }
}


var Trans = {

    show_list: function(page,  count_show){
        Trans.load_ajax("show");

        count_show = typeof count_show !== 'undefined' ? count_show : '20';

        url_page = '?page=' + page+'&count_show='+count_show;

        $.ajax({
            url : url_page,
            dataType: 'html'
        }).done(function (data) {

            $('.table_center').html(data);
            window.history.pushState(url_page, '', url_page);
            Trans.load_ajax("hide");

        }).fail(function () {
            TableBuilder.showErrorNotification("Что-то пошло не так, попробуйте позже");
        });
    },

    getCreateForm: function(){
        $("#modal_form").modal('show');
        Trans.preloadPage();
        $.post("/admin/translations/create_pop", {},
            function (data) {
                $("#modal_form .modal-content").html(data);
            });
    },

    //yandex autotranslate
    getTranslate: function(phrase)
    {
        $( ".langs_input" ).each(function( index ) {
            lang = $(this).attr("name");
            if (phrase && lang) {
                $(".langs_input[name="+lang+"]").attr("placeholder","Переводит...");

                $.post("/admin/translations_cms/translate", {phrase: phrase, lang: lang},
                    function (data) {

                        $(".langs_input[name=" + data.lang + "]").attr("placeholder", "")

                        if (data.text) {
                            $(".langs_input[name=" + data.lang + "]").val(data.text);
                        }
                    }, "json");
            }
        });
    },

    AddRec : function()
    {
        $.post("/admin/translations/add_record", {data:$('#form_page').serialize() },
            function (data) {
                if (data.status == "ok") {

                    TableBuilder.showSuccessNotification(data.ok_messages);

                    $("#modal_form").modal('hide');
                    Trans.show_list(1);
                } else {
                    var mess_error = ""
                    $.each( data.errors_messages, function( key, value ) {
                        mess_error += value+"<br>";
                    });

                    TableBuilder.showErrorNotification(mess_error);
                }
            },"json");
    },

    doDelete : function(this_id_pages)
    {
        $.post("/admin/translations/del_record", {id:this_id_pages },
            function(data){
                Trans.show_list(1);
            });
    },

    preloadPage : function()
    {
        $("#modal_form .modal-content").html('<div id="table-preloader" class="text-align-center"><i class="fa fa-gear fa-4x fa-spin"></i></div>');
    },

    load_ajax : function($show)
    {
        if ($show == "show") {
            $(".load_ajax").show();
        } else {
            $(".load_ajax").hide();
        }
    }

}

$(document).on("change", '[name=dt_basic_length]', function(){
    Trans.show_list("1", $(this).val());
});

$(document).on("submit", '#search_form', function(){
    var search_q = $("[type=search]").val();

    $.get(window.location.pathname, {search_q:search_q, "page":1},
        function(data){
            $('.table_center').html(data);
        });
    return false;
});

$(document).on('click', '.pagination a', function (e) {
    Trans.show_list($(this).attr('href').split('page=')[1]);
    e.preventDefault();
});

$(document).ready(function(){
    $('.lang_change').editable2({
        url: '/admin/translations/change_text_lang',
        type: 'text',
        pk: 1,
        id: "",
        name: 'username',
        title: 'Enter username'
    });
});
