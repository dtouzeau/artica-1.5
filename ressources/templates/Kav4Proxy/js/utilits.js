(function($){
	$.fn.appendMultiple = function (stuff) {
		if (stuff && stuff.constructor === Array)
			for (var i = 0, l = stuff.length; i < l; ++i)
				this.append(stuff[i]);
		else
			this.append(stuff);
		return this;
	};
})(jQuery);


var Utils = function() {};

/**
 * Processes template - replaces the template parameters of their values
 * Example of usage: 
 * 	 Utils.replace(
 * 	 	'Hello %user from %country!',
 *   	{ 'user': 'Ivan', 'country': 'Russia' }
 *   ); 
 */
Utils.replace = function(data, replacements) {
	if (typeof data != 'string') {
		return null;
	}

	return data.replace(new RegExp('\\%(.+?)\\b', 'gm'), function() {
		var value = (replacements || {})[arguments[1]];
		if (typeof value != 'undefined') {
			if (value == null) {
				return '';
			} else {
				return value;
			}
		} else {
			return arguments[1];
		}
	});
};

/**
 * Creates a context-depended callback function
 * Example of usage: 
 * 	 $('A').click(Utils.delegate(this, this.methodName));
 */
Utils.delegate = function(obj, fn /* , ...*/ ) {
	var args1 = $.makeArray(arguments).slice(2);
	return function () {
		var args = args1.concat($.makeArray(arguments));
		return fn.apply(obj, args);
	};
};

/**
 * Redirecting the browser to the given page (for any case)
 * @param {string}
 */
Utils.redirect = function(url) {
    if(url && url != window.location.href ) {
        window.location.href = url;
    } else {
        window.location.reload(true);
    }
};

/**
 * Implementation of class inheritance
 * @param {object} classChild  child class
 * @param {object} classParent parent class
 */
Utils.classInheritance = function(classChild, classParent) {
    var F = function() { };
    F.prototype = classParent.prototype;
    classChild.prototype = new F();
    classChild.prototype.constructor = classChild;
    classChild.superclass = classParent.prototype;   
};

/**
 * Checking the email
 * @param {string} email - validate email
 * @return {boolean} 
 */
Utils.emailCheck = function(email) {
	var filterRegEx = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	return filterRegEx.test(email);
};


Utils.generatePassword = function() {
    var abc="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";
    var result = "";
    for (var i=0; i<8; i++)
    {
        var j = Math.floor( Math.random() * (abc.length));
        result = result + abc.substr(j,1);
    }
    return result;
}


jQuery.fn.enabled = function(enableIt) {
    if (enableIt == null || enableIt == undefined) {
        return !($(this).attr("disabled") == "disabled");
    }
    else {
        $(this).each(function(){
            if (enableIt) {
                $(this).removeAttr("disabled");
            } else {
                $(this).attr("disabled", "disabled");
            }
        });
        return this;
    }
};

jQuery.fn.enterPressed = function(handler) {
    var RETURN_KEYCODE = 13;
	return $(this).keyup (function (event) {
		event.keyCode === RETURN_KEYCODE && handler(event);
	});
};




 

/**
 * @class Translate
 * Set of methods for localization 
 */
var Translate = (function() {
	var resources = {};
	return {
		addResources: function(addonResources) {
			$.extend(resources, addonResources);
		},
		
		_: function(resourceId, params) {
			var translate = resources[resourceId];
			if(!translate) translate = '[' + resourceId + ']';
            return params == undefined ? translate : Utils.replace(translate, params);
		}
	};
})();


/**
 * Class for convenient and easy to work with dialog boxes. 
 * @author Semenov_D
 * @class
 */
KHSSDialogClass = function() {
    /**
     * @private
     * @type jQuery
     */
    this._dialog = null;
};

/**
 * @private
 */
KHSSDialogClass.prototype._setDefaultButton = function()
{
    this._dialog.closest('.ui-dialog').find('.ui-dialog-buttonpane BUTTON:first').addClass('default-button');
};

/**
 * @private
 */
KHSSDialogClass.prototype._closeAndExecHandler = function(handler)
{
    this._dialog.dialog('close');
    return handler ? handler() : null;
};


/**
 * Returns the content of the currently open dialog box. 
 * @type jQuery
 */
KHSSDialogClass.prototype.getDialog = function()
{
    return this._dialog;
};


/**
 * Common method for displaying a dialog box with custom options.
 * @param {String} content
 * @param {String} title
 * @param {Object} options 
 */
KHSSDialogClass.prototype.show = function(content, title, options)
{
    if(this._dialog) {
        this._dialog.dialog( "destroy" );
    } else {
        this._dialog = $('<div class="dialog"></div>').appendTo('BODY');
    }
    
    var defaultOptions = {
        title: title,
        autoOpen: false,
        width: 800,
        modal: true
    };
    
    if(options) $.extend(defaultOptions, options);
    
    this._dialog.dialog(defaultOptions);
    this._dialog.html(content);
    // remove jQuery.UI styles for buttons
    this._dialog.closest('.ui-dialog').find('.ui-dialog-buttonpane BUTTON').attr('class', '');
    this._dialog.dialog('open');
};


KHSSDialogClass.prototype.close = function()
{
    this._closeAndExecHandler(null);
};

/**
 * @memberOf KHSSDialogClass
 */
KHSSDialogClass.prototype.destroy = function()
{
    this._dialog.dialog( "destroy" );
    this._dialog.close();
    this._dialog = null;
};

/**
 * Displays a dialog with the specified content and title. Bottom shows only one button "Ok"
 * @param {String} content 
 * @param {String} title 
 * @param {Function} okButtonHandler [optional]
 */
KHSSDialogClass.prototype.modalMessageBox = function(content, title, okButtonHandler)
{
    var buttonsHash = {};
    buttonsHash[Translate._('okTitle')] = Utils.delegate(
        this,
        this._closeAndExecHandler,
        okButtonHandler ? okButtonHandler: null
    );
    
    this.show(
        this._prepareContent(content),
        title,
        {
            resizable: false,
            modal: true,
            width: 500,
            buttons: buttonsHash
        }
    );
    this._setDefaultButton();
};

/**
 * Displays a dialog with the specified content and title. Two buttons at the bottom - "Ok" and "Cancel"
 * @param {String} content 
 * @param {String} title 
 * @param {Function} okButtonHandler  [optional]
 * @param {Function} cancelButtonHandler [optional]
 */
KHSSDialogClass.prototype.modalConfirmBox = function(content, title, okButtonHandler, cancelButtonHandler)
{
    var buttonsHash = {};
    buttonsHash[Translate._('okTitle')] = Utils.delegate(
        this,
        this._closeAndExecHandler,
        okButtonHandler ? okButtonHandler : null
    );
    buttonsHash[Translate._('cancelTitle')] = Utils.delegate(
        this,
        this._closeAndExecHandler,
        cancelButtonHandler ? cancelButtonHandler : null
    );
    this.show(
        this._prepareContent(content),
        title,
        {
            resizable: false,
            modal: true,
            width: 500,
            buttons: buttonsHash
        }
    );
    this._setDefaultButton();
};

KHSSDialogClass.prototype._prepareContent = function(content) {
    var wrap = $('<p class="dialog-content"/>');
    if(typeof content == 'string') {
        wrap.html(content)
    } else {
        wrap.append(content);
    }
    return wrap;
}

// Create a static instance of KHSSDialogClass
var KHSSDialog = new KHSSDialogClass();


/**
 * Class for convenient and easy to work with .
 * @class 
 * @author Semenov_D 
 */
var KHSSLoadingClass = function() {
    /**
     * @private
     * @type jQuery
     */
    this._container = null;
    
    /**
     * @private
     * @type jQuery
     */
    this._parentContainer = null;
};

/**
 * @param {jQuery} value
 */
KHSSLoadingClass.prototype.setParentContainer = function(value) {
    this._parentContainer = value;
};

/**
 * @private 
 */
KHSSLoadingClass.prototype._init = function() {
    if( !this._container ) {
        var parent = this._parentContainer ? this._parentContainer : $('BODY');
        this._container = $('<div><div class="message"></div></div>').appendTo( parent );
    }
};

/**
 * @param {String} message
 */
KHSSLoadingClass.prototype.showOverlay = function(message, options) {
    this._init();
    if( !message ) message = Translate._('LoadingTitle');
    if( !options) options = {};
    if( !options.classname) options.classname = 'progressbar-overlay';
    if( options.title) {
	    this._container.attr({'title': options.title});
    }

    this._container.css({'height': jQuery(document).height()+"px"});
    this._container.attr({'class': options.classname});
    this._container.find('.message').html(message);
    this._container.show();
};

/**
 * @param {jQuery} placeholder
 * @param {String} message
 */
KHSSLoadingClass.prototype.showInContainer = function( placeholder, message) {
    if( !message ) message = Translate._('LoadingTitle');
    this.destroy();
    
    this._container = placeholder;
    this._container.remove('SCRIPT');
    
    var parent = this._container.parent();
    
    var wrapper = $('<div class="progressbar-greyout-area" />');
    parent.append( wrapper );
    
    wrapper.append( this._container );
    wrapper.prepend('<div class="progressbar-greyout-shadow" />');
    wrapper.prepend('<div class="progressbar-loader"><div class="message">' + message + '</div></div>');
};

KHSSLoadingClass.prototype.hide = function() {
    if( this._container ) {
        if(this._container.parent().find('.progressbar-greyout-shadow').length ) {
            this._container.parent().replaceWith(this._container);
            this._container = null;
        } else {
            this._container.hide();
        }
    }
};


KHSSLoadingClass.prototype.destroy = function() {
    this.hide();
    if( !this._container ) return;
    this._container.remove();
    this._container = null;
};

/**
 * Create a static instance of KHSSLoadingClass
 * @type {KHSSLoadingClass}
 */
var KHSSLoading = new KHSSLoadingClass();



/**
 * Static class for convenient and easy to work with dialog. 
 * @author Semenov_D 
 */
var KHSSAjax = (function() {
    var HTTP_AUTHORIZATION_REQUIRED_CODE = 401;
    return {
        /**
         * Load a remote page using an HTTP request
         * @param {Object} settings 
         * @type XMLHttpRequest
         */
        ajax: function(settings) {
            var errorHandler = settings.error;
            settings.error = function(xhr) {
                if( xhr.status == HTTP_AUTHORIZATION_REQUIRED_CODE ) {
                    if( typeof(settings.authError) == 'function' ) {
                        return settings.authError.apply(this, arguments);
                    } else {
                        window.location.reload(true);
                    }
                } else {
                    if( typeof(errorHandler) == 'function' ) {
                        return errorHandler.apply(this, arguments);
                    }
                }    
            };
            return $.ajax(settings);
        },
        
        /** 
         * Load a remote page using an HTTP POST request.
         * @param {String} url  The URL of the page to load.
         * @param {Object} data  Key/Value pairs to send to the server.
         * @param {Function(data, textStatus)} callback Will be executed when the data is loaded successfully. `this` is the options for the request.
         * @return XMLHttpRequest
         * @type XMLHttpRequest
         */
        post: function( url, data, success, dataType) {
            return this.ajax({
                'type': 'POST',
                'url': url,
                'data': data,
                'success': success,
                'dataType': dataType
            });
        },
        
        /** 
         * Load a remote page using an HTTP GET request.
         * @param {String } url The URL of the page to load.
         * @param {Object} data Key/Value pairs to send to the server.
         * @param {Function(data, textStatus)} callback Will be executed when the data is loaded successfully. `this` is the options for the request.
         * @return XMLHttpRequest
         * @type XMLHttpRequest
         */
        get: function(url, data, success, dataType ) {
            return this.ajax({
                'url': url,
                'data': data,
                'success': success,
                'dataType': dataType
            });
        },
        
        /** 
         * Load JSON data using an HTTP GET request.
         * @param {String } url The URL of the page to load.
         * @param {Object} data Key/Value pairs to send to the server.
         * @param {Function(data, textStatus)} callback Will be executed when the data is loaded successfully. `this` is the options for the request.
         * @return XMLHttpRequest
         * @type XMLHttpRequest
         */
        getJSON: function( url, data, callback ) {
            return this.ajax({
                'url': url,
                'dataType': 'json',
                'data': data,
                'success': callback
            });
        }
    };
})();


/**
 * Class for convenient and easy error output.
 * @class 
 * @author Karpilovich
 */
var KHSSErrorClass = function(container, message) {

	this._containerCreated = false;
	
	/**
     * @privare
     * @type jQuery
     */
    if (container) {
    	this._container = container;
    } else {
    	this._container = $("<div class='error-global'></div>");
    	$(this._container).prependTo("body");

    	this._containerCreated = true;
    }
    
    if(this._container.length > 1) {
        this._container = null;
        throw "KHSSErrorClass: target container must be only one.";
    }

    /**
     * @privare
     * @type jQuery
     */
    this._message = message;
    
    this.show();
};

/**
 * @param {jQuery} placeholder
 * @param {String} message
 */
KHSSErrorClass.prototype._getTemplate = function() {
	return $("<div class='error-block'><div class='error-text'/><a href='#' class='error-close'>X</a></div>");
};


KHSSErrorClass.prototype.show = function() {
    this._container.empty().append(
    	this._getTemplate()
    		.find(".error-text")
                .html(this._message)
    		.end()
    		.find(".error-close")
                .html(Translate._("closeTitle"))
                .click(Utils.delegate(this, this._onCloseHeader))
    		.end()
    )
    .show()
	.delay(10000)
	.queue(Utils.delegate(this, this.hide));
};

KHSSErrorClass.prototype.hide = function() {
	this._container.hide().empty();
	if (this._containerCreated) {
		this._container.remove();
	}
};

KHSSErrorClass.prototype._onCloseHeader = function(e) {
	e.preventDefault();
	this.hide();
};


/**
 * Class BaseAjahForm
 * @params id
 * @author Semenov_D
 */
BaseAjahForm = function() {
    this.form = null;
}
BaseAjahForm.RESULT = {
    NONE: 1,
    RENDER: 2,
    PAGE_RELOAD: 3,
    REDIRECT_TO_FRONT: 4,
    RESULT_MESSAGE: 5,
    RESULT_MESSAGE_WITH_RELOAD: 6,
    CLOSE: 7
};
BaseAjahForm.isValidData = function(html) {
    return html.length == 1 && html.hasClass('ajahForm');
}

BaseAjahForm.getResultCode = function(html) {
    return parseInt(html.attr('data-result-code')) || undefined;
}


BaseAjahForm.prototype.actionUrl = function() {
    return this.options.actionUrl;
};

BaseAjahForm.prototype.bindData = function() {
	this.request({
        type: 'GET',
        url: this.actionUrl(),
        dataType: 'html',
        cache: false,
        success: $.isFunction(this.bindDataHandler) ? Utils.delegate(this, this.bindDataHandler) : null
	});
};
// @abstract
BaseAjahForm.prototype.bindDataHandler = null;

BaseAjahForm.prototype.request = function(requestOptions) {
    var options = $.extend({}, requestOptions);
    options.error = options.error || Utils.delegate(this, function() {
        this.showError(Translate._('errorTransportRequest'));
    });
    options.success = Utils.delegate(this, function(html) {
        var onSuccessResult = requestOptions.success ? requestOptions.success(html) : null;
        if(onSuccessResult) return;

        html = $(html);
        if(!BaseAjahForm.isValidData(html)) {
            this.showError(Translate._('errorTransportRequest'));
            return;
        }

        var resultCode = BaseAjahForm.getResultCode(html);
        switch(resultCode) {
            case BaseAjahForm.RESULT.RENDER:
                this.render(html);
                break;

            case BaseAjahForm.RESULT.PAGE_RELOAD:
                KHSSLoading.showOverlay();
                window.location.reload(true);
                break;

            case BaseAjahForm.RESULT.REDIRECT_TO_FRONT:
                Utils.redirect('/')
                break;

            case BaseAjahForm.RESULT.RESULT_MESSAGE:
            case BaseAjahForm.RESULT.RESULT_MESSAGE_WITH_RELOAD:
                KHSSDialog.modalMessageBox(
                    html,
                    this.getTitle(),
                    function() {
                        KHSSDialog.close();
                        if(resultCode == BaseAjahForm.RESULT.RESULT_MESSAGE_WITH_RELOAD) {
                            window.location.reload(true);
                        }
                    }
                );
                break;

             case BaseAjahForm.RESULT.CLOSE:
                KHSSDialog.close();
                break;

            case BaseAjahForm.RESULT.NONE:
            default:
                break;
        }
    });

	KHSSAjax.ajax(options);
};

BaseAjahForm.prototype.showError = function(message) {
    var errorElement;
    if(this.form) {
        errorElement = this.form.first().hasClass('error-placeholder')
           ? this.form.first()
           : $('<div class="error-placeholder"/>').prependTo(this.form);

    } else {
        errorElement = $('.error');
    }
    new KHSSErrorClass(errorElement, message)
};

BaseAjahForm.prototype.render = function(content) {
	KHSSDialog.show(
		content,
        this.getTitle(),
        {buttons: this.getButtons()}
	);

	this.form = content.find('form');
    var sendHandler = Utils.delegate(this, this.actionSend);
    this.form.submit(sendHandler).enterPressed(sendHandler);
    this.form.find('INPUT, TEXTAREA, SELECT').first().focus();
};

BaseAjahForm.prototype.actionSend = function(e) {
	e.preventDefault();
	this.request({
		type: 'POST',
		url: this.actionUrl(),
		data: this.form.serialize(),
		dataType: 'html',
		success: $.isFunction(this.actionSendHandler)
            ? Utils.delegate(this, this.actionSendHandler)
            : null
	});
};
