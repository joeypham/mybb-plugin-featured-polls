<?php
/**
 * T?p ng�n ng? cho Featured Polls
 * Ti?n t?: fp_
 * ��?c t? ch?c theo t?ng ph?n �? d? hi?u.
 */

// -----------------------------------------------------------------------------
// GIAO DI?N NG�?I D�NG � HI?N TH? & H�NH �?NG
// -----------------------------------------------------------------------------
$l['fp_block_title']      = 'B?nh ch?n n?i b?t';
$l['fp_no_featured']      = 'Hi?n ch�a c� b?nh ch?n n?i b?t n�o.';
$l['fp_view_thread']      = 'Xem ch? �?';
$l['fp_vote']             = 'B?nh ch?n!';
$l['fp_results']          = 'Xem k?t qu?';
$l['fp_undo']             = 'H?y b?nh ch?n';
$l['fp_edit']             = 'Ch?nh s?a b?nh ch?n';
$l['fp_votes']            = 'L�?t b?nh ch?n';
$l['fp_votethanks']       = 'C?m �n b?n �? b?nh ch?n!';
$l['fp_unvoted']          = 'B?nh ch?n c?a b?n �? b? h?y.';
$l['fp_thread']           = 'Ch? �?:';
$l['fp_voted_marker']     = '*';
$l['fp_guest']            = 'Kh�ch';
$l['fp_more_voters']      = '+{1}';
$l['fp_total']            = 'T?ng c?ng';
$l['fp_no_subject']       = '(kh�ng c� ti�u �?)';
$l['fp_no_question']      = '(kh�ng c� c�u h?i)';
$l['fp_back_to_voting']   = 'Quay l?i b?nh ch?n';
$l['fp_featured']         = 'N?i b?t';
$l['fp_expired']          = 'H?t h?n';
$l['fp_queued']           = '�ang ch?';
$l['fp_pending_review']   = '�ang ch? duy?t';
$l['fp_not_submitted']    = 'Ch�a g?i';
$l['fp_unknown']          = 'Kh�ng x�c �?nh';
$l['fp_request']          = 'Y�u c?u l�m n?i b?t b?nh ch?n n�y!';
$l['fp_status_change']    = 'B?nh ch?n #{1} hi?n l� {2} (V? tr� #{3})';
$l['fp_invalid_pid']      = 'M? b?nh ch?n kh�ng h?p l?.';

// -----------------------------------------------------------------------------
// TH�NG B�O & L?I AJAX
// -----------------------------------------------------------------------------
$l['fp_ajax_added']   = '�? th�m PID th�nh c�ng: {1}';
$l['fp_ajax_present'] = 'PID �? t?n t?i trong h? th?ng: {1}';
$l['fp_ajax_invalid'] = 'PID kh�ng h?p l?: {1}';
$l['fp_ajax_error']   = 'L?i';
$l['fp_ajax_unknown'] = 'L?i kh�ng x�c �?nh';

// -----------------------------------------------------------------------------
// X? L? L?I (GIAO DI?N + AJAX)
// -----------------------------------------------------------------------------
$l['fp_error_generic']             = '�? x?y ra l?i.';
$l['fp_error_network']             = 'L?i m?ng.';
$l['fp_error_unable_fetch_results']= 'Kh�ng th? t?i k?t qu?.';
$l['fp_error_invalidpoll']         = 'Kh�ng t?m th?y b?nh ch?n.';
$l['fp_error_pollclosed']          = 'B?nh ch?n n�y �? ��ng.';
$l['fp_error_postkey']             = 'M? POST kh�ng h?p l?.';
$l['fp_error_select_option']       = 'Vui l?ng ch?n m?t t�y ch?n.';
$l['fp_error_nooption']            = 'Vui l?ng ch?n m?t t�y ch?n.';
$l['fp_error_onlyone']             = 'B?n ch? ��?c ch?n m?t t�y ch?n.';
$l['fp_error_maxtoosoon']          = 'B?n ch? ��?c ch?n t?i �a {1} t�y ch?n.';
$l['fp_error_invalidoption']       = 'T�y ch?n kh�ng h?p l?.';
$l['fp_error_unable_undo']         = 'Kh�ng th? h?y b?nh ch?n.';
$l['fp_error_noundo']              = 'B?n kh�ng ��?c ph�p h?y b?nh ch?n.';
$l['fp_error_nopermission']        = 'B?n kh�ng c� quy?n th?c hi?n h�nh �?ng n�y.';
$l['fp_error_invalidpayload']      = 'D? li?u g?i �i kh�ng h?p l?.';
$l['fp_no_permission']             = 'B?n kh�ng c� quy?n th?c hi?n h�nh �?ng n�y.';

// -----------------------------------------------------------------------------
// MOD CP � THANH �I?U H�?NG & TI�U �? TRANG
// -----------------------------------------------------------------------------
$l['fp_modcp_nav_title']     = 'B?nh ch?n n?i b?t';
$l['fp_modcp_page_title']    = 'B?nh ch?n n?i b?t';
$l['fp_breadcrumb_modcp']    = 'B?ng �i?u khi?n Mod';

// -----------------------------------------------------------------------------
// MOD CP � DANH M?C & TI�U �?
// -----------------------------------------------------------------------------
$l['fp_modcp_featured']      = 'B?nh ch?n n?i b?t';
$l['fp_modcp_requested_polls']= 'B?nh ch?n ��?c y�u c?u';
$l['fp_modcp_expired_polls'] = 'B?nh ch?n �? h?t h?n';
$l['fp_modcp_queue']         = 'B?nh ch?n �ang ch?';

// -----------------------------------------------------------------------------
// MOD CP � TR?NG TH�I
// -----------------------------------------------------------------------------
$l['fp_modcp_status_featured'] = 'N?i b?t';
$l['fp_modcp_status_pending']  = '�ang ch?';
$l['fp_modcp_status_expired']  = 'H?t h?n';
$l['fp_modcp_status_queued']   = '�ang ch?';
$l['fp_modcp_slots']		   = '� tr?ng';

// -----------------------------------------------------------------------------
// MOD CP � N�T & H�NH �?NG H�NG LO?T
// -----------------------------------------------------------------------------
$l['fp_modcp_bulk_actions']   = 'H�nh �?ng h�ng lo?t';
$l['fp_modcp_btn_approve']    = 'Chuy?n sang N?i b?t';
$l['fp_modcp_btn_unfeature']  = 'Chuy?n v? �ang ch?';
$l['fp_modcp_btn_queue']      = 'Chuy?n sang H�ng ch?';
$l['fp_modcp_btn_expire']     = 'Chuy?n sang H?t h?n';
$l['fp_modcp_btn_remove']     = 'X�a ho�n to�n';
$l['fp_modcp_btn_update_expiry'] = 'C?p nh?t ng�y h?t h?n';

// -----------------------------------------------------------------------------
// MOD CP � NH?N B?NG & BI?U M?U
// -----------------------------------------------------------------------------
$l['fp_modcp_thread_label']  = 'Ch? �?:';
$l['fp_modcp_pid_label']     = 'PID';
$l['fp_modcp_expires']       = 'H?t h?n';
$l['fp_modcp_added_on']      = 'Th�m v�o l�c:';
$l['fp_modcp_queue_place']   = 'V? tr� trong h�ng:';
$l['fp_no_expiry']           = 'Kh�ng c� th?i h?n';
$l['fp_never']               = 'Kh�ng bao gi?';
$l['fp_modcp_save']          = 'L�u';

// -----------------------------------------------------------------------------
// MOD CP � H�?NG D?N & G?I ?
// -----------------------------------------------------------------------------
$l['fp_modcp_add_by_pid']      = 'Th�m b?nh ch?n theo PID';
$l['fp_modcp_add_placeholder'] = 'v� d?: 12,34,56';
$l['fp_modcp_add']             = 'Th�m';

// -----------------------------------------------------------------------------
// MOD CP � TH�NG B�O H? TH?NG / CHUY?N H�?NG
// -----------------------------------------------------------------------------
$l['fp_modcp_redirect_unfeatured']     = '�? chuy?n c�c b?nh ch?n v? tr?ng th�i �ang ch?.';
$l['fp_modcp_redirect_order_saved']    = 'Th? t? b?nh ch?n �? ��?c c?p nh?t.';
$l['fp_modcp_redirect_queued']         = '�? chuy?n b?nh ch?n v�o h�ng ch?.';
$l['fp_modcp_redirect_expired']        = 'B?nh ch?n �? ��?c ��nh d?u h?t h?n.';
$l['fp_modcp_redirect_expiry_updated'] = 'Ng�y h?t h?n �? ��?c c?p nh?t.';
$l['fp_modcp_removed']                 = 'B?nh ch?n �? b? x�a kh?i danh s�ch n?i b?t.';
$l['fp_redirect_removed']              = '�? x�a.';

// -----------------------------------------------------------------------------
// MOD CP � GIAO DI?N & Y?U T? HI?N TH?
// -----------------------------------------------------------------------------
$l['fp_modcp_drop_here']               = 'K�o b?nh ch?n v�o ��y';
$l['fp_modcp_featured_limit_reached']  = 'B?n ch? c� th? l�m n?i b?t t?i �a {1} b?nh ch?n.';
$l['fp_modcp_expiry_updated']          = '�? c?p nh?t ng�y h?t h?n.';
$l['fp_modcp_featured_none']           = 'Ch�a c� b?nh ch?n n?i b?t n�o.';
$l['fp_modcp_request_none']            = 'Kh�ng c� y�u c?u b?nh ch?n n�o �ang ch?.';
$l['fp_modcp_requested_by']            = '��?c y�u c?u b?i:';
$l['fp_modcp_queue_none']              = 'Kh�ng c� b?nh ch?n n�o trong h�ng ch?.';
$l['fp_modcp_manage_tools']            = 'C�ng c? qu?n l? b?nh ch?n';

// -----------------------------------------------------------------------------
// MOD CP � TH�NG B�O & C?NH B�O
// -----------------------------------------------------------------------------
$l['fp_notice_limit_reached_title'] = '�? �?t gi?i h?n b?nh ch?n n?i b?t';
$l['fp_notice_limit_reached_desc']  = 'B?n �? �?t �?n s? l�?ng t?i �a {1} b?nh ch?n n?i b?t. �? th�m m?i, h?y g? b? ho?c cho h?t h?n c�c b?nh ch?n hi?n t?i.';
