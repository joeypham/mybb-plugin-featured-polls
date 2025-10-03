<?php
/**
 * Featured Polls language file
 * Prefix: fp_
 */

// Frontend
$l['fp_block_title'] = 'Featured Polls';
$l['fp_no_featured'] = 'No featured polls available.';
$l['fp_view_thread'] = 'View thread';
$l['fp_vote']        = 'Vote!';
$l['fp_results']     = 'Show Results';
$l['fp_undo']        = 'Undo Vote';
$l['fp_edit']        = 'Edit Poll';
$l['fp_votes']       = 'Vote(s)';
$l['fp_votethanks']  = 'Thanks for voting!';
$l['fp_unvoted']     = 'Your vote has been removed.';
$l['fp_thread']      = 'Thread:';
$l['fp_voted_marker'] = '*';
$l['fp_guest']        = 'Guest';
$l['fp_more_voters']  = '+{1}';
$l['fp_total']        = 'Total';
$l['fp_no_subject']   = '(no subject)';
$l['fp_modcp_save']   = "Save";
$l['fp_status_change'] = "Poll #{1} is now {2} (Placement #{3})";
$l['fp_unknown']      = 'Unknown';
$l['fp_no_question'] = '(no question)';
$l['fp_error_nooption'] = 'Please select an option.';
$l['fp_back_to_voting']        = 'Back to voting';


// AJAX
$l['fp_ajax_error']   = "Error";
$l['fp_ajax_unknown'] = "Unknown error";
$l['fp_ajax_added']   = "PID(s) added successfully: {1}";
$l['fp_ajax_present'] = "PID(s) already in the system: {1}";
$l['fp_ajax_invalid'] = "Invalid PID(s): {1}";

$l['fp_modcp_add_placeholder'] = "e.g. 12,34,56";

// Checkbox request
$l['fp_request']         = 'Request this poll to be featured!';
$l['fp_featured']        = 'Featured';
$l['fp_pending_review']  = 'Pending review';
$l['fp_expired']         = 'Expired';
$l['fp_queued']          = 'Queued';
$l['fp_not_submitted']   = 'Not submitted';

// Errors (cleaned)
$l['fp_error_unable_fetch_results'] = 'Unable to fetch results.';
$l['fp_error_network']              = 'Network error.';
$l['fp_error_generic']              = 'An error occurred.';
$l['fp_error_unable_undo']          = 'Unable to undo vote.';
$l['fp_error_invalidpoll']          = 'Poll not found.';
$l['fp_error_pollclosed']           = 'This poll is closed.';
$l['fp_error_postkey']              = 'Invalid POST key.';
$l['fp_error_select_option']        = 'Please select an option.';
$l['fp_error_onlyone']              = 'You can only choose one option.';
$l['fp_error_maxtoosoon']           = 'You chose too many options.';
$l['fp_error_invalidoption']        = 'Invalid option.';
$l['fp_error_noundo']               = 'You are not allowed to undo your vote.';
$l['fp_error_nopermission']         = "You do not have permission to perform this action.";
$l['fp_error_invalidpayload']       = "Invalid payload.";

// Mod CP
$l['fp_modcp_nav_title']    = 'Featured Polls';
$l['fp_modcp_page_title']   = 'Featured Polls';
$l['fp_modcp_featured']     = 'Featured Polls';
$l['fp_modcp_add_by_pid']   = 'Add poll by PID';
$l['fp_modcp_pid']          = 'Poll ID:';
$l['fp_modcp_add']          = 'Add';
$l['fp_modcp_add_notes']    = 'Enter the <b>PID</b> from the polls table (not thread ID).';

// Redirect messages (only ones used)
$l['fp_modcp_redirect_removed']    = 'Removed.';
$l['fp_modcp_redirect_approved']   = 'Selected polls approved.';
$l['fp_modcp_redirect_unfeatured'] = 'Selected polls moved back to pending.';
$l['fp_modcp_redirect_none']       = "No valid polls were added.";
$l['fp_modcp_redirect_added']      = "Added polls: {1}.";
$l['fp_modcp_redirect_added_skipped'] = "Added polls: {1}. Skipped (already listed/invalid): {2}.";
$l['fp_modcp_redirect_all_skipped']   = "Polls {1} are already in Featured Polls.";

// Lists
$l['fp_modcp_requested_polls']    = 'Requested Polls';
$l['fp_modcp_approve_selected']   = 'Approve Selected';
$l['fp_modcp_unfeature_selected'] = 'Unfeature Selected';
$l['fp_modcp_remove_selected']    = 'Remove Selected';
$l['fp_modcp_thread_label']       = 'Thread:';

$l['fp_modcp_status_featured'] = "Featured";
$l['fp_modcp_status_pending']  = "Pending";
$l['fp_modcp_status_expired']  = "Expired";
$l['fp_modcp_status_queued']   = "Queued";

$l['fp_modcp_requested_on']    = 'Requested on:';
$l['fp_modcp_featured_none']   = 'No featured polls yet.';
$l['fp_modcp_request_none']    = 'No poll requests pending.';

$l['fp_modcp_pid_label']       = 'PID';
$l['fp_breadcrumb_modcp']      = 'Mod CP';

$l['fp_modcp_expires']                   = 'Expires';
$l['fp_modcp_redirect_expiry_updated']   = 'Expiry date updated.';
$l['fp_no_expiry']                       = 'No expiry';
$l['fp_never']                           = 'Never';

$l['fp_modcp_expired_polls']   = 'Expired Featured Polls';
$l['fp_modcp_expired_on']      = 'Expired on:';
$l['fp_modcp_expired_none']    = 'No expired polls recorded.';
$l['fp_modcp_expired']         = 'Expired Polls';

$l['fp_modcp_redirect_order_saved'] = "The poll order has been updated.";

$l['fp_modcp_queue']        = "Queued Polls";
$l['fp_modcp_queue_none']   = "No polls in queue.";
$l['fp_modcp_drop_here']    = "Drop polls here";

$l['fp_modcp_remove_featured'] = "Remove from Featured";
$l['fp_modcp_removed']         = "Poll removed from featured";

$l['fp_modcp_bulk_actions']   = "Bulk Actions";
$l['fp_modcp_btn_approve']    = "Move to Featured";
$l['fp_modcp_btn_queue']      = "Move to Queue";
$l['fp_modcp_btn_unfeature']  = "Move to Pending";
$l['fp_modcp_btn_remove']     = "Remove Completely";
$l['fp_modcp_btn_update_expiry'] = "Update Expiry Dates";

$l['fp_modcp_redirect_queued']       = "Polls moved to Queue.";
$l['fp_modcp_error_already_listed']  = "Poll ID {1} is already listed in Featured Polls.";

$l['fp_modcp_added_success']         = "Poll(s) added successfully: {1}";
$l['fp_modcp_added_partial']         = "Added: {1}. Already in system: {2}";
$l['fp_modcp_already_in_system']     = "All provided polls are already in the system.";

$l['fp_modcp_added_on'] = 'Added on:';
$l['fp_modcp_update_expiry'] = 'Update Expiry';
$l['fp_modcp_queue_place'] = 'Place in queue:';

$l['fp_modcp_btn_expire'] = "Move to Expired";
$l['fp_modcp_bulk_expired'] = "Selected polls have been marked as expired.";

$l['fp_modcp_featured_limit_reached'] = "You can only feature {1} polls at once.";