<div><span class="pagetitle">{words:index of} {info:path_nav} {words:folder}</span></div>
<a name="top"></a>
	<table class="tablebg2 autoindex_table" cellspacing="1" width="100%">
	<thead>
	<tr height="160">
		  <th id="quick-links" class="autoindex_th stat-block online-list">
		   <a class="plain_link" href="{sort:filename}" title="{words:sort by} {words:file}">{words:file}</a>
		  </th>
		  
		  {if:download_count}
		  <th id="quick-links" class="row2 table1 topicdetails autoindex_td_right stat-block online-list responsive-hide">
		   <a class="plain_link" href="{sort:downloads}" title="{words:sort by} {words:downloads}">{words:downloads}</a>
		  </th>
		  {end if:download_count}
		  
		  <th id="quick-links" class="row2 table1 topicdetails autoindex_td_right  stat-block online-list responsive-hide">
		   <a class="plain_link" href="{sort:size}" title="{words:sort by} {words:size}">{words:size}</a>
		  </th>
		  
		  <th id="quick-links" class="row2 table1 topicdetails autoindex_td_right stat-block online-list responsive-show">
		   <a class="plain_link" href="{sort:m_time}" title="{words:sort by} {words:date}">{words:date}</a>
		  </th>
		  
		  {if:description_file}
		  <th id="quick-links" class="autoindex_th stat-block online-list">
		   <a class="plain_link" href="{sort:description}" title="{words:sort by} {words:description}">{words:description}</a>
		  </th>
		  {end if:description_file}
	</tr>
