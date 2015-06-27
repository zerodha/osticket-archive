<?php

	include_once dirname(__FILE__)."/config.php";
	include_once dirname(__FILE__)."/../main.inc.php";

	set_time_limit(0);

	// OsTicket has a lot of bad PHP that throws errors. Silence.
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);

	// Fetch the list of all closed tickets to archive.
	$res = db_query ('SELECT ticket_id FROM ' . TICKET_TABLE . " WHERE status='closed' AND closed < DATE_SUB(NOW(), INTERVAL " . TICKET_AGE_DAYS . ' DAY)');

	echo "\n\nTotal tickets to archive: ".db_num_rows($res)."\n";

	$n = 0;
	foreach(db_assoc_array($res, true) as $t) {
		archiveTicket($t["ticket_id"]);
		$n++;

		echo $n."\n";
	}

	// Fetch a ticket's data and save it to disk.
	function archiveTicket($id) {
		$ticket = new Ticket($id);
		
		$tid = $ticket->getExtId();

		// Delete orphan tickets.
		$owner = $ticket->getOwner();
		if(!$owner) {
			$ticket->delete();
			return;
		}

		$o_name = $owner->getName();

		$threads = $ticket->getThreadEntries ( array ('M','R','N' ) );

		$out = [
			"id" => $tid,
			"department" => $ticket->getDeptName(),
			"subject" => $ticket->getSubject(),
			"opened" => $ticket->getOpenDate(),
			"closed" => $ticket->getCloseDate(),
			"owner" => (isset($o_name->name) ? $o_name->name : ''). " <".$owner->getEmail().">",
			"thread" => []
		];

		$date = date("Y-m-d", strtotime($out["opened"]));
		$path = TICKET_PATH."/".$date."/";

		if(!@file_exists($path)) {
			@mkdir($path);
		}

		// Individual messages.
		foreach($threads as $th) {
			$out["thread"][] = [
				"id" => $th["id"],
				"staff_id" => $th["staff_id"],
				"thread_type" => $th["thread_type"],
				"poster" => $th["poster"],
				"title" => $th["title"],
				"body" => $th["body"],
				"created" => $th["created"],
				"updated" => $th["updated"],
				"attachments" => intval($th["attachments"]),
			];

			// Process attachments.
			if($th["attachments"] != 0) {
				$entry = $ticket->getThreadEntry ( $th ['id'] );
				$attachments = $entry->getAttachments();

				foreach($attachments as $a) {
					$file = Attachment::lookup ($a["attach_id"])->getFile();
					$ext = $ext = strtolower ( substr ( strrchr ( $file->getName (), '.' ), 1 ) );
					$fname = $tid."_".$th["id"].".".$ext;

					@file_put_contents(ATTACHMENT_PATH."/".$fname, $file->getData());
				}
			}
		}

		// write the ticket to disk
		file_put_contents($path.$tid, json_encode($out, JSON_PRETTY_PRINT));

		// delete the ticket from the db
		$ticket->delete();
	}
