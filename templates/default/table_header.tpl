<div>{words:index of} {info:path_nav}</div>
	<table class="tablebg2 autoindex_table" cellspacing="1" width="100%">
	<thead>
	<tr>
		  <th class="autoindex_th">
		   <a class="plain_link" href="{sort:filename}" title="{words:sort by} {words:file}">{words:file}</a>
		  </th>
		  
		  {if:download_count}
		  <th class="row2 table1 topicdetails autoindex_td_right  responsive-hide">
		   <a class="plain_link" href="{sort:downloads}" title="{words:sort by} {words:downloads}">{words:downloads}</a>
		  </th>
		  {end if:download_count}
		  
		  <th class="row2 table1 topicdetails autoindex_td_right  responsive-hide">
		   <a class="plain_link" href="{sort:size}" title="{words:sort by} {words:size}">{words:size}</a>
		  </th>
		  
		  <th class="row2 table1 topicdetails autoindex_td_right responsive-show">
		   <a class="plain_link" href="{sort:m_time}" title="{words:sort by} {words:date}">{words:date}</a>
		  </th>
		  
		  {if:description_file}
		  <th class="autoindex_th">
		   <a class="plain_link" href="{sort:description}" title="{words:sort by} {words:description}">{words:description}</a>
		  </th>
		  {end if:description_file}
	</tr>
