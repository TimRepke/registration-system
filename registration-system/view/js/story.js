var debug = true;

var story;
var form_variables = {};

function storyImage(filename)
{
	return $('<img src="view/graphics/story/' + filename + '" alt="" />');
}
function storyImageDiv(filename)
{
	return $('<div style="position:absolute; width:900px; height:500px; background: url(view/graphics/story/'+filename+');"></div>');
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
	this.umleitung = $('#story_umleitung');
	this.state = -2;

	this.basicData = null;
	this.travelStart = null;
	this.eat = null;
	this.age = null;
	this.travelEnd = null;
}
Story.prototype.next = function(bGoBack)
{
	var previousState = this.state;
	if (!bGoBack)
		this.state += 1;

	switch(this.state)
	{
	case -1:
		this.initBeginButton();
		break;
	case 0:
		if (previousState == -1)
			this.storybox.children().remove();
		if (debug)
			this.storybox.append('(debug) <div style="cursor:pointer; text-decoration: underline" onclick="story.next()">NEXT</a>');
		this.initBasicData();
		break;
	case 1:
		this.initTravelStart();
		this.travelStart.animate({left:bGoBack?'900px':'0px'}, 1000);
		this.basicData.animate({left:bGoBack?'0px':'-900px'}, 1000);
		break;
	case 2:
		this.initEat();
		this.eat.animate({left:bGoBack?'900px':'0px'}, 1000);
		this.travelStart.animate({left:bGoBack?'0px':'-900px'}, 1000);
		break;
	case 3:
		this.initAge();
		this.age.animate({left:bGoBack?'900px':'0px'}, 1000);
		this.eat.animate({left:bGoBack?'0px':'-900px'}, 1000);
		break;
	case 4:
		this.initTravelEnd();
		this.travelEnd.animate({left:bGoBack?'-900px':'0px'}, 1000);
		this.age.animate({left:bGoBack?'0px':'900px'}, 1000);
		break;
	default:
		if (bGoBack)
			this.state += 1;
		else
			this.state -= 1;
	}
	if (bGoBack)
	{
		this.state -= 1;
		if (this.state < 0)
			this.state = 0;
	}
	if (bGoBack && this.state == 0)
		this.umleitung.animate({bottom:'-70px'}, 500);
	else if (!bGoBack && this.state == 1)
		this.umleitung.animate({bottom:'0px'}, 500);
}
Story.prototype.initBeginButton = function()
{
	this.storybox.append('<div style="cursor:pointer; text-decoration: underline" onclick="story.next()">Anmeldung starten (Story mode)</a>');
}
Story.prototype.initTravelStart = function()
{
	if (this.travelStart) return;
	this.travelStart = storyImageDiv('travelBegin.png');
	this.travelStart.animate({left:'900px'}, 0);
	this.storybox.append(this.travelStart);
}
Story.prototype.initTravelEnd = function()
{
	if (this.travelEnd) return;
	this.travelEnd = storyImageDiv('travelBegin.png');
	this.travelEnd.animate({left:'-900px'}, 0);
	this.storybox.append(this.travelEnd);
}
Story.prototype.initEat = function()
{
	if (this.eat) return;
	this.eat = storyImageDiv('eat.png');
	this.eat.animate({left:'900px'}, 0);
	this.storybox.append(this.eat);
}
Story.prototype.initAge = function()
{
	if (this.age) return;
	this.age = storyImageDiv('age.png');
	this.age.animate({left:'900px'}, 0);
	this.storybox.append(this.age);
}
Story.prototype.initBasicData = function()
{
	if (this.basicData) return;
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
Story.prototype.begin = function()
{
	this.next();
}

$(function() {
	story = new Story($('#storybox'));
	story.begin();
});
