/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2008 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

package org.freemedsoftware.gwt.client;

import org.freemedsoftware.gwt.client.Public.LoginAsync;
import org.freemedsoftware.gwt.client.screen.MainScreen;

import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.RootPanel;

/**
 * Entry point classes define <code>onModuleLoad()</code>.
 */
public class FreemedInterface implements EntryPoint {

	protected boolean active = false;

	protected LoginDialog loginDialog;

	protected MainScreen mainScreen;

	/**
	 * This is the entry point method.
	 */
	public void onModuleLoad() {
		// Test to make sure we're logged in
		if (Util.isStubbedMode()) {
			resume();
		} else {
			loginDialog = new LoginDialog();
			loginDialog.setFreemedInterface(this);
			LoginAsync service = null;
			try {
				service = (LoginAsync) Util
						.getProxy("org.freemedsoftware.gwt.Public.Login");
				service.LoggedIn(new AsyncCallback() {
					public void onSuccess(Object result) {
						Boolean r = (Boolean) result;
						if (r.booleanValue()) {
							// If logged in, continue
							MainScreen mainScreen = new MainScreen();
							RootPanel.get("rootPanel").add(mainScreen);
						} else {
							// Force login loop
							loginDialog.show();
						}
					}

					public void onFailure(Throwable t) {
						Window
								.alert("Unable to contact RPC service, try again later.");
					}
				});
			} catch (Exception e) {
			}
		}
	}

	public void resume() {
		if (!active) {
			mainScreen = new MainScreen();
			RootPanel.get("rootPanel").add(mainScreen);
		} else {
			RootPanel.setVisible(RootPanel.get("rootPanel").getElement(), true);
		}
	}
}