<td>
 <a class="autoindex_a" href="{file:link}">
  {file:if:is_dir}{if:icon_path}<img width="16" height="16" src="{file:icon}" alt="{words:thumbnail of} {file:filename}" />{end if:icon_path}{end if} {file:thumbnail}
 <br />{file:filename}</a>{file:new_icon}{file:md5_link}{file:delete_link}{file:rename_link}{file:edit_description_link}{file:ftp_upload_link}
 {if:description_file}<br />{file:description}{end if:description_file}
</td>
{do_every:4}</tr><tr height="160">{end do_every}