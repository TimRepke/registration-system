var debug = false;

var story; // global access for links

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

	// === validate ===

	// (e.g.) get mail:
	// angular.element(document.querySelector('[ng-controller="storyBasicData"]')).scope().mehl;

	if (!bGoBack)
	{
		switch(this.state)
		{
		case 0:
			var basicDataScope = angular.element(document.querySelector('[ng-controller="storyBasicData"]')).scope();
			if (basicDataScope.storyBasicData.$invalid)
				return;

			this.form_variables.forname = basicDataScope.forname;
			this.form_variables.name = basicDataScope.name;
			this.form_variables.anzeig = basicDataScope.anzeig;
			this.form_variables.mehl = basicDataScope.mehl;

			break;
		case 1:
			if (
				this.form_variables.travelStartDate == null
			||
				this.form_variables.travelStartType == null
			)
				return;

			break;
		case 2:
			if (this.form_variables.eat == null)
				return;

			break;
		case 3:
			if (this.form_variables.age == null)
				return;

			break;
		case 4:
			if (
				this.form_variables.travelEndDate == null
			||
				this.form_variables.travelEndType == null
			)
				return;

			break;
		}
	}

	// === navigate ===
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
		this.age.animate({left:bGoBack?'900px':'0px'}, {duration: 1000, complete: function()
		{
			self.initAgeAnimation();
		}});
		this.eat.animate({left:bGoBack?'0px':'-900px'}, 1000);
		break;
	case 4:
		this.initTravelEnd();
		this.travelEnd.animate({left:bGoBack?'-900px':'0px'}, 1000);
		this.age.animate({left:bGoBack?'0px':'900px'}, 1000);
		break;
	case 5:
		this.initSummary();
		this.summary.animate({left:bGoBack?'-900px':'0px'}, 1000);
		this.travelEnd.animate({left:bGoBack?'0px':'900px'}, 1000);
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
Story.prototype.initSummary = function()
{
	// === Init View ===
	if (!this.summary)
	{
		this.summary = $('<div style="position:absolute; left: 0px; top: 0px"/>');
		this.summary.animate({left:'-900px'}, 0);
		this.storybox.append(this.summary);

		this.summary.append('<h2>Zusammenfassung</h2>');
		this.summaryTable = $('<table class="story_summary"/>')
		this.summary.append(this.summaryTable);

		var rowOrder = ["forname", "name", "anzeig", "mehl", "andaytyp", "abdaytyp", "age"];
		var rows = {
			forname:
				"Vorname",
			name:
				"Nachname",
			anzeig:
				"Anzeigename",
			mehl:
				"eMail",
			andaytyp:
				"Anreise Tag/Typ",
			abdaytyp:
				"Abreise Tag/Typ",
			age:
				"Alter"
		};

		for (var i = 0; i < rowOrder.length; ++i)
		{
			var rowName = rowOrder[i];
			var rowTitle = rows[rowName];
			this.summaryTable.append('<tr><td>' + rowTitle + '</td><td id="story_summary_' + rowName + '"></td></tr>');
		}
		this.summaryTable.append('<tr><td colspan="2">&nbsp;</td></tr>');
		this.summaryTable.append('<tr><td colspan="2">Daten Ok? Dann <button onclick="storySubmit()">Anmelden</button>.</td></tr>');
	}

	// === Update View ===
	$('#story_summary_forname').text(this.form_variables.forname);
	$('#story_summary_name').text(this.form_variables.name);
	$('#story_summary_anzeig').text(this.form_variables.anzeig);
	$('#story_summary_mehl').text(this.form_variables.mehl);
	$('#story_summary_andaytyp').text(this.form_variables.travelStartDate + ", " + this.form_variables.travelStartType);
	$('#story_summary_abdaytyp').text(this.form_variables.travelEndDate + ", " + this.form_variables.travelEndType);
	$('#story_summary_age').text(this.form_variables.age);
}
Story.prototype.initTravelStart = function()
{
	if (this.travelStart) return;

	var self = this;

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

	this.travelStartDate = this.addComboBox(this.travelStartTicket, "Datum", "anday", [""].concat(comm_get_possible_dates()), 115, 70); // @TODO: get date options from php
	this.travelStartTicket.append('<div style="position: absolute; left: 65px; top: 95px">Typ</div>');
	this.travelStartTicket.append('<div style="position: absolute; left: 115px; top: 95px" id="travelStartType">------</div>');
	this.travelStartDate.change(function()
	{
		var value = $(this).val();
		if (value == '')
		{
			self.form_variables.travelStartDate = null;
			self.travelStartDateWarning.show();
		}
		else
		{
			self.form_variables.travelStartDate = value;
			self.travelStartDateWarning.hide();
		}
	});
	this.travelStartTypeButtons = this.addTravelTypeButtons(this.travelStart);
	var travelFormNames = {
	car:
		"Auto",
	bike:
		"Fahrrad",
	oeffi:
		"&Ouml;ffentlich",
	camel:
		"Individuell"
	};
	for (var i in this.travelStartTypeButtons)
	{
		(function(i) { // i - scope issues -> would remember last i in for loop
			self.travelStartTypeButtons[i].click(function()
			{
				self.form_variables.travelStartType = i;
				for (var j in self.travelStartTypeButtons)
					self.travelStartTypeButtons[j].css({border:'1px solid #000'});
				self.travelStartTypeButtons[i].css({border:'2px solid #f00'});
				$('#travelStartType').html(travelFormNames[i]);
				self.travelStartTypeWarning.hide();
			});
		})(i);
	}

	// warnings created at the end -> on top
	this.travelStartTypeWarning = this.toolTippedStoryWarning(this.travelStartTicket, 32, 95, null, "Auf der linken Seite den<br/>Anreise Typ anklicken");
	this.travelStartDateWarning = this.toolTippedStoryWarning(this.travelStartTicket, 32, 70, null, "Anreise Datum wählen");
}
Story.prototype.addTravelTypeButtons = function(page)
{
	var buttons = {
	car:
		$('<div style="position: absolute; border: 1px solid; left: 23px; top: 222px; width: 129px; height: 87px; cursor: pointer;">&nbsp;</div>'),
	bike:
		$('<div style="position: absolute; border: 1px solid; left: 177px; top: 366px; width: 90px; height: 73px; cursor: pointer;">&nbsp;</div>'),
	oeffi:
		$('<div style="position: absolute; border: 1px solid; left: 316px; top: 192px; width: 84px; height: 90px; cursor: pointer;">&nbsp;</div>'),
	camel:
		$('<div style="position: absolute; border: 1px solid; left: 461px; top: 114px; width: 78px; height: 71px; cursor: pointer;">&nbsp;</div>')
	};
	var tips = {
	car:
		$('<div class="storyTip" style="left: 8px; top: 314px; display:none">Anfahrt mit dem Auto</div>'),
	bike:
		$('<div class="storyTip" style="left: 127px; top: 444px; display:none">Anfahrt mit dem Fahrrad</div>'),
	oeffi:
		$('<div class="storyTip" style="left: 206px; top: 286px; display:none">Anfahrt gemeinsam mit den &Ouml;ffentlichen</div>'),
	camel:
		$('<div class="storyTip" style="left: 391px; top: 190px; display:none">Anritt mit Kamel / Individuell</div>')
	};

	for (var i in buttons)
	{
		if (i.indexOf('Tip') == i.length - 3)
			continue;

		var button = buttons[i];
		var tip = tips[i];
		page.append(button);
		page.append(tip);

		button.mouseenter(function ()
		{
			$(this).stop(true, true).effect("highlight");
		});
		function setHover(tip) // function to keep 'tip' from changing (scope issue)
		{
			button.hover(function () // in
			{
				tip.stop(true, true).fadeIn(200);
			},
			function () // out
			{
				tip.stop(true, true).fadeOut(200);
			});
		}
		setHover(tip);
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

	var self = this;

	this.travelEnd = this.storyImageDiv('travelEnd.png');
	this.travelEnd.animate({left:'900px'}, 0);
	this.storybox.append(this.travelEnd);
	
	this.travelEndTicket = this.storyImageDiv('ticket.png');
	this.travelEndTicket.css({left: '560px', top: '100px', width: '329px', height: '161px'});
	this.travelEnd.append(this.travelEndTicket);

	this.addTicketTitle(this.travelEndTicket, "Abreise");
	this.travelEndTicketButton = this.addTicketButton(this.travelEndTicket, "story.next()");
	this.travelEndTicketButton.mouseenter(function(event) {
		$(this).stop(true, true).effect("highlight");
	});

	this.travelEndDate = this.addComboBox(this.travelEndTicket, "Datum", "abday", [""].concat(comm_get_possible_dates()), 115, 70); // @TODO: get date options from php
	this.travelEndTicket.append('<div style="position: absolute; left: 65px; top: 95px">Typ</div>');
	this.travelEndTicket.append('<div style="position: absolute; left: 115px; top: 95px" id="travelEndType">------</div>');
	this.travelEndDate.change(function()
	{
		var value = $(this).val();
		if (value == '')
		{
			self.form_variables.travelEndDate = null;
			self.travelEndDateWarning.show();
		}
		else
		{
			self.form_variables.travelEndDate = value;
			self.travelEndDateWarning.hide();
		}
	});
	this.travelEndTypeButtons = this.addTravelTypeButtons(this.travelEnd);
	var travelFormNames = {
	car:
		"Auto",
	bike:
		"Fahrrad",
	oeffi:
		"&Ouml;ffentlich",
	camel:
		"Individuell"
	};
	for (var i in this.travelEndTypeButtons)
	{
		(function(i) { // i - scope issues -> would remember last i in for loop
			self.travelEndTypeButtons[i].click(function()
			{
				self.form_variables.travelEndType = i;
				for (var j in self.travelEndTypeButtons)
					self.travelEndTypeButtons[j].css({border:'1px solid #000'});
				self.travelEndTypeButtons[i].css({border:'2px solid #f00'});
				$('#travelEndType').html(travelFormNames[i]);
				self.travelEndTypeWarning.hide();
			});
		})(i);
	}

	// warnings created at the end -> on top
	this.travelEndTypeWarning = this.toolTippedStoryWarning(this.travelEndTicket, 32, 95, null, "Auf der linken Seite den<br/>Anreise Typ anklicken");
	this.travelEndDateWarning = this.toolTippedStoryWarning(this.travelEndTicket, 32, 70, null, "Anreise Datum wählen");
}
Story.prototype.initEat = function()
{
	if (this.eat) return;

	var self = this;

	this.eat = this.storyImageDiv('eat.png');
	this.eat.animate({left:'900px'}, 0);
	this.storybox.append(this.eat);

	this.foodTypeButtons = this.addFoodTypeButtons(this.eat);
	for (var i in this.foodTypeButtons)
	{
		(function (i)
		{
			self.foodTypeButtons[i].click(function()
			{
				self.form_variables.eat = i;
				for (var j in self.foodTypeButtons)
					self.foodTypeButtons[j].css({border: '1px solid #000'});
				self.foodTypeButtons[i].css({border: '2px solid #f00'});
				self.eatWarning.hide();
			});
		})(i);
	}
	
	this.eatContinueButton = $('<div style="position: absolute; left: 799px; top: 412px; width: 32px; height: 27px; cursor: pointer;" onclick="story.next()">&nbsp;</div>');
	this.eatContinueButton.mouseenter(function(event) {
		$(this).stop(true, true).effect("highlight");
	});
	this.eat.append(this.eatContinueButton);

	this.eatWarning = this.toolTippedStoryWarning(this.eat, 386, 149, null, "Art des Essens auswählen");
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
	var tips = {
	cow:
		$('<div class="storyTip" style="left: 175px; top: 237px; display:none">Ziemlich alles:<br/>Vegan, Vegetarisch und Fleisch</div>'),
	cheese:
		$('<div class="storyTip" style="left: 427px; top: 298px; display:none">Vegetarisch</div>'),
	wheat:
		$('<div class="storyTip" style="left: 632px; top: 346px; display:none">Vegan</div>')
	};

	for (var i in buttons)
	{
		var button = buttons[i];
		page.append(button);
		button.mouseenter(function(event) {
			$(this).stop(true, true).effect("highlight");
		});
		page.append(tips[i]);
		(function(tip) {
			button.hover(function () // in
			{
				tip.stop(true, true).fadeIn(200);
			},
			function () // out
			{
				tip.stop(true, true).fadeOut(200);
			});
		})(tips[i]);
	}

	return buttons;
}
Story.prototype.initAgeAnimation = function()
{
	var self = this;
	setTimeout(function()
	{
		for (var j in self.ageButtons)
			self.ageButtons[j].effect("highlight");
	}, 800);
}
Story.prototype.initAge = function()
{
	if (this.age) return;

	var self = this;

	this.age = this.storyImageDiv('age.png');
	this.age.animate({left:'900px'}, 0);
	this.storybox.append(this.age);

	this.ageButtonContinue = $('<div style="position: absolute; left: 675px; top: 354px; width: 28px; height: 21px; cursor: pointer;" onclick="story.next()">&nbsp;</div>');
	this.age.append(this.ageButtonContinue);
	this.ageButtonContinue.mouseenter(function() {
		$(this).stop(true, true).effect("highlight");
	});

	this.ageButtons = {
	eighteenplus:
		$('<div style="position: absolute; left: 468px; top: 232px; width: 35px; height: 20px; cursor: pointer;">&nbsp;</div>'),
	below:
		$('<div style="position: absolute; left: 38px; top: 285px; width: 88px; height: 33px; cursor: pointer;">&nbsp;</div>')
	};
	for (var i in this.ageButtons)
	{
		(function(i) { // std::scope_issue<i>, own scope fixes reference
			self.age.append(self.ageButtons[i]);
			self.ageButtons[i].click(function()
			{
				self.form_variables.age = i;
				for (var j in self.ageButtons)
					self.ageButtons[j].css({border:'none'});
				self.ageButtons[i].css({border:'2px solid #f00'});
				self.ageButtonContinue.effect("highlight");
				self.ageWarning.hide();
			});
			self.ageButtons[i].mouseenter(function() {
				$(this).stop(true, true).effect("highlight");
			});
		})(i);
	}

	this.ageWarning = this.toolTippedStoryWarning(this.age, 678, 134, null, "Links ein Schild mit passendem<br/>Altersbereich anklicken");
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

	var self = this;

	// == init view ==
	this.basicData = this.storyImageDiv('begin.png');
	this.storybox.append(this.basicData);
	this.bd_bell = this.storyImageDiv('bell.png');
	this.bd_bell.css({position: 'relative', top: '20px', left: '20px', width: '419px', height: '438px'});
	this.basicData.append(this.bd_bell);
	this.bd_bell.fadeOut(0);

	// == form ==
	this.bd_bellForm = $('<form name="storyBasicData" novalidate/>');
	this.bd_bell.append(this.bd_bellForm);

	this.bd_bellForname = this.addFormText(this.bd_bellForm, "Vorname", "forname", "text", "forname", 160, 60);
	this.bd_bellForname.attr('ng-minlength', '2');
	this.bd_bellForname.attr('required', 'required');
	this.toolTippedStoryWarning(this.bd_bell, 135, 80, 'forname', "Bitte den Vornamen eingeben");

	this.bd_bellName = this.addFormText(this.bd_bellForm, "Nachname", "name", "text", "name", 160, 140).attr('ng-minlength', '2');
	this.bd_bellName.attr('ng-minlength', '2');
	this.bd_bellName.attr('required', 'required');
	this.toolTippedStoryWarning(this.bd_bell, 135, 160, 'name', "Bitte den Nachnamen eingeben");

	this.bd_bellAnzeig = this.addFormText(this.bd_bellForm, "Anzeigename", "anzeig", "text", "anzeig", 160, 215);
	this.bd_bellAnzeig.attr('ng-minlength', '2');
	this.bd_bellAnzeig.attr('required', 'required');
	this.toolTippedStoryWarning(this.bd_bell, 135, 235, 'anzeig', "Bitte einen Anzeige-Namen eingeben");

	this.bd_bellMehl = this.addFormText(this.bd_bellForm, "eMail", "mehl", "email", "mehl", 160, 290);
	this.bd_bellMehl.attr('ng-minlength', '5');
	this.bd_bellMehl.attr('required', 'required');
	this.toolTippedStoryWarning(this.bd_bell, 135, 310, 'mehl', "Bitte eine g&uuml;ltige eMail Addresse eingeben");

	// == notice ==
	this.bd_bell.append($('<div style="position:absolute;top:380px;left:120px">Bitte klingeln, wenn fertig.</div>'))

	this.bd_btn_continue = $('<div style="width:60px;height:67px;position:absolute;top:48px;left:48px;cursor:pointer;" onclick="story.next();" />');
	this.bd_bell.append(this.bd_btn_continue);
	this.bd_btn_continue.mouseenter(function(event) {
		$(this).stop(true, true).effect("highlight");
	});

	// for transition animation
	this.bd_orig_bell = $('<div style="width:3px;height:3px;position:absolute;left:633px;top:193px" />');
	this.basicData.append(this.bd_orig_bell);

	// == angularJS ==
	this.basicData.attr('ng-controller', 'storyBasicData');

	// manually start angular for the forms
	angular.module('storyApp', []).controller('storyBasicData', function($scope)
	{
		// for function decl.
	});
	angular.bootstrap(document, ['storyApp']);
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
}

Story.prototype.addComboBox = function(parentNode, label, fieldName, options, x, y)
{
	var form_label = $('<div>'+label+'</div>');
	var form = $('<select name="story_field_'+fieldName+'"></select>');
	for(var i = 0; i < options.length; ++i)
		form.append('<option>' + options[i] + '</option>');
	form_label.css({position:'absolute', top: y + 'px',left: (x - 60) + 'px'});
	form.css({position:'absolute', top: y + 'px',left: x + 'px'});
	parentNode.append(form_label);
	parentNode.append(form);

	this.form_variables[fieldName] = null;

	return form;
}
Story.prototype.addFormText = function(parentNode, label, fieldName, type, model, x, y)
{
	var form_label = $('<div>'+label+':</div>');
	var form = $('<input type="'+type+'" name="story_field_'+fieldName+'" ng-model="'+model+'"/>');
	form_label.css({position:'absolute', top:y+'px',left:x+'px'});
	form.css({position:'absolute', top:(y+20)+'px',left:x+'px'});
	parentNode.append(form_label);
	parentNode.append(form);

	this.form_variables[fieldName] = null;

	return form;
}
Story.prototype.toolTippedStoryWarning = function(page, x, y, field, toolTipText)
{
	var warning = $('<div class="storyWarn" style="left: '+x+'px; top: '+y+'px;"' + (field != null ? (' ng-show="!'+field+'"') : '') + '>&nbsp;</div>');
	var toolTip = $('<div class="storyToolTip" style="left: '+x+'px; top: '+(y+25)+'px; display: none; text-align: left">'+toolTipText+'</div>');
	page.append(warning);
	page.append(toolTip);

	warning.hover(function() // over
	{
		toolTip.stop(true, true).fadeIn(200);
	},
	function() // out
	{
		toolTip.stop(true, true).fadeOut(200);
	});

	return warning;
}

function storySubmit()
{
	var formWrapper = $('<div style="display:none"/>');
	var form = $('<form name="storySubmitForm" method="POST"/>');
	formWrapper.append(form);
	$('#storybox').append(formWrapper);

	function formAppendText(name, value)
	{
		form.append('<input name="' + name + '" value="' + value.replace(/[\r\n]/g, " ").replace(/&/g, "&amp;").replace(/"/g, "&quot;") + '"/>');
	}

	var eatMap = {
	cow:
		"Alles",
	cheese:
		"Vegetarisch",
	wheat:
		"Vegan"
	};
	var ageMap = {
	eighteenplus:
		"Nein",
	below:
		"Ja"
	};
	var travelMapPhp = config_get_travel_types();
	travelMap = {
	car:
		"AUTO",
	oeffi:
		"BUSBAHN",
    bike:
		"RAD",
	camel:
		"INDIVIDUELL"
	};

	formAppendText('forname', story.form_variables.forname);
	formAppendText('sirname', story.form_variables.name);
	formAppendText('pseudo', story.form_variables.anzeig);
	formAppendText('mehl', story.form_variables.mehl);
	formAppendText('studityp', 'Ersti'); // ?
	formAppendText('virgin', ageMap[story.form_variables.age] || '');
	formAppendText('essen', eatMap[story.form_variables.eat] || '');
	formAppendText('anday', story.form_variables.travelStartDate);
	formAppendText('antyp', travelMapPhp[travelMap[story.form_variables.travelStartType]]);
	formAppendText('abday', story.form_variables.travelEndDate);
	formAppendText('abtyp', travelMapPhp[travelMap[story.form_variables.travelEndType]]);
	formAppendText('comment', 'This form was created with VisualPotato3D'); // ?
	// formAppendText('public', ''); // ?
	// form.append('<input type="submit" name="submit" />');
	formAppendText('storySubmit', 'storySubmit');

	form.submit();
}

// === INIT ===
$(function()
{
	var storybox = $('#storybox');
	if (storybox)
	{
		story = new Story($('#storyhead'), $('#storycanvas'), storybox);
		story.begin();
	}
});
