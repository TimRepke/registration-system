var UrlComponents = {
    decomposed: null,
    getParams: function() {
        if(this.decomposed) return this.decomposed;

        var query = location.search.substr(1);
        this.decomposed = decomposer(query);
        return this.decomposed;

        function decomposer(q) {
            // taken from http://jsperf.com/querystring-with-javascript
            return (function(a) {
                if (a == "") return {};
                var b = {};
                for (var i = 0; i < a.length; ++i) {
                    var p = a[i].split('=');
                    if (p.length != 2) continue;
                    b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
                }
                return b;
            })(q.split("&"));
        }
    },
    getValueOf: function(param) {
        return this.getParams()[param];
    }
};

function Bachelor() {
    var properties = {
        'forname': null,
        'sirname': null,
        'anday':   null,
        'abday':   null,
        'antyp':   null,
        'abtyp':   null,
        'pseudo':  null,
        'mehl':    null,
        'essen':   null,
        'public':  null,
        'studityp':null,
        'comment': null
    };

    this.getProperties = function () {
        return properties;
    };

    this.getValue = function (attribute) {
        if(attribute in properties)
            return properties[attribute];
        throw new Error("Attribute does not exist!");
    };

    this.isSet = function (attribute) {
        return !!properties[attribute];
    };

    this.setValue = function (attribute, value) {
        if(attribute in properties)
            properties[attribute] = value;
        else
            throw new Error("This property isn't supposed to be here!");
    };

    this.resetValue = function (attribute) {
        if(attribute in properties)
            properties[attribute] = null;
        else
            throw new Error("This property isn't supposed to exist in the first place!");
    };

    this.setValues = function (props) {
        if(props) {
            for(var key in props){
                this.setValue(key, props[key]);
            }
        }
    };

    this.testValidValue = function (attribute, value) {
        //TODO should return true, if the given value is valid for the given attribute (soft protect)
    };
}

function FAPI() {
    this.data = new Bachelor();
    this.methodBasepath = 'view/signups/' + UrlComponents.getValueOf('method') + '/';
}

FAPI.prototype.resolvePath = function(file) {
    return this.methodBasepath + file;
};

/**
 * params to send have to be set earlier (see the Bachelor)
 *
 * callback is called, once the request was sent to the server.
 * It'll transmit an object containing the
 *    -> state (0 = successful, 1 = error, 2 = ?)
 *    -> main message (string)
 *    -> errors (null, if non; array of strings with messages else)

 * @param callback
 */
FAPI.prototype.submitSignup = function(callback) {
    // TODO evaluate params and send them

    callback({
        state: 0,
        messages: 'Successful signup',
        errors: null
    })
};

/**
 * Helper function for FAPI.prototype.submitSignup
 * it creates a hidden form to be submitted.
 */
FAPI.prototype.prepareSubmission = function () {
    var formWrapper = $('<div style="display:none"/>');
    var form = $('<form name="storySubmitForm" method="POST"/>');
    formWrapper.append(form);
    $('#signup-container').append(formWrapper);

    function formAppendText (name, value) {
        form.append('<input name="' + name + '" value="' + value.replace(/[\r\n]/g, "<br/>").replace(/&/g, "&amp;").replace(/"/g, "&quot;") + '"/>');
    }

    if(window.location.pathname.search("waitlist")>0)
        formAppendText("waitlist", "waitlist");
    formAppendText('forname', story.form_variables.forname);
    formAppendText('sirname', story.form_variables.name);
    formAppendText('pseudo', story.form_variables.anzeig);
    formAppendText('mehl', story.form_variables.mehl);
    formAppendText('studityp', $('#story_summary_studityp').val());
    formAppendText('virgin', Story.ageMap[story.form_variables.age]);
    formAppendText('essen', Story.eatMapPhp[Story.eatMap[story.form_variables.eat]]);
    formAppendText('anday', story.form_variables.travelStartDate);
    formAppendText('antyp', Story.travelMapPhp[Story.travelMap[story.form_variables.travelStartType]]);
    formAppendText('abday', story.form_variables.travelEndDate);
    formAppendText('abtyp', Story.travelMapPhp[Story.travelMap[story.form_variables.travelEndType]]);
    formAppendText('comment', $('#story_summary_comment').val());
    if ($('#story_summary_public').is(':checked'))
        formAppendText('public', 'public');
    formAppendText('captcha', $('#story_summary_captcha').val());
    formAppendText('storySubmit', 'storySubmit');

    form.submit();
};

FAPI.prototype.attachSoftProtector = function (elementIds, regex) {
    for(var i = 0; i < elementIds.length; ++i) {
        $('#'+elementIds[i]).keyup(function(event) {
            if (!event.target.value.match(regex))
                event.target.style.backgroundColor="#f00";
            else
                event.target.style.backgroundColor="#fff";
        });
    }
};

