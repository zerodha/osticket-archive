<?php

	// Closed tickets older than this will be archived.
	define("TICKET_AGE_DAYS", 90);

	// Directory where tickets should be archived.
	define("TICKET_PATH", dirname(__FILE__)."/tickets");

	// Directory where attachments should be archived.
	define("ATTACHMENT_PATH", dirname(__FILE__)."/attachments");
