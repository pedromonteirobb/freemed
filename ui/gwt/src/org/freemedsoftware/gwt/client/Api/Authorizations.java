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

package org.freemedsoftware.gwt.client.Api;

import java.util.Date;
import java.util.HashMap;

import com.google.gwt.user.client.rpc.RemoteService;

public interface Authorizations extends RemoteService {
	public Integer[] FindByCoverage(Integer coverageId);

	public HashMap<String, String> GetAuthorization(Integer authorizationId);

	public Boolean Replace(Integer authorizationId);

	public Boolean UseAuthorization(Integer authorizationId);

	public Boolean Valid(Integer authorizationId, Date comparisonDate);

	public Integer[] ValidSet(Integer[] authSet, Date comparisonDate);
}
