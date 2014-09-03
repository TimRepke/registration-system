var debug = true;
//var debug = false;

var story;
var form_variables = {};

function storyImage(filename)
{
	return $('<img src="view/graphics/story/' + filename + '" alt="" />');
}
function storyImageDiv(filename)
{
	return $('<div style="width:900px; height:500px; background: url(view/graphics/story/'+filename+');"></div>');
	addFormText(bell, "eMail", "mehl", 150, 215);
}

function addFormText(parentNode, label, fieldName, x, y)
{
	var form_label = $('<div>'+label+':</div>');
	var form = $('<input name="'+fieldName+'"/>');
	form_label.css({position:'absolute', top:y+'px',left:x+'px'});
	form.css({position:'absolute', top:(y+20)+'px',left:x+'px'});
	parentNode.append(form_label);
	parentNode.append(form);
}

function Story(_storybox)
{
	this.storybox = _storybox;
	this.state = 0;

	this.basicData = null;
	this.travelStart = null;
}
Story.prototype.initTravelStart = function()
{
	this.travelStart = storyImageDiv('travelStart.png');
	this.storybox.append(this.travelStart);
}
Story.prototype.initBasicData = function()
{
	this.basicData = storyImageDiv('begin.png');
	this.storybox.append(this.basicData);
	var bell = storyImageDiv('bell.png');
	bell.css({position: 'relative', top: '20px', left: '20px', width: '419px', height: '438px'});
	this.basicData.append(bell);
	if (!debug)
		bell.fadeOut(0);

	addFormText(bell, "Vorname", "forname", 150, 60);
	addFormText(bell, "Nachname", "name", 150, 140);
	addFormText(bell, "Anzeigename", "anzeig", 150, 215);
	addFormText(bell, "eMail", "mehl", 150, 290);
	bell.append($('<div style="position:absolute;top:380px;left:120px">Bitte klingeln, wenn fertig.</div>'))

	var btn_continue = $('<div style="width:60px;height:67px;position:absolute;top:48px;left:48px;cursor:pointer;" onclick="story.next();" />');
	bell.append(btn_continue);
	btn_continue.mouseenter(function(event) {
		$(this).effect("highlight");
	});

	var orig_bell = $('<div style="width:3px;height:3px;position:absolute;left:633px;top:193px" />');
	this.basicData.append(orig_bell);
	if (!debug)
		setTimeout(function() {
			bell.fadeIn(1200);
			orig_bell.effect("transfer", {to: bell}, 800);
		}, 600);
}
Story.prototype.next = function()
{
	switch(this.state)
	{
	case 0:
		this.initBasicData();
		break;
	case 1:
		this.basicData.remove();
		this.initTravelStart();
		break;
	case 2:
		break;
	case 3:
		break;
	}
	this.state += 1;
}
Story.prototype.begin = function()
{
	this.next();
}

$(function() {
	story = new Story($('#storybox'));
	story.begin();
});
