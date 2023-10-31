 <tr>
  <th class="autoindex_th stat-block online-list">
   <span class="autoindex_small">{info:total_files} {words:files} - {info:total_folders} {words:folders}</span>
  </th>
  {if:download_count}
  <th class="autoindex_th stat-block online-list">
    <span class="autoindex_small">{words:total downloads}: {info:total_downloads}</span>
  </th>
  {end if:download_count}
  <th class="autoindex_th stat-block online-list">
    <span class="autoindex_small">{words:total size}: {info:total_size}</span>
  </th>
  <th class="autoindex_th stat-block online-list">
   &nbsp;
  </th>
  {if:description_file}
  <th class="autoindex_th stat-block online-list">
   &nbsp;
  </th>
  {end if:description_file}
 </tr>
</table>


<div class="autoindex_small" style="text-align: right;">
Powered by <a class="autoindex_a" href="http://autoindex.sourceforge.net/">Original AutoIndex PHP Script</a>
and  <a class="autoindex_a" href="http://github.com/BeitDina/AutoIndex/">AutoIndex @ Beit Dina</a>
</div>
		/* We request that you do not remove the link to the AutoIndex website.
		   This not only gives respect to the large amount of time given freely by the
		   developer, but also helps build interest, traffic, and use of AutoIndex. */


{if:entries_per_page}
<p>
	{words:page}
	{info:previous_page_link}
	{info:current_page_number}
	{info:next_page_link}
	{words:of} {info:last_page_number}
</p>
{end if:entries_per_page}


{if:archive}
<div class="autoindex_small stat-block online-list" style="text-align: left;"><a class="autoindex_a" href="{info:archive_link}">{words:download directory as tar.gz archive}</a></div>
{end if:archive}


<p></p>
<table class="autoindex_table">
 <tr style="vertical-align: top;">
  {if:search_enabled}
  <td>
   <table><tr class="paragraph">
   <td class="autoindex_td search_box" style="padding: 8px;">
    {if:icon_path}
	<a name="{words:search}"><i class="icon ion-ios-search fa-fw" aria-hidden="true"></i></a>	
	{end if:icon_path}{words:search}:
     {info:search_box}
   </td></tr></table>
  </td>
  {end if:search_enabled}
  {if:use_login_system}
  <td>
   <table>
   <tr class="paragraph">
   <td class="autoindex_td login_box" style="padding: 8px;">
    {if:icon_path}
	<a name="{words:login}"><i class="icon ion-ios-log-in fa-fw" aria-hidden="true"></i><i class="icon ion-ios-log-out fa-fw" aria-hidden="true"></i></a>
	{end if:icon_path}{words:account}:
     {info:login_box}
   </td>
   </tr>
   </table>
  </td>
  {end if:use_login_system}
 </tr>
</table>
<a name="bottom"></a>
</td></tr>
</table>
