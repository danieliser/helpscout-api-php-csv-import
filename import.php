<?php

require_once 'init.php';

$backlog = include 'uploads/backlog-current.php';

use HelpScout\ApiClient;

$client = ApiClient::getInstance();
$client->setKey( HS_API_KEY );

// The mailbox associated with the conversation
$mailbox = new \HelpScout\model\ref\MailboxRef();
$mailbox->setId(49350);

$failed = array();

foreach ( $backlog as $key => $row ) {
	try {
		// Attachments: attachments must be sent to the API before they can
		// be used when creating a thread. Use the hash value returned when
		// creating the attachment to associate it with a ticket.

		$attachments = array();

		if ( ! empty ( $row['attachment'] ) ) {

			foreach ( explode(' , ', $row['attachment'] ) as $url ) {
				$attachment = new HelpScout\model\Attachment();

				if ( endswith( $url, '.png' ) ) {
					$attachment->setMimeType( 'image/png' );
				}

				if ( endswith( $url, '.jpg' ) || endswith( $url, '.jpeg' ) ) {
					$attachment->setMimeType( 'image/jpeg' );
				}

				$attachment->setFileName( filename_from_url( $url ) );
				$attachment->setData( file_get_contents( $url ) );
				$client->createAttachment( $attachment );

				$attachments[] = $attachment;
			}

		}

		// Create the customer
		$customer = new \HelpScout\model\ref\CustomerRef();
		$customer->setId( null );
		$customer->setEmail( $row['email'] );
		$customer->setFirstName( $row['f_name'] );
		$customer->setLastName( $row['l_name'] );

		// Create the conversation
		$conversation = new \HelpScout\model\Conversation();
		$conversation->setSubject( $row['reason'] . ': ' . $row['subject'] );
		$conversation->setCreatedAt( format_api_date( $row['date'] ) );
		$conversation->setMailbox( $mailbox );
		$conversation->setCustomer( $customer );
		$conversation->setType( 'email' );

		$tags = array(
			'backlog',
			strtolower( $row['reason'] )
		);

		if ( $row['extension'] == 'Yes' ) {
			$tags[] = strtolower( $row['extension_name'] );
		}

		$conversation->setTags( $tags );

		// A conversation must have at least one thread
		$thread = new \HelpScout\model\thread\Customer();
		$thread->setCreatedAt( format_api_date( $row['date'] ) );

		$body = '<strong>Website: </strong>' . $row['website'] . "\r\n";

		if ( $row['extension'] == 'Yes' ) {
			$body .= '<strong>Extension: </strong>' . $row['extension_name'] . "\r\n";
		}

		$body .= "\r\n\r\n" . $row['body'];

		$thread->setBody( $body );
		$thread->setStatus( 'active' );

		// Add attachments if they exist.
		if ( ! empty( $attachments ) ) {
			$thread->setAttachments( $attachments );
		}

		// Create by: required
		$thread->setCreatedBy( $customer );

		$conversation->setThreads( array( $thread ) );
		$conversation->setCreatedBy( $customer );

		$client->createConversation( $conversation );

		echo $key . "+\r\n";

		unset( $backlog[ $key ] );
		save_array_to_file( $backlog, 'uploads/backlog-left.php' );

	} catch (HelpScout\ApiException $e) {
		$failed[] = $row;
		echo $e->getMessage();
		print_r($e->getErrors());
		save_array_to_file( $failed, 'uploads/backlog-failed.php' );
	}

}



add_action( 'wp_footer', 'my_custom_popup_scripts', 500 );
function my_custom_popup_scripts() { ?>
	<script type="text/javascript">
		(function ($) {

			$('.pum_sub_form').on('pumNewsletterSuccess', function () {
				$(this).parents('.popmake').popmake('close');
			});


		}(jQuery))
	</script><?php
}
