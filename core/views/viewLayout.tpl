{extends file="baseLayout.tpl"}
{block name="full-content"}
<aside id="left-panel">
  <nav>
    <ul class="nav" id="menu1">
    </ul>
  </nav>
  {hook name='displayLeftMenu' container='<nav><ul class="nav" id="hook-menu">[hook]</ul></nav>'}
</aside>
<div class="content" id="main">
<div class="flash alert fade in" id="flash-message" style="display: none;">
  <button type="button" class="close" aria-hidden="true">&times;</button>
</div>
{block name="content"}
{/block}
</div>
{environment}
{/block}

{block name="javascript-bottom" append}
<script>
$(document).ready(function() {
    leftPanelHeight();
    $('#main').on('resize', function() {
        leftPanelHeight();
    });
    $(window).on('resize', function() {
        leftPanelHeight();
    });
    var mdata = {get_environment_id};
    loadMenu('{url_for url="/menu/getmenu/"}', mdata.envid, mdata.subid, mdata.childid);
    $('a.envmenu').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $("#hook-menu").html("");
        loadMenu('{url_for url="/menu/getmenu/"}', $(this).data('menu'), 0, 0);
    });
    $('#flash-message').on('click', 'button.close', function() {
        alertClose();
    });
    $('body').on('click', '#menu1 li', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var target = e.currentTarget;
        $(target).find('ul').collapse('toggle');
        if ($(target).find('i.toggle').hasClass('fa-plus-square-o')) {
            $(target).find('i.toggle').removeClass('fa-plus-square-o');
            $(target).find('i.toggle').addClass('fa-minus-square-o');
        } else {
            $(target).find('i.toggle').removeClass('fa-minus-square-o');
            $(target).find('i.toggle').addClass('fa-plus-square-o');
        }
        
        var targetUrl = $(target).find('a').attr('href');
        if (targetUrl !== undefined) {
            document.location.href = targetUrl;
        }
    });
});
</script>
{/block}
