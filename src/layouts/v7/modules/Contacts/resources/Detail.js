/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Contacts_Detail_Js", {}, {
	calculateAge: function (container) {
		var headerContainer = jQuery('.detailview-header');
		var customRow = headerContainer.find('.salesorder-contact-info');
		if(customRow.length == 0) {
			customRow = jQuery(
			'<div class="row salesorder-contact-info" style="padding:10px; border:0px; border-radius:4px; margin-top:10px;">' +
				'<div class="col-sm-12" style="border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' +
					'<p style="margin:0; font-weight:bold; color:#333;">Edad: ' + '</p>' +
				'</div>' +
			'</div>');
			headerContainer.append(customRow);
		}
	},

	registerAjaxPreSaveEvents: function (container) {
		var thisInstance = this;
		app.event.on(Vtiger_Detail_Js.PreAjaxSaveEvent, function (e) {
			if (!thisInstance.checkForPortalUser(container)) {
				e.preventDefault();
			}
		});
	},
	/**
	 * Function to check for Portal User
	 */
	checkForPortalUser: function (form) {
		var element = jQuery('[name="portal"]', form);
		var response = element.is(':checked');
		
		if (response) {
			var primaryEmailField = jQuery('[data-name="email"]');

			if (primaryEmailField.length == 0) {
				app.helper.showErrorNotification({message: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS')});
				return false;
			}

			var primaryEmailValue = primaryEmailField.data("value");
			if (primaryEmailValue == "") {
				app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER')});
				return false;
			}
		}
		return true;
	},
	/**
	 * Function which will register all the events
	 */
	registerEvents: function () {
		var form = this.getForm();
		this._super();
		this.registerAjaxPreSaveEvents(form);
		this.calculateAge();
	}
})
