<%

Sub FP_SetLocaleForPage
    On Error Resume Next
    Session("FP_OldCodePage") = Session.CodePage
    Session("FP_OldLCID") = Session.LCID
    Err.Clear
    if FP_CodePage <> 0 then
        Session.CodePage = FP_CodePage
        if Err.Number <> 0 then Session.CodePage = Session("FP_OldCodePage")
    end if
    Err.Clear
    if FP_LCID <> 0 then
        Session.LCID = FP_LCID
        if Err.Number <> 0 then Session.LCID = Session("FP_OldLCID")
    end if
End Sub

Sub FP_RestoreLocaleForPage
    On Error Resume Next
    if Session("FP_OldCodePage") <> 0 then
        Session.CodePage = Session("FP_OldCodePage")
    end if
    if Session("FP_OldLCID") <> 0 then
        Session.LCID = Session("FP_OldLCID")
    end if
    Err.Clear
End Sub

Function FP_HTMLEncode(str)

    FP_HTMLEncode = str
    FP_HTMLEncode = Replace(FP_HTMLEncode,"&","^^@^^")
    FP_HTMLEncode = Server.HTMLEncode(FP_HTMLEncode)
    FP_HTMLEncode = Replace(FP_HTMLEncode,"^^@^^","&")

End Function

Function FP_FieldVal(rs, fldname)

    FP_FieldVal = FP_HTMLEncode(FP_Field(rs, fldname))
    if FP_FieldVal = "" then FP_FieldVal = "&nbsp;"

End Function

Function FP_Field(rs, fldname)

    If Not IsEmpty(rs) And Not (rs Is Nothing) and Not IsNull(rs(fldname)) Then 
        Select Case rs(fldname).Type
            Case 128, 204, 205 ' adBinary, adVarBinary, adLongVarBinary
                FP_Field = "[#BINARY#]"
            Case 201, 203 ' adLongVarChar, adLongVarWChar
                if rs(fldname).DefinedSize > 255 then
                    ' check for Access hyperlink fields (only absolute http links)
                    fp_strVal = rs(fldname)
                    fp_idxHash1 = InStr(LCase(fp_strVal),"#http://")
                    if fp_idxHash1 > 0 then
                        fp_idxHash2 = InStr(fp_idxHash1+1,fp_strVal,"#")
                        if fp_idxHash2 > 0 then 
                            ' this is an Access hyperlink; extract the URL part 
                            fp_strVal = Mid(fp_strVal,fp_idxHash1+1)
                            if Right(fp_strVal,1) = "#" then
                                fp_strVal = Left(fp_strVal,Len(fp_strVal)-1)
                            end if
                        end if
                     end if
                     FP_Field = fp_strVal
                else
                     FP_Field = rs(fldname)
                end if
            Case Else
                FP_Field = rs(fldname)
        End Select
    Else
        FP_Field = ""
    End If

End Function

Function FP_FieldHTML(rs, fldname)

    FP_FieldHTML = FP_HTMLEncode(FP_Field(rs, fldname))

End Function

Function FP_FieldURL(rs, fldname)

    FP_FieldURL = Server.URLEncode(FP_Field(rs, fldname))

End Function

Function FP_FieldLink(rs, fldname)

    FP_FieldLink = Replace(FP_Field(rs, fldname), " ", "%20")

End Function

Sub FP_OpenConnection(oConn, sAttrs, sUID, sPWD, fMSAccessReadOnly)

	Dim sTmp
	Dim sConnStr
	Dim fIsAccessDriver

	fIsAccessDriver = (InStr(LCase(sAttrs), "microsoft access driver") > 0)
	sConnStr = FP_RemoveDriverWithDSN(sAttrs)
	sTmp = sConnStr
	
	On Error Resume Next

	If fMSAccessReadOnly And fIsAccessDriver Then

		sTmp = sTmp & ";Exclusive=1;ReadOnly=1"

		Err.Clear
		oConn.Open sTmp, sUID, sPWD
		If Err.Description = "" Then Exit Sub

	End If

	Err.Clear
	oConn.Open sConnStr, sUID, sPWD

End Sub

Function FP_RemoveDriverWithDSN(sAttrs)

	FP_RemoveDriverWithDSN = sAttrs

	sDrv = "driver="
	sDSN = "dsn="
	sLC = LCase(sAttrs)
	if InStr(sLC, sDSN) < 1 then exit function

	idxFirst = InStr(sLC, sDrv)
	if idxFirst < 1 then exit function
	idxBeg = idxFirst + Len(sDrv)
	if Mid(sLC,idxBeg,1) = "{" then 
		idxEnd = InStr(idxBeg, sLC, "}")
		if idxEnd > 0 and Mid(sLC,idxEnd+1,1) = ";" then 
			idxEnd = idxEnd + 1
		end if
	else
		idxEnd = InStr(idxBeg, sLC, ";")
	end if
	if idxEnd < 1 then idxEnd = Len(sLC)
	
	FP_RemoveDriverWithDSN = Left(sAttrs,idxFirst-1) & Mid(sAttrs,idxEnd+1)

End Function

Sub FP_OpenRecordset(rs)
	
	On Error Resume Next
	rs.Open

End Sub

Function FP_ReplaceQuoteChars(sQry)

	Dim sIn
	Dim sOut
	Dim idx

	sIn = sQry
	sOut = ""

	idx = InStr(sIn, "%%")

	Do While (idx > 0)

		sOut = sOut & Left(sIn, idx - 1)
		sIn = Mid(sIn, idx + 2)
		if (Left(sIn,1) = "%") And (Left(sIn,2) <> "%%") then
			sIn = Mid(sIn, 2)
			sOut = sOut & "%"
		end if
		sOut = sOut & "::"

		idx = InStr(sIn, "%%")
		if idx > 0 then
			sOut = sOut & Left(sIn, idx - 1)
			sIn = Mid(sIn, idx + 2)
			sOut = sOut & "::"
			if (Left(sIn,1) = "%") And (Left(sIn,2) <> "%%") then
				sIn = Mid(sIn, 2)
				sOut = sOut & "%"
			end if
		end if
		
		idx = InStr(sIn, "%%")

	Loop

	sOut = sOut & sIn

	FP_ReplaceQuoteChars = sOut

End Function

Sub FP_Close(obj)

	On Error Resume Next

	obj.Close

End Sub

Sub FP_SetCursorProperties(rs)

	On Error Resume Next

	rs.CursorLocation = 3 ' adUseClient
	rs.CursorType = 3 ' adOpenStatic

End Sub

%>

