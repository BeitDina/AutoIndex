<%
    ' Close the loop iterating records
    fp_iCount = fp_iCount + 1
    fp_rs.MoveNext
Loop

if fp_fShowNavbar then 
    if fp_fTableFormat then 
	if fp_iDisplayCols < 1 then fp_iDisplayCols = 16
        Response.Write "<TR><TD ALIGN=LEFT VALIGN=MIDDLE COLSPAN=" & CStr(fp_iDisplayCols) & ">"
    end if

    Response.Write "<FORM NAME=""" & fp_sFormName & """ ACTION=""" & Replace(fp_sPagePath," ","%20") & """ TARGET=""_self"" METHOD=POST>"

    if fp_iAbsPage > 1 then 
        fp_sType = "Submit" 
        fp_sLabel = fp_sFirstLabel
    else
        fp_sType = "Button"
        fp_sLabel = fp_sDashLabel
    end if
    Response.Write "<NOBR><INPUT TYPE=" & fp_sType & " NAME=""" & fp_sFormKey & """ VALUE=""" & fp_sLabel & """>"
    if fp_iAbsPage > 1 then fp_sLabel = fp_sPrevLabel
    Response.Write "<INPUT TYPE=" & fp_sType & " NAME=""" & fp_sFormKey & """ VALUE=""" & fp_sLabel & """>"
    if fp_iAbsPage < fp_rs.PageCount then 
        fp_sType = "Submit" 
        fp_sLabel = fp_sNextLabel
    else 
        fp_sType = "Button"
        fp_sLabel = fp_sDashLabel
    end if
    Response.Write "<INPUT TYPE=" & fp_sType & " NAME=""" & fp_sFormKey & """ VALUE=""" & fp_sLabel & """>"
    if fp_iAbsPage < fp_rs.PageCount then fp_sLabel = fp_sLastLabel
    Response.Write "<INPUT TYPE=" & fp_sType & " NAME=""" & fp_sFormKey & """ VALUE=""" & fp_sLabel & """>"

    Response.Write "  [" & fp_iAbsPage & "/" & fp_rs.PageCount & "]</NOBR>"

    ' remember names and values used in query
    for each fp_sKey in fp_dictInputs
        fp_sVal = fp_dictInputs.Item(fp_sKey)
        Response.Write "<INPUT TYPE=HIDDEN NAME=""" & fp_sKey & """ VALUE=""" & fp_sVal & """>"
    next

    Response.Write "</FORM>"

    if fp_fTableFormat then 
        Response.Write "</TD></TR>"
    end if
end if

if IsObject(fp_rs) then 
	FP_Close(fp_rs)
	FP_Close(fp_conn)
end if

set fp_dictInputs = Nothing

set fp_rs = Nothing
set fp_cmd = Nothing
set fp_conn = Nothing

FP_RestoreLocaleForPage

%>
