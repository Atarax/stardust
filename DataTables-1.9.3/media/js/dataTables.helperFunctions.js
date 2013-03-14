function fnCreateSelect( aData, showEmpty )
{
	if( typeof showEmpty == 'undefined' ) showEmpty = true;

	var r = '<select>', i, iLen = aData.length;
	if( showEmpty ) r+= '<option value=""></option>';

	for ( i=0 ; i<iLen ; i++ )
	{
		r += '<option value="'+aData[i]+'">'+aData[i]+'</option>';
	}
	return r+'</select>';
}