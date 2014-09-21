var debug = false;

var story;

function Story(_storyhead, _storycanvas, _storybox)
{
	this.storyhead = _storyhead;
	this.storycanvas = _storycanvas;
	this.storybox = _storybox;
	this.umleitung = $('#story_umleitung');
	this.state = -2;

	this.form_variables = {};

	this.basicData = null;
	this.travelStart = null;
	this.eat = null;
	this.age = null;
	this.travelEnd = null;
}
Story.prototype.next = function(bGoBack)
{
	var self = this;

	var previousState = this.state;
	if (!bGoBack)
		this.state += 1;

	switch(this.state)
	{
	case -1:
		this.initBeginButton();
		this.initBasicData();
		this.storycanvas.animate({height:0}, 0);
		break;
	case 0:
		if (previousState == -1)
		{
			this.storyhead.children().remove();
			if (debug)
				this.storyhead.append('(debug) <div style="cursor:pointer; text-decoration: underline" onclick="story.next()">NEXT</a>');

			if (debug)
			{
				this.storycanvas.stop(true, true).animate({height:'500px'}, 0);
				this.initBasicDataAnimation();
			}
			else
			{
				this.storycanvas.stop(true, true).animate({height:'500px'}, {duration: 200, complete: function()
				{
					self.initBasicDataAnimation();
				}});
			}
		}
		break;
	case 1:
		this.initTravelStart();
		this.travelStart.animate({left:bGoBack?'900px':'0px'}, {duration: 1000, complete: function()
		{
			self.initTravelStartAnimation();
		}});
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
	this.storyhead.append('<div style="cursor:pointer; text-decoration: underline" onclick="story.next()">Anmeldung starten (Story mode)</a>');
}
Story.prototype.initTravelStartAnimation = function()
{
	this.travelStartTicket.fadeIn(debug ? 0 : 1000);
}
Story.prototype.initTravelStart = function()
{
	if (this.travelStart) return;
	this.travelStart = this.storyImageDiv('travelBegin.png');
	this.travelStart.animate({left:'900px'}, 0);
	this.storybox.append(this.travelStart);
	
	this.travelStartTicket = this.storyImageDiv('ticket.png');
	this.travelStartTicket.css({left: '560px', top: '100px', width: '329px', height: '161px'});
	this.travelStart.append(this.travelStartTicket);
	this.travelStartTicket.fadeOut(0);

	this.addTicketTitle(this.travelStartTicket, "Anreise");
	this.travelStartTicketButton = this.addTicketButton(this.travelStartTicket, "story.next()");
	this.travelStartTicketButton.mouseenter(function(event) {
		$(this).stop(true, true).effect("highlight");
	});

	this.travelStartTypeButtons = this.addTravelTypeButtons(this.travelStart);
}
Story.prototype.addTravelTypeButtons = function(page)
{
	var buttons =
	{
	car:
		$('<div style="position: absolute; border: 1px solid; left: 23px; top: 222px; width: 129px; height: 87px; cursor: pointer;">&nbsp;</div>'),
	bike:
		$('<div style="position: absolute; border: 1px solid; left: 177px; top: 366px; width: 90px; height: 73px; cursor: pointer;">&nbsp;</div>'),
	oeffi:
		$('<div style="position: absolute; border: 1px solid; left: 316px; top: 192px; width: 84px; height: 90px; cursor: pointer;">&nbsp;</div>'),
	camel:
		$('<div style="position: absolute; border: 1px solid; left: 461px; top: 114px; width: 78px; height: 71px; cursor: pointer;">&nbsp;</div>')
	};

	for (var i in buttons)
	{
		var button = buttons[i];
		page.append(button);
		button.mouseenter(function(event) {
			$(this).stop(true, true).effect("highlight");
		});
	}

	return buttons;
}
Story.prototype.addTicketTitle = function(ticket, title)
{
	ticket.append('<div style="position: absolute; left: 55px; top: 15px;">' + title + '</div>');
}
Story.prototype.addTicketButton = function(ticket, funcstring)
{
	var newButton = $('<div style="position: absolute; left: 249px; top: 125px; width: 27px; height: 27px; cursor: pointer;" onclick="' + funcstring + '">&nbsp;</div>');
	ticket.append(newButton);
	return newButton;
}
Story.prototype.initTravelEnd = function()
{
	if (this.travelEnd) return;
	this.travelEnd = this.storyImageDiv('travelBegin.png');
	this.travelEnd.animate({left:'-900px'}, 0);
	this.storybox.append(this.travelEnd);
}
Story.prototype.initEat = function()
{
	if (this.eat) return;
	this.eat = this.storyImageDiv('eat.png');
	this.eat.animate({left:'900px'}, 0);
	this.storybox.append(this.eat);

	this.foodTypeButtons = this.addFoodTypeButtons(this.eat);
	
	this.eatContinueButton = $('<div style="position: absolute; left: 799px; top: 412px; width: 32px; height: 27px; cursor: pointer;" onclick="story.next()">&nbsp;</div>');
	this.eatContinueButton.mouseenter(function(event) {
		$(this).stop(true, true).effect("highlight");
	});
	this.eat.append(this.eatContinueButton);
}
Story.prototype.addFoodTypeButtons = function(page)
{
	var buttons =
	{
	cow:
		$('<div style="position: absolute; border: 1px solid; left: 236px; top: 139px; width: 129px; height: 93px; cursor: pointer;">&nbsp;</div>'),
	cheese:
		$('<div style="position: absolute; border: 1px solid; left: 446px; top: 236px; width: 57px; height: 56px; cursor: pointer;">&nbsp;</div>'),
	wheat:
		$('<div style="position: absolute; border: 1px solid; left: 604px; top: 214px; width: 112px; height: 127px; cursor: pointer;">&nbsp;</div>')
	};

	for (var i in buttons)
	{
		var button = buttons[i];
		page.append(button);
		button.mouseenter(function(event) {
			$(this).stop(true, true).effect("highlight");
		});
	}

	return buttons;
}
Story.prototype.initAge = function()
{
	if (this.age) return;
	this.age = this.storyImageDiv('age.png');
	this.age.animate({left:'900px'}, 0);
	this.storybox.append(this.age);
}
Story.prototype.initBasicDataAnimation = function()
{
	var self = this;
	setTimeout(function() {
		self.bd_bell.fadeIn(debug ? 0 : 1200);
		self.bd_orig_bell.effect("transfer", {to: self.bd_bell}, 800);
	}, debug ? 0 : 600);
}
Story.prototype.initBasicData = function()
{
	if (this.basicData) return;
	this.basicData = this.storyImageDiv('begin.png');
	this.storybox.append(this.basicData);
	this.bd_bell = this.storyImageDiv('bell.png');
	this.bd_bell.css({position: 'relative', top: '20px', left: '20px', width: '419px', height: '438px'});
	this.basicData.append(this.bd_bell);
	this.bd_bell.fadeOut(0);

	this.addFormText(this.bd_bell, "Vorname", "forname", 150, 60);
	this.addFormText(this.bd_bell, "Nachname", "name", 150, 140);
	this.addFormText(this.bd_bell, "Anzeigename", "anzeig", 150, 215);
	this.addFormText(this.bd_bell, "eMail", "mehl", 150, 290);
	this.bd_bell.append($('<div style="position:absolute;top:380px;left:120px">Bitte klingeln, wenn fertig.</div>'))

	this.bd_btn_continue = $('<div style="width:60px;height:67px;position:absolute;top:48px;left:48px;cursor:pointer;" onclick="story.next();" />');
	this.bd_bell.append(this.bd_btn_continue);
	this.bd_btn_continue.mouseenter(function(event) {
		$(this).stop(true, true).effect("highlight");
	});

	this.bd_orig_bell = $('<div style="width:3px;height:3px;position:absolute;left:633px;top:193px" />');
	this.basicData.append(this.bd_orig_bell);
}
Story.prototype.begin = function()
{
	this.next();
}

Story.prototype.storyImage = function(filename)
{
	return $('<img src="view/graphics/story/' + filename + '" alt="" />');
}
Story.prototype.storyImageDiv = function(filename)
{
	return $('<div style="position:absolute; width:900px; height:500px; background: url(view/graphics/story/'+filename+');"></div>');
	this.addFormText(bell, "eMail", "mehl", 150, 215);
}

Story.prototype.addFormText = function(parentNode, label, fieldName, x, y)
{
	var form_label = $('<div>'+label+':</div>');
	var form = $('<input name="'+fieldName+'"/>');
	form_label.css({position:'absolute', top:y+'px',left:x+'px'});
	form.css({position:'absolute', top:(y+20)+'px',left:x+'px'});
	parentNode.append(form_label);
	parentNode.append(form);

	this.form_variables[fieldName] = null;
}

$(function() {
	story = new Story($('#storyhead'), $('#storycanvas'), $('#storybox'));
	story.begin();
});
