<thead> </thead>
</tbody>
 <tbody class="forabg">
 <tr class="{file:tr_class}">
 
  <td class="row1 table1 autoindex_td">
   {file:if:is_file} /* if it is a file, make a direct link */
   <a class="autoindex_a" href="{file:parent_dir}{file:filename}">
   {end if}
   {file:if:is_dir} /* otherwise, for directories, display the folder with autoindex */
   <a class="autoindex_a" href="{file:link}">
   {end if}
    {if:icon_path}<img width="32" height="32" alt="[{file:file_ext}]" src="{file:icon}" />{end if:icon_path}
    {file:filename}
   </a> {file:thumbnail}{file:new_icon}{file:md5_link}{file:delete_link}{file:rename_link}{file:edit_description_link}{file:ftp_upload_link}
  </td>
  
  {if:download_count}
  <td class="row2 table1 topicdetails autoindex_td_right  responsive-hide">
  {file:downloads}
  </td>
  {end if:download_count}
  
  <td class="row2 table1 topicdetails autoindex_td_right responsive-hide">
   {file:size}
  </td>
	
  <td class="row2 table1 topicdetails autoindex_td_right responsive-show">
   {file:date}
  </td>
	
	{if:description_file}
	<td class="row2 table1 topicdetails autoindex_td">
	{file:description}
   </td>  
  {end if:description_file}
 
</tr>
</tbody>
