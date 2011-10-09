/* Global menu with links for Home Tab redirection */
var menu_top = new Array(
	"",
	"/message/",
	"/settings/",
	"/overview/",
	"/report/",
	"/web/"
);

function set_header_tabs_links() {
    $(".header_tabs_email").click(function() {
        // overview
        location.href = menu_top[3];
    });
    
    $(".header_tabs_web").click(function() {
        location.href = menu_top[5];
    });
}

function resize_iframe(h)
{
    $("#web_content_iframe").height(h);
}

$.datepicker.setDefaults({
	changeMonth: true,
	changeYear: true,
	showAnim: 'slide',
	showOptions: {direction: 'up'},
	dateFormat: 'dd.mm.yy'
});

/* get User's time offset */
var time_global = new Date();
var time_offset = -time_global.getTimezoneOffset()/60;

$(window).resize(resize_content);

function get_timestamp() {
	// time is a global variable
	return parseInt(time_global.getTime() / 1000);
}

function format_datetime(t) {
	if (!t)
	{
		t = get_timestamp();
	}
	
	var time_str = setDateToday(t)+" "+t.toLocaleTimeString();
	
	return time_str;
}

function tooltipInit() {
    $("*[tooltip]").tipTip({
        attribute: "tooltip",
        maxWidth: "700px",
        delay: 0
    });
}

function supportDialogInit() {
 	var close = Translate._("closeTitle");
 	
 	var buttons = {};
 	buttons[close] = function() {
 		$(this).dialog("close");
 	};
	
	$("#support-dialog").dialog({
		autoOpen: false,
		modal: true,
		title: Translate._("supportDialogTitle"),
		resizable: false,
		width: 700,
		buttons: buttons
	});
	
	$("#support-dialog-link").click(function(){
		$("#support-dialog").dialog("open");
	});
}

$(document).ready(function(){

        $("#changeLoginsOwnPassword").dialog({
            autoOpen: false,
            modal: true,
            title: Translate._('changeLoginsOwnPasswordTitle'),
            width: 570,
            height: 482,
            resizable: false
        });
        $('#closechangeLoginsOwnPasswordDialog').click(function(){
            $("#changeLoginsOwnPassword").dialog('close');
        });
        $('#newPassword').attr('disabled', 'disabled');
        $('#repeatNewPassword').attr('disabled', 'disabled');
        $('#sendNewPasswordCheckbox').attr('disabled', 'disabled');
        $('#changePasswordButton').attr('disabled', 'disabled');
        $('#sendNewPasswordCheckbox').click(function(){
            if (this.checked)
            {
                $('#sendNewPasswordEmail').show();
                $('#sendOwnPassToEmailAddressLabel').show();
                if ($("#sendNewPasswordEmail").val() == '')
                {
                    $('#changePasswordButton').attr('disabled', 'disabled');
                }
                else
                {
                    //Need to run email validity check
                    //The simpliest way is to fire keyup event
                    $("#sendNewPasswordEmail").keyup();
                }
            }
            else
            {
                $('#sendNewPasswordEmail').hide();
                $('#sendOwnPassToEmailAddressLabel').hide();
                $('#sendNewPasswordEmail').val('');
                $('#changePasswordButton').removeAttr('disabled');
            }
        });

        var oldPassCheckInterval = 0;
        var newPassCheckInterval = 0;
        var emailCheckInterval, emailCheckInProcess = 0;
        $('#oldPassword').bind('keyup paste', function(){
            if(oldPassCheckInterval) clearTimeout(oldPassCheckInterval);
            oldPassCheckInterval = setTimeout(function(){
                if($('#oldPassword').val().length >= 3 && $('#oldPassword').val().length <= 20)
                {
                    $('#newPassword').removeAttr('disabled');
                    $('#repeatNewPassword').removeAttr('disabled');
                    $('#oldPasswordTick').show();
                }
                else
                {
                    $('#newPassword').attr('disabled', 'disabled');
                    $('#repeatNewPassword').attr('disabled', 'disabled');
                    $('#sendNewPasswordEmail').hide();
                    $('#sendOwnPassToEmailAddressLabel').hide();
                    $('#sendNewPasswordEmail').val('');
                    $('#oldPasswordTick').hide();
                    $('#sendNewPasswordCheckbox').removeAttr('checked');
                    $('#sendNewPasswordCheckbox').attr('disabled', 'disabled');
                }
            }, 1000);
        });
        $('#newPassword,#repeatNewPassword').bind('keyup paste', function(){
            if(newPassCheckInterval) clearTimeout(newPassCheckInterval);
            newPassCheckInterval = setTimeout(function(){
                if($('#newPassword').val() != '' && $('#repeatNewPassword').val() != '' && $('#repeatNewPassword').val() == $('#newPassword').val() && $('#newPassword').val().length >= 3 && $('#newPassword').val().length <= 20)
                {
                    $('#sendNewPasswordCheckbox').removeAttr('disabled');
                    $('#changePasswordButton').removeAttr('disabled');
                    $('#bothNewPasswordsTick').show();
                }
                else
                {
                    $('#sendNewPasswordCheckbox').attr('disabled', 'disabled');
                    $('#changePasswordButton').attr('disabled', 'disabled');
                    $('#sendNewPasswordEmail').hide();
                    $('#sendOwnPassToEmailAddressLabel').hide();
                    $('#bothNewPasswordsTick').hide();
                    $('#sendNewPasswordEmail').val('');
                    $('#sendNewPasswordCheckbox').removeAttr('checked');
                }
            }, 1000);
        });
        $('#sendNewPasswordEmail').bind('keyup paste', function(){
            if(emailCheckInterval) clearTimeout(emailCheckInterval);
            if(emailCheckInProcess) emailCheckInProcess.abort();
            emailCheckInterval = setTimeout(function(){
                var email = $('#sendNewPasswordEmail').val();
                emailCheckInProcess = KHSSAjax.post('/settings/login/ajax/', {email:email,operation: 'check-email'}, ajaxProcessCheckEmailForNewPassword, 'json');
            }, 1000);
        });
        
        $('#changePasswordButton').click(function(){
            KHSSAjax.post('/settings/login/ajax/', {operation:'change-own-pass', password:$('#oldPassword').val(), new_password:$('#newPassword').val(), emailAddress:(!$('#sendNewPasswordCheckbox').attr('checked')?0:$('#sendNewPasswordEmail').val())}, ajaxProcessChangeOwnPassword, 'json');
        });

 	var buttons = {};
 	var okTitle = Translate._("okTitle");
 	buttons[okTitle] = function() {
 	    $(this).dialog('close');
 	};
        
    $("#errorContainer").dialog({
		autoOpen: false,
		modal: true,
		title: Translate._("errorWindowTitle"),
		resizable: false,
		width: 400,
		buttons: buttons
	});

	$('.mainpage_link').click(function() {
		var link = "/";
		if ($('#home_tab_id').val() > 0)
		{
			link = menu_top[$('#home_tab_id').val()];
		}
		location.href = link;
		return false;
	});

	$('#menu_cmv').click(function(event){
		if($(event.target).attr("class")!="section_home")
			location.href = menu_top[1];
	});
	
	$('#menu_settings').click(function(event){
		if($(event.target).attr("class")!="section_home")
			location.href = menu_top[2];
	});

	$('#menu_dashboard').click(function(event){
		if($(event.target).attr("class")!="section_home")
			location.href = menu_top[3];
	});

	
	$('#menu_reports').click(function(event){
		if($(event.target).attr("class")!="section_home")
			location.href = menu_top[4];
	});

	$('#menu .menu_section').mouseover(function(){
	    if ($("#"+$(this).attr("id")+" .section_home").length > 0)
            $("#"+$(this).attr("id")+" .section_home").show();
	});

	$('#menu .menu_section').mouseout(function(){
	    if ($("#"+$(this).attr("id")+" .section_home").length > 0)
			$("#"+$(this).attr("id")+" .section_home").hide();
	});

	$('.section_home_activate_area').click(function(){
	    var t = this;

	    KHSSAjax.post(
			"/login/sethometab/", 
			{
				id: $("span", $(t).parent()).attr("id")
			}, 
			function(data) {
				$('#home_tab_id').val(data.home_tab);
				
        		$('#menu .menu_section div[id=tab_home]').attr("class", "section_home");
        		$('#menu .menu_section div[id=tab_home]').hide();
        		$('#menu .menu_section div[id=tab_home]').removeAttr("id");
        		
        		$(t).parent().attr("id", "tab_home");
        		$(t).parent().removeAttr("class");
			},"json"
		);
		
		return false;
	});

	$('input.time').timeEntry({show24Hours: true, defaultTime: new Date(0,0,0,0,0,0)});

	/* avoid "Enter" click in forms */
	$("#cmv_global_div form").submit(function(){
		return false;
	});
	
	$(".action-panel div").hover(
		function() {
			$(this).addClass("action-top-hover");
		},
		function() {
			$(this).removeClass("action-top-hover");
		}
	);
	
	$(".action-panel div").mousedown(
		function() {
			$(this).addClass("action-top-click");
		}
	);

	$(".action-panel div").mouseup(
		function() {
			$(this).removeClass("action-top-click");
		}
	);

	resize_content();

	// activate "Enter click" functionality
	$("#searchBlockInput").keyup(function(event) {
		if (event.keyCode == '13') {
			$('#searchBlockSubmit').click();
		}
	});

	// change icons for submenu  hover
	$("#submenu div[class=submenu-title]").hover(
		function() {
			if (!$("#"+$(this).attr("id")+"-one").hasClass("selected"))
				$("#"+$(this).attr("id")+"-image").toggleClass('hover');
		},
		function() {
			if (!$("#"+$(this).attr("id")+"-one").hasClass("selected"))
				$("#"+$(this).attr("id")+"-image").toggleClass('hover'); 
		}
	);

	$("#settings_users_type input").change(function(){
		if ($(this).val() == 3)
			$("#settings_users_type_auto_ldap_content").show();
		else
			$("#settings_users_type_auto_ldap_content").hide();
	});

});

function getBodyWidth() {
	var width=window.innerWidth;//Firefox
	if (document.body.clientWidth)
	{
		width=document.body.clientWidth;//IE
	}
	
	return width;
}

function formatNumberLower10(num) {
	if (num < 10) 
	{
		num = "0" + num;
	}
	return num;
}

function formatMonthLower10(num) {
	num = num + 1;
	
	return formatNumberLower10(num);
}

function setDateToday(t) {
	var date;
	if (!t)
		date = new Date();
	else
		date = t;
	
	return date.getDate()+"."+formatMonthLower10(date.getMonth())+"."+date.getFullYear();
}

function resize_content() {
    var content = $('#content-body');
	if (content.length > 0)	{
	    var h = $('BODY').height() - $('#submenu').outerHeight(true) - $('#header').outerHeight(true) - $('#footer').outerHeight(true) - 65;
	    
	    content.height(h);
        
		// it is done for scrollbar showing in groupTree
        var groupTree = $('.groupTree');
        if(groupTree.length > 0) {
            groupTree.height(parseInt(h / 2)-50);
        }
        
    	if ($("#web_content_iframe").length > 0)
    	{
    	    resize_iframe(h);
    	}
	}
}

// animation while Messages loading
function setBusy(v) {
	if ($("#loadingStatus").length)
	{
		var loadingStatus = $("#loadingStatus");
		loadingStatus.toggle(!!v);
		$("#busy").val(v);
	}
}

function emptyErrorContainer() {
	$("#errorContainer .dialog-content").html("");
	$("#errorContainer").hide();
}

function fillErrorContainer(errorMessages) {
	html = "";
	$("#errorContainer .dialog-content").html("");
        
	if (errorMessages.length > 0)
	{
		for (var i = 0; i < errorMessages.length; i++)
	    {
	    	html += '<p>'+errorMessages[i]+'</p>';
	    }
		$("#errorContainer .dialog-content").html(html);
		$("#errorContainer").dialog('open');
		$("#errorContainer").show();
	}
}

function check_smtp_enabled(elem) {
	if ($(elem).is(":checked"))
	{
		$(elem).parent().parent().addClass("tr_smtp_enabled");
	}
	else
	{
		$(elem).parent().parent().removeClass("tr_smtp_enabled");
	}
}

function checkAll(class_template)
{
	$("."+class_template).attr("checked", "checked");
}

function uncheckAll(class_template)
{
	$("."+class_template).removeAttr("checked");
}

function open_license_window()
{
    var w = document.documentElement.offsetWidth; // MSIE specific.
    var h = document.documentElement.offsetHeight; // MSIE specific.
    if (! w) {
        w = window.innerWidth; // Other browsers.
        h = window.innerHeight; 
    }

    $('#licensing-dialog').dialog(
        {
                autoOpen: false, 
                modal: true,
                resizable: true, 
                width: w * 0.75, 
                height: h * 0.75 
        } );
    $('#licensing-dialog').dialog('open');

    $('#licensing-dialog').html(
        '<iframe src="/licenses" style="width:100%; height:95%; padding:0px; margin:0px; border:0"' +
        ' frameborder="0"></iframe>');
}

function ajaxProcessCheckEmailForNewPassword(data)
{
    if ( data.is_valid == 1 )
    {
        $('#changePasswordButton').removeAttr('disabled');
        $('#emailToSendNewPasswordTick').show();
    }
    else
    {
        $('#changePasswordButton').attr('disabled', 'disabled');
        $('#emailToSendNewPasswordTick').hide();
    }
}
function ajaxProcessChangeOwnPassword(data)
{
    if ( data.success == 1 )
    {
        $('#newPassword').attr('disabled', 'disabled');
        $('#repeatNewPassword').attr('disabled', 'disabled');
        $('#sendNewPasswordEmail').hide();
        $('#sendOwnPassToEmailAddressLabel').hide();
        $('#sendNewPasswordEmail').val('');
        $('#oldPasswordTick').hide();
        $('#sendNewPasswordCheckbox').removeAttr('checked');
        $('#sendNewPasswordCheckbox').attr('disabled', 'disabled');
        $("#changeLoginsOwnPassword").dialog('close');
        // add redirect
    }
    else
    {
        new KHSSErrorClass($(".error-change-own-pass"), Translate._(data.error));
    }
}


/**
 * Class AccountContactForm
 * @params id
 * @author Semenov_D
 */
AccountContactForm = function() {
	this.options = {
        actionUrl: '/settings/login/account-change/'
    };
    this.bindData();
};
Utils.classInheritance(AccountContactForm, BaseAjahForm);

AccountContactForm.prototype.getTitle = function() {
    return Translate._('accountContactDetails');
};

AccountContactForm.prototype.getButtons = function() {
	var buttonsHash = {};
	buttonsHash[Translate._('saveDataTitle')] = Utils.delegate( this, this.actionSend );
	buttonsHash[Translate._('closeTitle')] = Utils.delegate(KHSSDialog, KHSSDialog.close);
    return buttonsHash;
};


InactivityLogout = function(timeout) {
    if(!timeout) throw "InactivityLogout: incorrect timeout.";
    
    var timeoutHandler = null;    
    
    function logout() {
        window.location.reload(true);
    }
    
    function defer() {
        if(timeoutHandler) clearTimeout(timeoutHandler);
        
        timeoutHandler = setTimeout(logout, timeout*1000);
    }
    
    $(document).ajaxSend(defer);
    
    defer();
        
    return {defer: defer}
};

SimpleSearch = function(form) {
    var clearButton = form.find('.iconClear');
    var searchInput = form.find('.inputSearchKey');

    clearButton.click(function(e){
        e.preventDefault();
        clearButton.hide();
        searchInput.val("").focus();
    });

    searchInput
        .bind('keyup', function(){ clearButton.toggle(searchInput.val() != ''); })
        .enterPressed( function(){form.submit();} )
        .trigger('keyup');
};
