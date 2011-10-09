
/**
 * Abstract Base Class for front forms
 * @author Semenov_D 
 * @class FrontDigestForm
 * @constructor
 * @param {jQuery} container
 */
FrontForm = function(container) {
	if( !container ) return;
    /* @private */
	this.container = container;
    /* @private */
    this.sendData = false;
    this.init();
};

FrontForm.RESULT_CODE_OK = 1;
FrontForm.RESULT_CODE_ERROR = 2;


/**
 * Abstract method - event for send action
 */
FrontForm.prototype.onSubmit = function() {};

/**
 * Abstract method - returns true if the data is entered into the form are correct
 * @return {boolean}
 */
FrontForm.prototype.isDataCorrect =  function() {};


FrontForm.prototype.init = function() {
    this.container.submit( Utils.delegate(this, this.submitFormHandler) );
    setInterval(Utils.delegate(this, this.checkFormAction), 500);
    this.find('INPUT:visible').first().focus();
};

/**
 * Sets form to send data mode
 */
FrontForm.prototype.requestDataStart = function() {
    if(this.sendData) return;
    this.find('.submit-process').css('visibility', 'visible');
    this.find('INPUT, SELECT').enabled(false);
    this.sendData = true;
    this.checkFormAction();
}

/**
 * Unsets send data mode for form
 */
FrontForm.prototype.requestDataStop = function() {
    if(!this.sendData) return;
    this.find('.submit-process').css('visibility', 'hidden');
    this.find('INPUT, SELECT').enabled(true);
    this.sendData = false;
    this.checkFormAction();
}


/**
 * @private
 * @param {event} e
 */
FrontForm.prototype.submitFormHandler = function(e) {
    e.preventDefault();
    if( this.isDataCorrect() ) this.onSubmit();
};

/**
 * @private
 */
FrontForm.prototype.checkFormAction = function() {
    var isButtonEnable = !this.sendData && this.isDataCorrect();
    this.find('.submit')
        .attr("disabled", !isButtonEnable )
        .toggleClass('btn', isButtonEnable);
};

/**
 * Find element in form container
 * @param selector
 * @return {jQuery}
 */
FrontForm.prototype.find = function(selector) {
    return this.container.find(selector);
};

/**
 * Shows a warning message
 * @param {string} message
 */
FrontForm.prototype.messageShow = function(message) {
    this.find('.message').html(message).css('display', 'block');
};

/**
 * Hides a warning message
 */
FrontForm.prototype.messageHide = function() {
    this.find('.message').css('display', 'none');
};

/**
 * Class for quarantine digest form on front page
 * @class FrontDigestForm
 * @constructor
 * @base FrontForm
 * @param {jQuery} container
 * @author Semenov_D 
 */
FrontDigestForm = function(container) {
    // call parent constructor
    FrontDigestForm.superclass.constructor.apply(this, arguments);

    /**
     * @private
     */
    this.timer = null;

    /**
     * @private
     */
    this.oldEnteredDomain = '';

    /**
     * @private
     * Current id of CAPTCHA. If == '' then CAPTCHA does not yet exist
     */
    this.captchaId = '';

    /**
     * @private
     * Current host of CAPTCHA.
     */
    this.captchaHost = '';
    
    /**
     * @private
     * Hash of the pairs { domainName1: host1, domainName2: host2, ... }
     */
    this.domainHosts = {};

	this.find('[name=email]').bind(
		['blur', 'focus', 'change', 'keyup'].join(' '),
		Utils.delegate(this, this.onChangeEmailHandler)
	);
    this.resetCaptcha();
};
FrontDigestForm.CAPTCHA_CODE_LEN = 6;
FrontDigestForm.REQUEST_HOST_PAUSE = 2000; // in msec

Utils.classInheritance(FrontDigestForm, FrontForm);

FrontDigestForm.prototype.isDataCorrect =  function() {
    if( this.captchaId == '' ) return false;
    if( this.getCaptchaInputKey().length != FrontDigestForm.CAPTCHA_CODE_LEN ) return false;

    return true;
};

/**
 * @inherits FrontForm.onSubmit
 */
FrontDigestForm.prototype.onSubmit = function() {
    this.messageHide();
    this.requestDataStart();

    var localUrl = this.container.attr('action');

    var dateObject = new Date();

    KHSSAjax.ajax({
        url: this.captchaHost ? Utils.replace("https://%host%url?callback=?", {'host': this.captchaHost, 'url': localUrl}) : localUrl,
		type: this.captchaHost ? 'GET' : 'POST',
		dataType: 'json',
		data: {
            email: this.getEmail(),
            captcha: this.getCaptchaInputKey(),
            id: this.captchaId,
            period: this.getPeriod(),
            gmt: dateObject.getTimezoneOffset() * 60
        },
		success: Utils.delegate(this, this.onSubmitHandler),
		error: Utils.delegate(this, function() {
            this.requestDataStop();
            this.messageShow( Translate._('unknownError') );
            this.getCapchaInfo();
	    })
	});
};


/**
 * Parse response from server
 * @private
 */
FrontDigestForm.prototype.onSubmitHandler = function(data) {
    this.getCapchaInfo();
    this.requestDataStop();
    if(data.code == FrontForm.RESULT_CODE_OK) {
       this.messageShow('<span class="success">' + data.message + '</span>' );
       this.find('#digest-fields-parent').hide(); // Hide the fields.
    } else {
        this.messageShow(data.message ? data.message : Translate._('unknownError') );
    }
};


FrontDigestForm.prototype.getEmail = function() {
    return this.find('[name=email]').val();
};

FrontDigestForm.prototype.getPeriod = function() {
    return this.find('[name=period]').val();
};

FrontDigestForm.prototype.getCaptchaInputKey = function() {
    return this.find('[name=captcha]').val();
};


/**
 * Processing change email
 * @private
 */
FrontDigestForm.prototype.onChangeEmailHandler = function(e) {
    var domain = this.getEmail().split('@')[1];
    domain = domain || '';

    if( this.oldEnteredDomain != domain ) {
        this.resetCaptcha();
        if(this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
        this.timer = setTimeout(Utils.delegate(this, this.getDomainInfo, domain), FrontDigestForm.REQUEST_HOST_PAUSE);
        this.oldEnteredDomain = domain;
    }
};

/**
 * Get info about domain - host
 */
FrontDigestForm.prototype.getDomainInfo = function() {
    this.messageHide();
    var requestedDomain = this.oldEnteredDomain;
    var host = this.domainHosts[requestedDomain];
    // this.find('.captcha-image').html('<div class="get-captcha-info-progress"></div>');

    if( host!== undefined ) {
        this.setHost(host);
    } else {
        KHSSAjax.ajax({
            url: '/auth/domain-info',
            dataType: 'json',
            data: {domain: requestedDomain},
            success: Utils.delegate(this, function(data){
                if(data.host !== undefined ) {
                    this.domainHosts[requestedDomain] = data.host;
                    if(requestedDomain == this.oldEnteredDomain) {
                        this.setHost(data.host);
                    }
                    return;
                }
                // Duplicate domain - exists on several clusters.
                if (data.is_duplicate) {
                    // Show localised error
                    this.messageShow(data.error);
                }
                this.resetCaptcha();
            }),
            error: Utils.delegate(this, function() {
                this.captchaError( Translate._('getDomainInfoError') );
            })
        });
    }
};

FrontDigestForm.prototype.getCapchaInfo = function() {
    // reset input field
    this.find('[name=captcha]').val('');

    var localUrl = '/auth/captcha-info';
    KHSSAjax.ajax({
        url: this.captchaHost ? Utils.replace("https://%host%url?callback=?", {'host': this.captchaHost, 'url': localUrl}) : localUrl,
        dataType: 'json',
        cache: false,
        success: Utils.delegate(this, function(data){
            if(data.key && data.code) {
                this.find('.captcha-image').html(data.code).find('IMG').click(Utils.delegate(this, this.getCapchaInfo));
                this.captchaId = data.key;
                this.find('.field-captha').show('normal');
                return;
            }
            this.captchaError( data.error ? data.error : Translate._('unknownError') );
        }),
        error: Utils.delegate(this, function() {
            this.captchaError();
        })
    });
};

FrontDigestForm.prototype.resetCaptcha = function() {
    this.find('.captcha-image').empty();
    this.find('.field-captha').hide('normal');
    this.captchaId = '';

};

FrontDigestForm.prototype.setHost = function(host) {
    this.captchaHost = host;
    this.getCapchaInfo();
};

FrontDigestForm.prototype.captchaError = function(error) {
    this.messageShow(error ? error : Translate._('unknownError') );
    this.find('.field-captha').hide('normal');
}


/**
 * Class for login form on front page
 * @class FrontLoginForm
 * @constructor
 * @base FrontForm
 * @param {jQuery} container
 * @author Semenov_D 
 */
FrontLoginForm = function(container) {
    // call parent constructor
    FrontLoginForm.superclass.constructor.apply(this, arguments);

    this.remoteHost = '';
    // Add the menu_top values into a hidden field, as a 
    // colon separated list, e.g.
    // /settings:/something_else:/another/thing
    this.find('[name=menu_top]').val(
        menu_top.join(":")
    );
};
Utils.classInheritance(FrontLoginForm, FrontForm);

FrontLoginForm.prototype.isDataCorrect =  function() {
    return this.getLogin() != '' && this.getPassword() != '';
};

// Override the submitFormHandler so it won't do
// preventDefault(), as that stops the form from ever submitting
// through the browser.
FrontLoginForm.prototype.submitFormHandler = function(e) {
    if( this.isDataCorrect() ) this.onSubmit(e);
};

/**
 * @inherits FrontForm.onSubmit
 */
FrontLoginForm.prototype.onSubmit = function(e) {
	this.messageHide();
    if (this.checkComplete) {
        // We already did the AJAX. 
        // Check is complete, we may have updated the form action
        // to point to where we need to go.
        // Let the browser do its default action.
        return;
    } else {
        // We will handle the submission via AJAX.
        // Don't let the browser submit the form yet.
        e.preventDefault();
    }

    this.requestDataStart();
    
    var localUrl = 'logon.php';
    KHSSAjax.ajax({
        url: localUrl,
        type: 'POST',
        dataType: 'json',
        data: {
            name: this.getLogin(),
            password: this.getPassword()
        },
        success: Utils.delegate(this, this.onSubmitHandler),
        error: Utils.delegate(this, function() {
            this.requestDataStop();
            this.messageShow( Translate._('unknownError') );
        })
    });
};

/**
 * Parse response from server
 * JSON response from logincheck may contain the following properties:
 *  valid = boolean to tell us whether the login is valid.
 *  host = the hostname to use for login (if present)
 *  path = the path to use for login (if present)
 */
FrontLoginForm.prototype.onSubmitHandler = function(inData) {
    if (! (parseInt(inData.valid))) {
        this.requestDataStop();
        this.messageShow(inData.error ? inData.error : Translate._('unknownError') );
        return; // AJAX Login failed.
    }
    
    this.checkComplete = true; // Ensure we don't create a loop.
    // Host appears in the response... submit to a different host
    // And a different path.
	if (inData.host) {
        // container is a jQuery object, containing a single form.
        var path = inData.path;
        var host = inData.host;
        this.container.attr('action', 'https://' + host + path);
	}
    this.find('INPUT').enabled(true);
    // Re-submit the form with the (possibly) modified ACTION URL.
    this.container.submit().animate({opacity: 0.1}, 1000);
};

FrontLoginForm.prototype.getPassword = function() {
    return this.find('input[name=password]').val();
};

FrontLoginForm.prototype.getLogin = function() {
    return this.find('input[name=login]').val();
};

