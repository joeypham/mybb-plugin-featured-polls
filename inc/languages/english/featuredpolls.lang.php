<?php
/**
 * Featured Polls language file
 * Prefix: fp_
 * Organized by section for clarity.
 */

// -----------------------------------------------------------------------------
// FRONTEND — DISPLAY & USER ACTIONS
// -----------------------------------------------------------------------------
$l['fp_block_title']      = 'Featured Polls';
$l['fp_no_featured']      = 'No featured polls available.';
$l['fp_view_thread']      = 'View thread';
$l['fp_vote']             = 'Vote!';
$l['fp_results']          = 'Show Results';
$l['fp_undo']             = 'Undo Vote';
$l['fp_edit']             = 'Edit Poll';
$l['fp_votes']            = 'Vote(s)';
$l['fp_votethanks']       = 'Thanks for voting!';
$l['fp_unvoted']          = 'Your vote has been removed.';
$l['fp_thread']           = 'Thread:';
$l['fp_voted_marker']     = '*';
$l['fp_guest']            = 'Guest';
$l['fp_more_voters']      = '+{1}';
$l['fp_total']            = 'Total';
$l['fp_no_subject']       = '(no subject)';
$l['fp_no_question']      = '(no question)';
$l['fp_back_to_voting']   = 'Back to voting';
$l['fp_featured']         = 'Featured';
$l['fp_expired']          = 'Expired';
$l['fp_queued']           = 'Queued';
$l['fp_pending_review']   = 'Pending review';
$l['fp_not_submitted']    = 'Not submitted';
$l['fp_unknown']          = 'Unknown';
$l['fp_request']          = 'Request this poll to be featured!';
$l['fp_status_change']    = 'Poll #{1} is now {2} (Placement #{3})';
$l['fp_invalid_pid']      = 'Invalid poll ID.';

// -----------------------------------------------------------------------------
// AJAX MESSAGES & ERRORS
// -----------------------------------------------------------------------------
$l['fp_ajax_added']   = 'PID(s) added successfully: {1}';
$l['fp_ajax_present'] = 'PID(s) already in the system: {1}';
$l['fp_ajax_invalid'] = 'Invalid PID(s): {1}';
$l['fp_ajax_error']   = 'Error';
$l['fp_ajax_unknown'] = 'Unknown error';

// -----------------------------------------------------------------------------
// ERROR HANDLING (FRONTEND + AJAX)
// -----------------------------------------------------------------------------
$l['fp_error_generic']             = 'An error occurred.';
$l['fp_error_network']             = 'Network error.';
$l['fp_error_unable_fetch_results']= 'Unable to fetch results.';
$l['fp_error_invalidpoll']         = 'Poll not found.';
$l['fp_error_pollclosed']          = 'This poll is closed.';
$l['fp_error_postkey']             = 'Invalid POST key.';
$l['fp_error_select_option']       = 'Please select an option.';
$l['fp_error_nooption']            = 'Please select an option.';
$l['fp_error_onlyone']             = 'You can only choose one option.';
$l['fp_error_maxtoosoon']          = 'You can only select a maximum of {1} options.';
$l['fp_error_invalidoption']       = 'Invalid option.';
$l['fp_error_unable_undo']         = 'Unable to undo vote.';
$l['fp_error_noundo']              = 'You are not allowed to undo your vote.';
$l['fp_error_nopermission']        = 'You do not have permission to perform this action.';
$l['fp_error_invalidpayload']      = 'Invalid payload.';
$l['fp_no_permission']             = 'You do not have permission to perform this action.';

// -----------------------------------------------------------------------------
// MOD CP — NAVIGATION & PAGE TITLES
// -----------------------------------------------------------------------------
$l['fp_modcp_nav_title']     = 'Featured Polls';
$l['fp_modcp_page_title']    = 'Featured Polls';
$l['fp_breadcrumb_modcp']    = 'Mod CP';

// -----------------------------------------------------------------------------
// MOD CP — LIST SECTIONS & HEADERS
// -----------------------------------------------------------------------------
$l['fp_modcp_featured']      = 'Featured Polls';
$l['fp_modcp_requested_polls']= 'Requested Polls';
$l['fp_modcp_expired_polls'] = 'Expired Polls';
$l['fp_modcp_queue']         = 'Queued Polls';

// -----------------------------------------------------------------------------
// MOD CP — STATUSES
// -----------------------------------------------------------------------------
$l['fp_modcp_status_featured'] = 'Featured';
$l['fp_modcp_status_pending']  = 'Pending';
$l['fp_modcp_status_expired']  = 'Expired';
$l['fp_modcp_status_queued']   = 'Queued';
$l['fp_modcp_slots']		   = 'slots';

// -----------------------------------------------------------------------------
// MOD CP — BUTTONS & BULK ACTIONS
// -----------------------------------------------------------------------------
$l['fp_modcp_bulk_actions']   = 'Bulk Actions';
$l['fp_modcp_btn_approve']    = 'Move to Featured';
$l['fp_modcp_btn_unfeature']  = 'Move to Pending';
$l['fp_modcp_btn_queue']      = 'Move to Queue';
$l['fp_modcp_btn_expire']     = 'Move to Expired';
$l['fp_modcp_btn_remove']     = 'Remove Completely';
$l['fp_modcp_btn_update_expiry'] = 'Update Expiry Dates';

// -----------------------------------------------------------------------------
// MOD CP — TABLE / FORM LABELS
// -----------------------------------------------------------------------------
$l['fp_modcp_thread_label']  = 'Thread:';
$l['fp_modcp_pid_label']     = 'PID';
$l['fp_modcp_expires']       = 'Expires';
$l['fp_modcp_added_on']      = 'Added on:';
$l['fp_modcp_queue_place']   = 'Position in queue:';
$l['fp_no_expiry']           = 'No expiry';
$l['fp_never']               = 'Never';
$l['fp_modcp_save']          = 'Save';

// -----------------------------------------------------------------------------
// MOD CP — INSTRUCTIONS & PLACEHOLDERS
// -----------------------------------------------------------------------------
$l['fp_modcp_add_by_pid']      = 'Add poll by PID';
$l['fp_modcp_add_placeholder'] = 'e.g. 12,34,56';
$l['fp_modcp_add']             = 'Add';

// -----------------------------------------------------------------------------
// MOD CP — SYSTEM MESSAGES / REDIRECTS
// -----------------------------------------------------------------------------
$l['fp_modcp_redirect_unfeatured']     = 'Selected polls moved back to pending.';
$l['fp_modcp_redirect_order_saved']    = 'The poll order has been updated.';
$l['fp_modcp_redirect_queued']         = 'Polls moved to Queue.';
$l['fp_modcp_redirect_expired']        = 'Poll moved to expired.';
$l['fp_modcp_redirect_expiry_updated'] = 'Expiry date updated.';
$l['fp_modcp_removed']                 = 'Poll removed from featured list.';
$l['fp_redirect_removed']              = 'Removed.';

// -----------------------------------------------------------------------------
// MOD CP — VISUAL & UI ELEMENTS
// -----------------------------------------------------------------------------
$l['fp_modcp_drop_here']               = 'Drop polls here';
$l['fp_modcp_featured_limit_reached']  = 'You can only feature {1} polls at once.';
$l['fp_modcp_expiry_updated']          = 'Expiry updated.';
$l['fp_modcp_featured_none']           = 'No featured polls yet.';
$l['fp_modcp_request_none']            = 'No poll requests pending.';
$l['fp_modcp_requested_by']            = 'Requested by:';
$l['fp_modcp_queue_none']              = 'No polls in queue.';
$l['fp_modcp_manage_tools']            = 'Poll Management Tools';

// -----------------------------------------------------------------------------
// MOD CP — NOTICES / WARNINGS
// -----------------------------------------------------------------------------
$l['fp_notice_limit_reached_title'] = 'Featured Polls Limit Reached';
$l['fp_notice_limit_reached_desc']  = 'You have reached the maximum of {1} featured polls. To feature new ones, unfeature or expire existing polls first.';
