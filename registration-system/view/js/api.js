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
    isSet: function(param) {
        var tmp = this.getParams();
        return tmp && param in tmp;
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
        if(! (attribute in properties)) throw new Error("Attribute does not exist!");
        return properties[attribute] !== null;
    };

    this.isComplete = function () {
        for (var key in properties) {
            if(!this.isSet(key)) return false;
        }
        return true;
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
        var tests = {
            'forname': function() {
                return /^[^0-9<>!?.::,#*@^_$\\"'%;()&+]{2,50}$/.test(value);
            },
            'sirname': function() {
                return /^[^0-9<>!?.::,#*@^_$\\"'%;()&+]{2,50}$/.test(value);
            },
            'mehl': function() {
                return /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value);
            }
        };

        try {
            return tests[attribute]();
        } catch (e) {
            console.warn('testing for ' + attribute + ' threw ' + e);
            return false;
        }
    };
}

function FAPI() {
    this.data = new Bachelor();
    this.captcha = '';

    this.methodBasepath = 'view/signups/' + UrlComponents.getValueOf('method') + '/';
}

FAPI.prototype.resolvePath = function(file) {
    return this.methodBasepath + file;
};

/**
 * Submit the registration send-and-pray style!
 *
 * All error handling will be done by the simple form (if needed).
 */
FAPI.prototype.submitSignup = function() {
    var formWrapper = $('<div style="display:none"/>');
    var form = $('<form name="storySubmitForm" method="POST"/>');
    formWrapper.append(form);
    $('#signup-container').append(formWrapper);


    var data = this.data.getProperties();

    for (var key in data) {
        var value = data[key];
        var leaveOut = false;
        switch (key) {
            case 'public':
                if(value === true) value = 'public';
                else leaveOut = true;
                break;
            default: /* does nothing */
        }

        if(!leaveOut) {
            value = value.replace(/[\r\n]/g, "<br/>").replace(/&/g, "&amp;").replace(/"/g, "&quot;");
            addToForm(key, value);
        }
    }

    addToForm('captcha', this.captcha);
    addToForm('storySubmit', 'storySubmit');
    if(UrlComponents.isSet('waitlist'))
        addToForm('waitlist', 'waitlist');

    form.submit();

    function addToForm(key, value) {
        form.append('<input name="' + key + '" value="' + value + '"/>');
    }
};

FAPI.attachSoftProtector = function (elementIds, regex) {
    for(var i = 0; i < elementIds.length; ++i) {
        $('#'+elementIds[i]).keyup(function(event) {
            if (!event.target.value.match(regex))
                event.target.style.backgroundColor="#f00";
            else
                event.target.style.backgroundColor="#fff";
        });
    }
};

