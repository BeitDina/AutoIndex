<!--
Powered by AutoIndex PHP Script (version {info:version})
Copyright (C) 2002-2007 Justin Hagstrom, 2019-2023 Florin Ciprian Bodin
http://autoindex.sourceforge.net
Page generated in {info:page_time} milliseconds.
-->
<script type="text/javascript">window.jQuery || document.write('\x3Cscript src="{config:assets_path}/javascript/jquery.min.js?assets_version=75">\x3C/script>');</script>
<script type="text/javascript" src="{config:assets_path}/javascript/core.js?assets_version=75"></script>
<script type="text/javascript" src="{config:template}/jquery.cookie.js"></script>
<script type="text/javascript" src="{config:template}/sidebar.js"></script>
<script type="text/javascript" src="{config:template}/jquery.collapse.js"></script>
<script type="text/javascript">
		$(".forumlist").collapse({show: function() {
                this.animate({
                    opacity: 'toggle',
                    height: 'toggle'
                }, 300);
            },
            hide : function() {                   
                this.animate({
                    opacity: 'toggle',
                    height: 'toggle'
                }, 300);
            }
        });
</script>
<script type="text/javascript">
		(function($) {
			var $fa_cdn = $('head').find('link[rel="stylesheet"]').first(),
				$span = $('<span class="fa" style="display:none"></span>').appendTo('body');
			if ($span.css('fontFamily') !== 'FontAwesome') {
				$fa_cdn.after('<link href="{config:assets_path}/css/font-awesome.min.css" rel="stylesheet">');
				$fa_cdn.remove();
			}
			$span.remove();
		})(jQuery);
</script>

<script src="{config:assets_path}/cookieconsent/cookieconsent.min.js?assets_version=75"></script>
<script>
		if (typeof window.cookieconsent === "object") {
			window.addEventListener("load", function() {
				window.cookieconsent.initialise({
				"palette": {
					"popup": {
						"background": "#0F538A"
					},
					"button": {
						"background": "#E5E5E5"
					}
				},
				"theme": "classic",
				"content": {
					"message": "{info:message}",
					"dismiss": "{info:dismiss}",
					"link": "{info:link}",
					"href": "{info:href}""
				}
				});
			});
		}
</script>

<script language="javascript" type="text/javascript" src="{config:template}jquery.min.js?assets_version=75"></script>
<script language="javascript" type="text/javascript" src="{config:template}core.js?assets_version=75"></script>
<script language="javascript" type="text/javascript" src="{config:template}ajax.js?assets_version=75"></script>
<script language="javascript" type="text/javascript" src="{config:template}forum_fn.js?assets_version=75"></script>
<script language="javascript" type="text/javascript" src="{config:template}collapsiblecategories.js?assets_version=75"></script>

{info:statinfo}

<script>$('.tables1 br').remove();</script>
</body>
</html>
