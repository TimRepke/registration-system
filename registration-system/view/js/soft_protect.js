function soft_protect(elementIds, regex)
{
	for(var i = 0; i < elementIds.length; ++i)
	{
		$('#'+elementIds[i]).keyup(function(event) {
			if (!event.target.value.match(regex))
				event.target.style.backgroundColor="#f00";
			else
				event.target.style.backgroundColor="#fff";
		});
	}
}
