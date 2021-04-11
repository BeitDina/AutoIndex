
<%

FP_SetLocaleForPage

' determine whether or not to provide navigation controls
if fp_iPageSize > 0 then
	fp_fShowNavbar = True
else
	fp_fShowNavbar = False
end if

fp_sPagePath = Request.ServerVariables("PATH_INFO")
fp_sEnvKey = fp_sPagePath & "#fpdbr_" & fp_iRegion
fp_sFormName = "fpdbr_" & CStr(fp_iRegion)
fp_sFormKey = fp_sFormName & "_PagingMove"

fp_sInputs = fp_sDefault

fp_sFirstLabel = "  |<  "
fp_sPrevLabel  = "   <  "
fp_sNextLabel  = "  >   "
fp_sLastLabel  = "  >|  "
fp_sDashLabel  = "  --  "

if not IsEmpty(Request(fp_sFormKey)) then
	fp_sMoveType = Request(fp_sFormKey)
else
    fp_sMoveType = ""
end if

fp_iCurrent=1
fp_fError=False
fp_bBlankField=False
Set fp_dictInputs = Server.CreateObject("Scripting.Dictionary")

fp_sQry = FP_ReplaceQuoteChars(fp_sQry)

' replace any input parameters in query string
Do While (Not fp_fError) And (InStr(fp_iCurrent, fp_sQry, "::") <> 0)
	' found a opening quote, find the close quote
	fp_iStart = InStr(fp_iCurrent, fp_sQry, "::")
	fp_iEnd = InStr(fp_iStart + 2, fp_sQry, "::")
	If fp_iEnd = 0 Then
		fp_fError = True
		Response.Write "<B>Database Results Error: mismatched parameter delimiters</B>"
	Else
		fp_sField = Mid(fp_sQry, fp_iStart + 2, fp_iEnd - fp_iStart - 2)
		fp_sValue = Request.Form(fp_sField)
		if len(fp_sValue) = 0 then fp_sValue = Request.QueryString(fp_sField)

		' if the named form field doesn't exist, make a note of it
		If (len(fp_sValue) = 0) Then
			fp_iStartField = InStr(fp_sDefault, fp_sField & "=")
			if fp_iStartField > 0 then
				fp_iStartField = fp_iStartField + len(fp_sField) + 1
				fp_iEndField = InStr(fp_iStartField,fp_sDefault,"&")
				if fp_iEndField > 0 then
					fp_sValue = Mid(fp_sDefault,fp_iStartField,fp_iEndField - fp_iStartField)
				else
					fp_sValue = Mid(fp_sDefault,fp_iStartField)
				end if
			end if
		End If

		' remember names and values used in query
		if not fp_dictInputs.Exists(fp_sField) then
			fp_dictInputs.Add fp_sField, fp_sValue
		end if

		' this next finds the named form field value, and substitutes in
		' doubled single-quotes for all single quotes in the literal value
		' so that SQL doesn't get confused by seeing unpaired single-quotes
		If (Mid(fp_sQry, fp_iStart - 1, 1) = """") Then
			fp_sValue = Replace(fp_sValue, """", """""")
		ElseIf (Mid(fp_sQry, fp_iStart - 1, 1) = "'") Then
			fp_sValue = Replace(fp_sValue, "'", "''")
		End If

		If (len(fp_sValue) = 0) Then fp_bBlankField = True

		fp_sQry = Left(fp_sQry, fp_iStart - 1) & fp_sValue & Right(fp_sQry, Len(fp_sQry) - fp_iEnd - 1)
		
		' Fixup the new current position to be after the substituted value
		fp_iCurrent = fp_iStart + Len(fp_sValue)
	End If
Loop

' establish connection
If Not fp_fError Then
	if Application(fp_sDataConn & "_ConnectionString") = "" then
		Err.Description = "The database connection named '" & fp_sDataConn & "' is undefined.<br><br>"
		Err.Description = Err.Description & "This problem can occur if:<br>"
		Err.Description = Err.Description & "* the connection has been removed from the web<br>"
		Err.Description = Err.Description & "* the file 'global.asa' is missing or contains errors<br>"
		Err.Description = Err.Description & "* the root folder does not have Scripting permissions enabled<br>"
		Err.Description = Err.Description & "* the web is not marked as an Application Root<br>"
		fp_fError = True
	end if
	if Not fp_fError then
		set fp_conn = Server.CreateObject("ADODB.Connection")
		fp_conn.ConnectionTimeout = Application(fp_sDataConn & "_ConnectionTimeout")
		fp_conn.CommandTimeout = Application(fp_sDataConn & "_CommandTimeout")
		fp_sConn = Application(fp_sDataConn & "_ConnectionString")
		fp_sUid = Application(fp_sDataConn & "_RuntimeUserName")
		fp_sPwd = Application(fp_sDataConn & "_RuntimePassword")
		Err.Clear
		FP_OpenConnection fp_conn, fp_sConn, fp_sUid, fp_sPwd, Not(fp_fCustomQuery)
		if Err.Description <> "" then fp_fError = True
	end if
	if Not fp_fError then
		set fp_cmd = Server.CreateObject("ADODB.Command")
		fp_cmd.CommandText = fp_sQry
		fp_cmd.CommandType = fp_iCommandType
		set fp_cmd.ActiveConnection = fp_conn
		set fp_rs = Server.CreateObject("ADODB.Recordset")
		set fp_rs.Source = fp_cmd
		If fp_iCommandType = 4 Then
			fp_cmd.Parameters.Refresh
			Do Until Len(fp_sInputs) = 0
				fp_iLoc = InStr(fp_sInputs,"=")
				if fp_iLoc = 0 then exit do
				fp_sKey = Left(fp_sInputs,fp_iLoc - 1)
				fp_sInputs = Mid(fp_sInputs,fp_iLoc + 1)
				fp_iLoc = InStr(fp_sInputs,"&")
				if fp_iLoc = 0 then
					fp_sInpVal = fp_sInputs
					fp_sInputs = ""
				else
					fp_sInpVal = Left(fp_sInputs,fp_iLoc - 1)
					fp_sInputs = Mid(fp_sInputs,fp_iLoc + 1)
				end if			
				fp_sVal = Request.Form(fp_sKey)
				if len(fp_sVal) = 0 then fp_sVal = Request.QueryString(fp_sKey)
				if len(fp_sVal) = 0 then fp_sVal = fp_sInpVal
				fp_pType = fp_cmd.Parameters(fp_sKey).Type
				select case fp_pType
					case 129, 200, 201, 130, 202, 203 ' adChar, adVarChar, adLongVarChar, adWChar, adVarWChar, adLongVarWChar
						fp_cmd.Parameters(fp_sKey).Size = Len(fp_sVal) + 1
					case else
						' do nothing
				end select

				' remember names and values used in query
				if not fp_dictInputs.Exists(fp_sKey) then
					fp_dictInputs.Add fp_sKey, fp_sVal
				end if

				fp_cmd.Parameters(fp_sKey) = fp_sVal
			Loop
		End If
		If fp_iMaxRecords <> 0 Then fp_rs.MaxRecords = fp_iMaxRecords

		FP_SetCursorProperties(fp_rs)

		FP_OpenRecordset(fp_rs)
	end if

	If Err.Description <> "" Then
		if fp_fTableFormat then
			Response.Write "<tr><td colspan=" & fp_iDisplayCols & " color=#000000 bgcolor=#ffff00>"
		end if
		Response.Write "<tt>"
		Response.Write "<b>Database Results Error</b><br>"
		if Not fp_fError then
			Response.Write "<i>Description:</i> " & Err.Description & "<br>"
			Response.Write "<i>Number:</i> " & Err.Number & " (0x" & Hex(Err.Number) & ")<br>"
			Response.Write "<i>Source:</i> " & Err.Source & "<br>"
		else
			Response.Write Err.Description
		end if
		if fp_bBlankField Then
			Response.Write "<br>One or more form fields were empty."
			Response.Write " You should provide default values for all form fields that are used in the query."
		End If
		Response.Write "</tt>"
		if fp_fTableFormat then
			Response.Write "</td></tr>"
		end if
		fp_fError = True
	Else
		' Check for the no-record case
		if fp_rs.State <> 1 then
			fp_fError = True
			Response.Write fp_sNoRecords
		ElseIf fp_rs.EOF And fp_rs.BOF Then
			fp_fError = True
			Response.Write fp_sNoRecords
		end if
    end if
end if

' determine whether or not provider supports Absolute Positioning
if not fp_fError then
	if IsObject(fp_rs) and not(fp_rs.Supports(&H00004000)) then 
		fp_iPageSize = 0
		fp_fShowNavbar = False
	end if
end if

' move to correct position in result set
if not fp_fError then

    if fp_iPageSize > 0 then
		fp_iAbsPage = 1
		fp_sVal = Session(fp_sEnvKey)
		if fp_sVal <> "" then 
			fp_iAbsPage = CInt(fp_sVal)
		end if

		fp_rs.PageSize = fp_iPageSize
		if fp_iAbsPage > fp_rs.PageCount then fp_iAbsPage = fp_rs.PageCount
		fp_rs.AbsolutePage = fp_iAbsPage
		if fp_rs.PageCount = 1 then fp_fShowNavbar = False

		select case fp_sMoveType
			case ""
				' do nothing
			case fp_sFirstLabel
				fp_rs.AbsolutePage = 1
			case fp_sPrevLabel
				if fp_rs.AbsolutePage > 1 then fp_rs.AbsolutePage = fp_rs.AbsolutePage - 1
			case fp_sNextLabel
				if fp_rs.AbsolutePage < fp_rs.PageCount then fp_rs.AbsolutePage = fp_rs.AbsolutePage + 1
			case fp_sLastLabel
				fp_rs.AbsolutePage = fp_rs.PageCount
			case else
				' do nothing
		end select

		fp_iAbsPage = fp_rs.AbsolutePage
		Session(fp_sEnvKey) = fp_iAbsPage
    end if

end if

if fp_fError then fp_fShowNavbar = False

fp_iCount = 0
Do
    if fp_fError then exit do
    if fp_rs.EOF then exit do
    if fp_iPageSize > 0 And fp_iCount >= fp_rs.PageSize then exit do
    if fp_iMaxRecords > 0 And fp_iCount >= fp_iMaxRecords then 
	' MaxRecords didn't work; exit loop
	fp_fShowNavbar = False
	exit do
    end if
%>

