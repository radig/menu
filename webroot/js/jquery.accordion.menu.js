/* 
Simple JQuery menu.
HTML structure to use:

Notes: 

Each menu MUST have a class 'menu' set. If the menu doesn't have this, the JS won't make it dynamic
If you want a panel to be expanded at page load, give the containing LI element the classname 'expand'.
Use this to set the right state in your page (generation) code.

Optional extra classnames for the UL element that holds an accordion:

noaccordion : no accordion functionality
collapsible : menu works like an accordion but can be fully collapsed

<ul class="menu [optional class] [optional class]">
<li><a href="#">Sub menu heading</a>
<ul>
<li><a href="http://site.com/">Link</a></li>
<li><a href="http://site.com/">Link</a></li>
<li><a href="http://site.com/">Link</a></li>
...
...
</ul>
// This item is open at page load time
<li class="expand"><a href="#">Sub menu heading</a>
<ul>
<li><a href="http://site.com/">Link</a></li>
<li><a href="http://site.com/">Link</a></li>
<li><a href="http://site.com/">Link</a></li>
...
...
</ul>
...
...
</ul>

Copyright 2007-2010 by Marco van Hylckama Vlieg

web: http://www.i-marco.nl/weblog/
email: marco@i-marco.nl

Free to use any way you like.
*/


jQuery.fn.initMenu = function() {  
    return this.each(function(){
        $('li.topLevel a', this).addClass('ui-state-default');
        $('li:not(.topLevel)', this).addClass('ui-widget-content');
        $('li:not(.topLevel) a', this).addClass('ui-state-active');
        $('li.expand > .menu-active-item', this).show();
        $('li.expand > .menu-active-item', this).prev().addClass('ui-state-active');
        $('li a', this).hover(
        	function() {
        		$(this).addClass('ui-state-hover');
        	},
        	function() {
        		$(this).removeClass('ui-state-hover');
        	}
        );
        $('li a', this).click(
            function(e) {
                e.stopImmediatePropagation();
                var theElement = $(this).next();
                var parent = this.parentNode.parentNode;
                if($(parent).hasClass('noaccordion')) {
                    $(theElement).slideToggle('normal', function() {
                        if ($(this).is(':visible')) {
                        	//TODO ícone de menu aberto
                        }
                        else {
                        	//TODO ícone de menu fechado
                        }
                    });
                    
                    if($(this).attr('href') == '#')
                    {
                    	return false;
                    }
                }
                else {
                    if(theElement.hasClass('menu-active-item') && theElement.is(':visible')) {
                        if($(parent).hasClass('collapsible')) {
                            $('.acitem:visible', parent).first().slideUp('normal', 
                            function() {
                                $(this).prev().removeClass('ui-state-active');
                            }
                        );
                    }
                }
                if(theElement.hasClass('menu-active-item') && !theElement.is(':visible')) {         
                    $('.menu-active-item:visible', parent).first().slideUp('normal', function() {
                        $(this).prev().removeClass('ui-state-active');
                    });
                    theElement.slideDown('normal', function() {
                        $(this).prev().addClass('ui-state-active');
                    });
                }
            }
        }
    );
});
};

$(document).ready(function() {$('.menu').initMenu();});