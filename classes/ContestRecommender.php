<?php
/* This is the interface the contest handler classes need to implement. It defines a handler method for every relevant
 * message type.
 */

interface Recommender {
	/* this function handles incoming impression messages. it is responsible for posting the data back to the contest server
	 * using ContestMessage::postTo('stdout').
	 *
	 * @param ContestImpression $msg the incoming impression, use the provided accessor methods such as getClient() and getItem() to access the contained data
	 */
	function recommend(ContestImpression $msg);
}
