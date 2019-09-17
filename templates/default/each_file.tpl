
 <tr class="{file:tr_class}">
  <td class="autoindex_td">
   <a class="autoindex_a" href="{file:link}">
    {if:icon_path}<img width="16" height="16" alt="[{file:file_ext}]" src="{file:icon}" />{end if:icon_path}
    {file:filename} {file:thumbnail}
   </a>{file:new_icon}{file:md5_link}{file:delete_link}{file:rename_link}{file:edit_description_link}{file:ftp_upload_link}
  </td>
  {if:download_count}
  <td class="autoindex_td_right">
  {file:downloads}
  </td>
  {end if:download_count}
  <td class="autoindex_td_right">
   {file:size}
  </td>
  <td class="autoindex_td_right">
   {file:date}
  </td>
  {if:description_file}
  <td class="autoindex_td">
  {file:description}
  </td>
  {end if:description_file}
 </tr>