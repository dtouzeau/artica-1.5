<table CLASS="ttb2" cellspacing="2" cellpadding="2" border="0" width="100%">
	<tr>
		<!-- BEGIN time_date_row -->
		<td CLASS="gensmall" width="50%"><div align="left">{time_date_row.CURRENT_DATE}</div></td>
		<!-- END time_date_row -->
		<td><div align="right">
		<form>
			Refresh after <input name="refresh" size="4" value="{REFRESH}"> seconds.
			<input name="rate" type="hidden" value="{RATE}"> 
			<input name="host" type="hidden" value="{HOST}"> 
			<input name="span" type="hidden" value="{SPAN}">
			<input name="debug" type="hidden" value="{DEBUG}">
			<input name="legend" type="hidden" value="{LEGEND}">
		</form>
		</div></td> 
	</tr>
</table>
</td></tr><tr><td width="611">

<!-- BEGIN hostlist_row -->
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td width="2"></td>
<!-- BEGIN button_row2 -->
		<td>{hostlist_row.button_row2.BUTTON}</td>
<!-- END button_row2 -->
</table>
<!-- END hostlist_row -->
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>{PGRAPH}</td>
	</tr>
	<tr>
		<td>{SBGRAPH}</td>
	</tr>
	<tr>
		<td>{VGRAPH}</td>
	</tr>
</table>
<!-- BEGIN DUMMY -->
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td rowspan="2">This is a test</td><td>123</td><td>456</td>
	</tr><tr>
		<td rowspan="2">678</td><td>901</td>
	</tr><tr>
		<td>234</td><td>567</td>
	</tr>
</table>
<!-- END DUMMY -->
</td><td>
