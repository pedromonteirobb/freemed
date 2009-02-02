/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
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

import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.TabPanel;

public abstract class ScreenInterface extends Composite {

	protected CurrentState state = null;

	public ScreenInterface() {
		super();
		// setStylePrimaryName(this.getElement(), "freemed-ScreenInterface");
		// setSize("100%", "100%");
	}

	public void assignState(CurrentState s) {
		state = s;
	}

	public void setState(CurrentState s) {
		state = s;
	}

	public CurrentState getState() {
		return state;
	}

	public void closeScreen() {
		TabPanel t = state.getTabPanel();
		t.selectTab(t.getWidgetIndex(this) - 1);
		t.remove(t.getWidgetIndex(this));
	}
}
