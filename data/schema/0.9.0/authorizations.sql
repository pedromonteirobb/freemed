# $Id$
#
# Authors:
#      Jeff Buchbinder <jeff@freemedsoftware.org>
#
# Copyright (C) 1999-2006 FreeMED Software Foundation
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

CREATE TABLE `authorizations` (
	authdtadd		DATE NOT NULL DEFAULT NOW(),
	authdtmod		DATE NOT NULL DEFAULT NOW(),
	authpatient		INT UNSIGNED NOT NULL,
	authdtbegin		DATE,
	authdtend		DATE,
	authnum			VARCHAR (25),
	authtype		INT UNSIGNED,
	authprov		INT UNSIGNED,
	authprovid		VARCHAR (20),
	authinsco		INT UNSIGNED,
	authvisits		INT UNSIGNED,
	authvisitsused		INT UNSIGNED,
	authvisitsremain	INT UNSIGNED,
	authcomment		VARCHAR (100),
	id			INT UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY 		( id ),

	# Define keys

	KEY			( authpatient, authdtbegin, authdtend )
);

