$(function() {
    $('.datepicker').datetimepicker({format: 'yyyy-mm-dd hh:ii',language:'zh-CN'});
    var pagerOption={
        visiblePages: 8,
        onPageClick: function (event, page) {
            $('input[name=page]').val(page);
            $('.search-form').trigger('submit');
        }
    };
    pagerOption.totalPages=parseInt($('#total_page').val());
    pagerOption.startPage=parseInt($('input[name=page]').val());
    $('.js-pager').twbsPagination(pagerOption);
    $('.js-pager-bottom').twbsPagination(pagerOption);
    $('.search-form button[type=submit]').click(function()
    {
        $('.search-form input[name=page]').val(1);
    });
    if ($('.search-form').length > 0) 
    {
        var str =decodeURI(window.location.search.replace('?',''));
        var kvarr = str.split('&');
        for (var i = 0; i < kvarr.length; i++) {
            kv = kvarr[i].split('=');
            var node = $("[name='" + kv[0] + "']");
            node.each(function() {
                inputFill(this, kv[1]);
            });
        }
    }
    $('select[data-val]').each(function()
    {
        var value=$(this).data('val');
        if($.trim(value)!=''){
            $(this).find('option[value='+value+']').attr('selected',true);
        }
    });
    $('input[data-val]').each(function()
    {
        if(this.value===$(this).data('val'))
        {
            $(this).attr('checked',true).trigger('change');
        }
    });
    $('.js-ajaxform').validator(function(form){
        $(form).ajaxSubmit({'success':function(r){
            var resp = $.parseJSON(r);
            if(resp.error===0 && resp.redirect !== undefined)
            {
                window.location.href=resp.redirect;
            }else
            {
                bootbox.alert(resp.message);
            }
        }});
    });
    $('.ajax-post').click(function(){
        var href = this.href;
        bootbox.confirm($(this).data('confirm'), function(sure){
            if(sure){
               $.post(href,function(r){
                    var resp = $.parseJSON(r);
                    if(resp.error===0)
                    {
                        window.setTimeout(function(){window.location.reload()},3000);
                    }else
                    {
                        bootbox.find('.bootbox-body').html(resp.message);
                    }
               });
            }
        });
        return false;
    });

    $('.batchpost').click(function(){
        var ids=[];
        $('input[name="ids[]"]').each(function(){
            if(this.checked){
                ids.push(this.value);
            }
        });
        $.post(this.href, {ids: ids}, function(r) {
            console.log(r);
            var resp = $.parseJSON(r);
            if (resp.error === 0)
            {
                window.location.reload();
            } else {
                alert(resp.message);
            }
        });
        return false;
    });
    $('.checkall').click(function(){
        if(this.checked){
            $('input[name="ids[]"]').each(function(){
                this.checked = !this.checked;
            });
        }
    });
    
});

$(document).delegate('.cardpic-remove','click',function(){
    $(this).parent().detach();
});
function inputFill(node, val) {
    var tagName = node.tagName.toLowerCase();
    if (tagName === 'input') 
    {
        if (node.type === 'radio' || node.type === 'checkbox') 
        {
            if (node.value === val) 
            {
                node.checked = true;
            }
        } else
        {
            node.value = val;
        }
    } else if (tagName === 'textarea') 
    {
        node.value = val;
    } else if (tagName === 'select') 
    {
        $(node).find('option').each(function() 
        {
            if (this.value == val) 
            {
                $(this).attr('selected', true);
            }
        });
    }
}


