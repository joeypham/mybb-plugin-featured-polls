<?php
/**
 * Featured Polls (MyBB 1.8.x)
 * - Admin/Mods can feature existing thread polls
 * - Global block renders featured polls (inserted on index by default)
 * - AJAX voting with optional redirect; results/vote toggle; undo vote; edit link for owner
 * - Simple Mod CP page: list/add/remove featured PIDs
 */

if(!defined('IN_MYBB')){ die('No direct script access.'); }
require_once MYBB_ROOT . "inc/class_parser.php";

function featuredpolls_info()
{
    return [
        'name'          => 'Featured Polls',
        'description'   => 'Promote thread polls to a global block with AJAX voting, toggle results, undo vote, and a simple Mod CP featured polls manager.',
        'website'       => 'https://mybb.vn',
        'author'        => 'JLP423',
        'authorsite'    => 'https://mybb.vn',
        'version'       => '1.0',
        'compatibility' => '18*',
        'codename'      => 'featuredpolls'
    ];
}

function featuredpolls_is_installed()
{
    global $db;
    return $db->table_exists('featuredpolls');
}

function featuredpolls_install()
{
    global $db;

    $collation = $db->build_create_table_collation();

    if (!$db->table_exists('featuredpolls')) {
        $db->write_query("
            CREATE TABLE `".TABLE_PREFIX."featuredpolls` (
              `pid` INT UNSIGNED NOT NULL,
              `featured` TINYINT(1) NOT NULL DEFAULT 1,
              `dateline` INT UNSIGNED NOT NULL DEFAULT 0,
              `expires` INT UNSIGNED NOT NULL DEFAULT 0,
              `disporder` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`pid`),
              INDEX `fp_featured_dateline` (`featured`, `dateline`)
            ) ENGINE=InnoDB {$collation};
        ");
    }

    $gid = (int)$db->insert_query('settinggroups', [
        'name'        => 'featuredpolls',
        'title'       => 'Featured Polls',
        'description' => 'Settings for Featured Polls plugin.',
        'disporder'   => 50,
        'isdefault'   => 0
    ]);

    $settings = [
        [
            'name'        => 'featuredpolls_enabled',
            'title'       => 'Enable Featured Polls',
            'description' => 'Turn the Featured Polls system on or off globally.',
            'optionscode' => 'yesno',
            'value'       => '1',
            'disporder'   => 1,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_auto_promote',
            'title'       => 'Enable Auto-Promote from Queue',
            'description' => 'If enabled, when a Featured poll expires, the next poll from the queue will automatically be promoted.',
            'optionscode' => 'yesno',
            'value'       => '1',
            'disporder'   => 2,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_promote_position',
            'title'       => 'Auto-Promote Position',
            'description' => 'Choose where auto-promoted polls are inserted in the Featured list.',
            'optionscode' => "select\ntop=Top of Featured\nbottom=Bottom of Featured",
            'value'       => 'top',
            'disporder'   => 3,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_max_display',
            'title'       => 'Max featured polls to display',
            'description' => 'How many featured polls to render in the block.',
            'optionscode' => 'text',
            'value'       => '5',
            'disporder'   => 4,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_default_expiry_days',
            'title'       => 'Default expiry (days) if poll has no timeout',
            'description' => 'When featuring a poll with no timeout, set this many days from now as its expiry.',
            'optionscode' => 'text',
            'value'       => '7',
            'disporder'   => 5,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_redirect_after_vote',
            'title'       => 'Redirect to thread after vote',
            'description' => 'If enabled, after a successful in-block vote, users will be redirected to the poll thread.',
            'optionscode' => 'yesno',
            'value'       => '0',
            'disporder'   => 6,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_show_public_voters',
            'title'       => 'Show voters on public polls',
            'description' => 'If the poll is public, display the list of voters under each option in the featured block.',
            'optionscode' => 'yesno',
            'value'       => '1',
            'disporder'   => 7,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_max_public_voters',
            'title'       => 'Max voters to display per option',
            'description' => 'Limit the number of voter names displayed under each poll option (extra voters will be summarized).',
            'optionscode' => 'text',
            'value'       => '10',
            'disporder'   => 8,
            'gid'         => $gid
        ],
        [
            'name'        => 'featuredpolls_allow_rerequest',
            'title'       => 'Allow re-request of expired polls',
            'description' => 'If enabled, users can re-submit a request for their poll after it has expired. If disabled, expired polls remain locked unless handled by a moderator/admin.',
            'optionscode' => 'yesno',
            'value'       => '0',
            'disporder'   => 9,
            'gid'         => $gid
        ],
		[
			'name'        => 'featuredpolls_request_groups',
			'title'       => 'Groups Allowed to Request Feature',
			'description' => 'Select which usergroups can request their poll to be featured. This setting is checked in addition to the forum’s default poll permissions (users must already be able to create a poll in the forum).',
			'optionscode' => 'groupselect',
			'value'       => '2,3,4,6',
			'disporder'   => 10,
			'gid'         => $gid
		],
		[
			'name'        => 'featuredpolls_view_groups',
			'title'       => 'Groups Allowed to See Featured Block',
			'description' => 'Select which usergroups can view the Featured Polls block on the index/portal. This setting is checked in addition to the forum’s default view permissions (users must already have access to the forum/thread).',
			'optionscode' => 'groupselect',
			'value'       => '2,3,4,6',
			'disporder'   => 11,
			'gid'         => $gid
		],
		[
			'name'        => 'featuredpolls_timeout_behavior',
			'title'       => 'Timed-out Poll Behavior',
			'description' => 'Choose how to handle polls that have reached their MyBB timeout date.',
			'optionscode' => "select\nkeep=Keep visible until expiry\nautoexpire=Auto-expire when poll times out",
			'value'       => 'keep',
			'disporder'   => 12,
			'gid'         => $gid
		],
		[
			'name'        => 'featuredpolls_manage_scope',
			'title'       => 'Who Can Manage Featured Polls',
			'description' => 'Choose whether only administrators (ACP) or administrators + super moderators (cancp) can manage Featured Polls.',
			'optionscode' => "select\nadmin=Admins only\ncancp=Admins + Super Moderators",
			'value'       => 'cancp',
			'disporder'   => 13,
			'gid'         => $gid
		],
	];

    foreach ($settings as $s) {
        $db->insert_query('settings', $s);
    }

    rebuild_settings();

	require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';

$stylesheet = <<<CSS
.featuredpolls { box-shadow:0 2px 6px rgba(0,0,0,0.08); border-radius:8px; overflow:hidden; margin-bottom:16px; margin-bottom:16px;}
.featuredpolls .thead { background:linear-gradient(90deg,#2563eb,#1d4ed8); color:#fff; padding:10px 14px; font-size:1em; }
.featuredpolls .trow1 { background:#f9fafb; padding:12px; }

.poll-card { border-radius:12px; border:1px solid #e5e7eb; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.06); transition:all 0.2s; }
.poll-card .thead { padding:16px 20px; background:linear-gradient(90deg,#f5f8fc,#ebf0f8); border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center; }

.fp-box { max-height: 300px; overflow-y: auto; padding-right: 6px;}
.fp-box::-webkit-scrollbar { width: 8px;}
.fp-box::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px;}
.fp-box::-webkit-scrollbar-thumb:hover { background: #999;}

.fp-btn { display:inline-block; padding:6px 14px; border-radius:6px; font-size:0.9em; font-weight:500; text-decoration:none; background:#2563eb; color:#fff !important; transition:all 0.2s; }
.fp-btn:hover { background:#1d4ed8; }
.fp-btn.secondary { background:#e5e7eb; color:#111 !important; }
.fp-btn.secondary:hover { background:#d1d5db; }
.fp-btn.warning { background:#f97316; color:#fff !important; }
.fp-btn.warning:hover { background:#ea580c; }

.fp-status { font-weight:700; font-size:0.9em; margin-left:6px; }
.fp-status-featured { color:#080; }
.fp-status-pending  { color:#b80; }
.fp-status-expired  { color:#888; }
.fp-status-queued   { color:#06c; }
.fp-status-none     { color:#aaa; }

.fp-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 500;
    margin-left: 8px;
    vertical-align: middle;
    border: 1px solid transparent;
}

.fp-status-badge.fp-status-featured {
    background-color: #ecfdf5; /* Light Green */
    color: #065f46;
    border-color: #a7f3d0;
}
.fp-status-badge.fp-status-pending {
    background-color: #fefce8; /* Light Yellow */
    color: #854d0e;
    border-color: #fde68a;
}
.fp-status-badge.fp-status-expired {
    background-color: #f3f4f6; /* Light Gray */
    color: #4b5563;
    border-color: #d1d5db;
}
.fp-status-badge.fp-status-queued {
    background-color: #eff6ff; /* Light Blue */
    color: #1e40af;
    border-color: #bfdbfe;
}

.poll-option { margin:10px 0; }
.poll-option label { display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid #e0e0e0; border-radius:8px; background:#fafafa; cursor:pointer; transition:all 0.2s; }
.poll-option label:hover { background:#f1f5f9; }

.poll-result { display:flex; align-items:center; gap:12px; margin:6px 0; font-size:0.95em; }
.poll-result div { color:#333; }
.poll-result .bar { background:#f1f5f9; border-radius:7px; overflow:hidden; height:14px; }
.poll-result .bar-inner { height:100%; background:linear-gradient(90deg,#3b82f6,#1d4ed8); }

.ui-state-highlight { height: 48px; background: #e0f2fe; border: 2px dashed #38bdf8; border-radius: 6px; margin: 6px 0; }
.ui-sortable-helper { opacity: 0.9; transform: scale(1.02); background: #fff; box-shadow: 0 6px 14px rgba(0,0,0,0.15); border-radius: 6px; }

.fp-handle { cursor: grab; font-size: 18px; color: #666; padding: 0 6px; user-select: none; }
.fp-handle:hover { color: #111; }

.fp-empty { padding: 20px; text-align: center; color: #aaa; border: 2px dashed #ccc; border-radius: 6px; background: #fafafa; margin: 6px 0; font-size: 0.9em; min-height: 60px; line-height: 60px; }
.fp-empty.ui-state-highlight { background: #e0f2fe; border-color: #38bdf8; color: #0369a1; }

.fp-sortable { min-height: 60px; list-style:none; margin:0; padding:0; }

.fp-feedback { margin-bottom:10px; padding:8px 12px; border-radius:6px; text-align:center; font-weight:500; display:none; }

.smalltext-extra { margin-top:4px; }

.jGrowl {
  z-index: 99999; 
  font-family: inherit;
  max-width: 340px;
}

.jGrowl-notification {
  border-radius: 10px !important;
  padding: 12px 16px !important;
  font-size: 0.9em !important;
  font-weight: 500 !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.12);
  margin-bottom: 10px !important;
}

.jGrowl-notification.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #28a745;
}

.jGrowl-notification.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #dc2626;
}

.jGrowl-notification.warning {
  background: #fef9c3;
  color: #92400e;
  border: 1px solid #f59e0b;
}

.jGrowl-close {
  color: inherit !important;
  font-weight: bold;
  margin-left: 8px;
  cursor: pointer;
  opacity: 0.6;
}
.jGrowl-close:hover {
  opacity: 1;
}

CSS;


	$query = $db->simple_select('themes', 'tid');
	while ($theme = $db->fetch_array($query)) {
		$record = [
			'name'        => 'featuredpolls.css',
			'tid'         => (int)$theme['tid'],
			'stylesheet'  => $db->escape_string($stylesheet),
			'cachefile'   => 'featuredpolls.css',
			'lastmodified'=> TIME_NOW
		];
		$db->insert_query('themestylesheets', $record);
		cache_stylesheet($record['tid'], $record['cachefile'], $stylesheet);
		update_theme_stylesheet_list($record['tid'], false, true);
	}

    featuredpolls_install_templates();
}

function featuredpolls_uninstall()
{
    global $db;
	require_once MYBB_ADMIN_DIR.'/inc/functions_themes.php';
    if ($db->table_exists('featuredpolls')) {
        $db->drop_table('featuredpolls');
    }

    $db->delete_query('settings', "name LIKE 'featuredpolls_%'");
    $db->delete_query('settinggroups', "name='featuredpolls'");
    rebuild_settings();

	$query = $db->simple_select('themestylesheets', 'sid,tid', "name='featuredpolls.css'");
	while ($row = $db->fetch_array($query)) {
		$db->delete_query('themestylesheets', "sid='{$row['sid']}'");
		update_theme_stylesheet_list($row['tid'], false, true);
	}

    featuredpolls_uninstall_templates();
}


function featuredpolls_activate()
{
    require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

    find_replace_templatesets(
        'index',
        '#'.preg_quote('{$forums}').'#i',
        '{$featured_polls}'."\n".'{$forums}'
    );
	find_replace_templatesets(
		'polls_newpoll',
		'#<td class="trow1"><span class="smalltext">#i',
		'<td class="trow1"><span class="smalltext">{$featured_checkbox}'
	);
	find_replace_templatesets(
		'polls_editpoll',
		'#<td class="trow1"><span class="smalltext">#i',
		'<td class="trow1"><span class="smalltext">{$featured_checkbox}'
	);
    find_replace_templatesets(
        'modcp_nav',
        '#'.preg_quote('{$modcp_nav_forums_posts}').'#i',
        '{$modcp_nav_forums_posts}<!--FEATUREDPOLLS_NAV-->'
    );
    find_replace_templatesets(
        'headerinclude',
        '#'.preg_quote('{$stylesheets}').'#i',
        '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/featuredpolls/featuredpolls_front.js?ver=1.0"></script>'."\n".'{$stylesheets}'
    );
    find_replace_templatesets(
        'showthread_poll',
        '#' . preg_quote('{$poll[\'question\']}</strong>') . '#i',
        '{$poll[\'question\']}</strong><span class="smalltext">{$poll_featured_status}</span>'
    );
    find_replace_templatesets(
        'showthread_poll_results',
        '#' . preg_quote('{$poll[\'question\']}</strong>') . '#i',
        '{$poll[\'question\']}</strong><span class="smalltext">{$poll_featured_status}</span>'
    );
    find_replace_templatesets(
        'polls_showresults',
        '#' . preg_quote('<strong>{$lang->poll} {$poll[\'question\']}</strong>') . '#i',
        '{$poll[\'question\']}</strong><span class="smalltext">{$poll_featured_status}</span>'
    );

}

function featuredpolls_deactivate()
{
    require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
    find_replace_templatesets('index', "#".preg_quote('{$featured_polls}'."\n")."#", '', 0);
    find_replace_templatesets('modcp_nav', '#'.preg_quote('<!--FEATUREDPOLLS_NAV-->').'#', '', 0);
	find_replace_templatesets('polls_newpoll', '#'.preg_quote('{$featured_checkbox}').'#i', '', 0);
	find_replace_templatesets('polls_editpoll', '#'.preg_quote('{$featured_checkbox}').'#i', '', 0);
    find_replace_templatesets('headerinclude', "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/featuredpolls/featuredpolls_front.js?ver=1.0"></script>'."\n")."#", '', 0);
	find_replace_templatesets('showthread_poll','#' . preg_quote('<span class="smalltext">{$poll_featured_status}</span>') . '#i',	'', 0);
	find_replace_templatesets('showthread_poll_results','#' . preg_quote('<span class="smalltext">{$poll_featured_status}</span>') . '#i',	'', 0);
	find_replace_templatesets('poll_results','#' . preg_quote('<span class="smalltext">{$poll_featured_status}</span>') . '#i',	'', 0);
}

function featuredpolls_install_templates()
{
    global $db;

    $gid = (int)$db->fetch_field(
        $db->simple_select('templategroups', 'gid', "prefix='featuredpolls'"),
        'gid'
    );
    if($gid){
        $db->update_query('templategroups', [
            'title' => $db->escape_string('Featured Polls')
        ], "gid={$gid}");
    } else {
        $db->insert_query('templategroups', [
            'prefix' => $db->escape_string('featuredpolls'),
            'title'  => $db->escape_string('Featured Polls')
        ]);
    }

    $t = [];

	$t['featuredpolls_container'] = <<<'JLP423'
<div class="featuredpolls tborder">
  <div class="thead" style="padding:10px 14px; font-size:1em;">
    <strong>{$lang->fp_block_title}</strong>
  </div>
  <div class="trow1" style="padding:12px; background:#fff;">
    {$items}
  </div>
</div>
JLP423;

	$t['featuredpolls_item'] = <<<'JLP423'
<div id="fp_{$poll['pid']}" class="fp-form-container" style="margin:0 auto 20px;" 
    data-error-unable-fetch-results="{$lang->fp_error_unable_fetch_results}"
    data-error-network="{$lang->fp_error_network}"
    data-error-unable-undo="{$lang->fp_error_unable_undo}"
    data-error-generic="{$lang->fp_error_generic}"
>
    <form class="fp-form" action="xmlhttp.php?action=featuredpolls_vote" method="post" data-pid="{$poll['pid']}">
        <input type="hidden" name="my_post_key" value="{$mybb->post_code}">
        <input type="hidden" name="pid" value="{$poll['pid']}">

        <div class="poll-card" style="border-radius:12px; overflow:hidden; border:1px solid #e5e7eb; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.06); transition:all 0.2s;">
            <div class="thead" style="padding:16px 20px; background:linear-gradient(90deg,#f5f8fc,#ebf0f8); border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center;">
                <strong style="font-size:1.1em; color:#1c1c1c;">{$poll['question_html']}</strong>
                <span class="smalltext">
                    <a href="{$thread_url}" style="color:#2563eb; text-decoration:none;">{$lang->fp_view_thread}</a>
                </span>
            </div>

            <div class="fp-options" style="padding:20px 20px 10px 20px;">
                <div class="fp-feedback smalltext" style="margin-bottom:10px; padding:8px 12px; border-radius:6px; display:none; text-align:center; font-weight:500;"></div>
                <div class="fp-options-inner">
                    {$options_or_results_html}
                </div>
            </div>

            <div class="fp-actions" style="padding:14px 20px; border-top:1px solid #eee; background:#fafafa; display:flex; flex-wrap:wrap; gap:10px; justify-content:center;">
                {$actions_html}
            </div>
        </div>

        <div class="fp-options-template" style="display:none;">
            {$options_template_html}
        </div>
    </form>
</div>
JLP423;

	$t['featuredpolls_actions_vote'] = <<<'JLP423'
<button type="button" class="fp-btn fp-vote">{$lang->fp_vote}</button>
<button type="button" class="fp-btn secondary fp-view-results">{$lang->fp_results}</button>
JLP423;

	$t['featuredpolls_actions_results_can_undo'] = <<<'JLP423'
<button type="button" class="fp-btn warning fp-undo-vote">{$lang->fp_undo}</button>
{$edit_btn}
JLP423;

	$t['featuredpolls_actions_results_readonly'] = <<<'JLP423'
{$edit_btn}
JLP423;

	$t['featuredpolls_actions_back_to_voting'] = <<<'JLP423'
<a href="#" class="fp-btn secondary fp-view-options" data-default-actions="{$default_actions_escaped}">
    {$lang->fp_back_to_voting}
</a>
JLP423;

	$t['featuredpolls_option'] = <<<'JLP423'
<div class="poll-option" style="margin:10px 0;">
    <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid #e0e0e0; border-radius:8px; background:#fafafa; cursor:pointer; transition:all 0.2s;">
        <input type="{$input_type}" name="options[]" value="{$index}" style="margin:0;" />
        <span style="flex:1;">{$text}</span>
    </label>
</div>
JLP423;

	$t['featuredpolls_result'] = <<<'JLP423'
<div class="poll-result" style="display:flex; align-items:center; gap:12px; margin:6px 0; font-size:0.95em;">
    <div style="flex:1; min-width:120px; text-align:right;">{$text}</div>
    <div style="flex:3;">
        <div style="background:#f1f5f9; border-radius:7px; overflow:hidden; height:14px;">
            <div style="width:{$percent}%; background:linear-gradient(90deg,#3b82f6,#1d4ed8); height:100%;"></div>
        </div>
        {$voter_names}
    </div>
    <div style="flex:0 0 auto; min-width:60px; text-align:right; color:#333;">
        {$count}
    </div>
    <div style="flex:0 0 auto; min-width:50px; text-align:right; color:#333;">
        {$percent}%
    </div>
</div>
JLP423;

	$t['featuredpolls_result_total'] = <<<'JLP423'
<div class="poll-result" style="display:flex; align-items:center; gap:12px; margin-top:10px; font-weight:bold; font-size:0.95em;">
    <div style="flex:3;"></div>
    <div style="flex:1; min-width:120px; text-align:right;">{$lang->fp_total}</div>
    <div style="flex:0 0 auto; min-width:60px; text-align:right; color:#333;">
        {$total} {$lang->fp_votes}
    </div>
    <div style="flex:0 0 auto; min-width:50px; text-align:right; color:#333;">
        {$total_percent}%
    </div>
</div>
JLP423;

	$t['featuredpolls_poll_feature_checkbox'] = <<<'JLP423'
<label>
  <input type="checkbox" class="checkbox" {$featuredpolls_attrs} />
  <strong>{$lang->fp_request}</strong>
  <span class="fp-status {$featuredpolls_status_class}">{$featuredpolls_status_html}</span>
</label><br />
JLP423;

	$t['featuredpolls_modcp_nav'] = <<<'JLP423'
<tr>
    <td class="trow1 smalltext">
        <a href="modcp.php?action=featuredpolls" class="modcp_nav_item modcp_nav_modqueue">
            {$lang->fp_modcp_nav_title}
        </a>
    </td>
</tr>
JLP423;

$t['featuredpolls_modcp'] = <<<'JLP423'
<html>
<head>
    <title>{$mybb->settings['bbname']} - {$lang->fp_modcp_page_title}</title>
    {$headerinclude}
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="//code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <script type="text/javascript">
        const FEATURED_LIMIT = parseInt("{$mybb->settings['featuredpolls_max_display']}", 10) || 5;
        const postKey = "{$mybb->post_code}";
        const fpLang = {
            dropHere: "{$lang->fp_modcp_drop_here}",
            removed: "{$lang->fp_modcp_removed}",
            ajaxError: "{$lang->fp_ajax_error}",
            ajaxUnknown: "{$lang->fp_ajax_unknown}",
            errorNetwork: "{$lang->fp_error_network}",
            errorInvalidPoll: "{$lang->fp_error_invalidpoll}",
            ajaxAdded: "{$lang->fp_ajax_added}",
            ajaxPresent: "{$lang->fp_ajax_present}",
            ajaxInvalid: "{$lang->fp_ajax_invalid}",
            limitReached: "{$lang->fp_modcp_featured_limit_reached}",
            statusFeatured: "{$lang->fp_modcp_status_featured}",
            statusPending: "{$lang->fp_modcp_status_pending}",
            statusExpired: "{$lang->fp_modcp_status_expired}",
            statusQueued: "{$lang->fp_modcp_status_queued}"
        };
    </script>
    <script type="text/javascript" src="{$mybb->settings['bburl']}/jscripts/featuredpolls/featuredpolls_modcp.js?ver=1.0"></script>
</head>
<body>
    {$header}
    <form action="modcp.php?action=featuredpolls" method="post">
        <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
        <table width="100%" border="0" align="center">
            <tr>
                {$modcp_nav}
                <td valign="top">
                    <table width="100%" border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" >
                        <tr>
                            <!-- Featured -->
                            <td valign="top" width="50%">
                                <div class="tborder">
                                    <div class="thead"><strong>{$lang->fp_modcp_featured} {$featured_count_html}</strong></div>
									<div class="trow1">
										<div class="fp-box">
											<ul id="fp-featured" class="fp-sortable" style="list-style:none;margin:0;padding:0;">
												{$featured}
											</ul>
										</div>
									</div>
                                </div>
                            </td>

                            <!-- Expired -->
                            <td valign="top" width="50%">
                                <div class="tborder">
                                    <div class="thead"><strong>{$lang->fp_modcp_expired_polls}</strong></div>
                                    <div class="trow1">
										<div class="fp-box">
											<ul id="fp-expired" class="fp-sortable" style="list-style:none;margin:0;padding:0;">
												{$expired}
											</ul>
										</div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <!-- Queue + Add by PID -->
                            <td valign="top" width="50%">
                                <div class="tborder" style="margin-bottom:15px;">
                                    <div class="thead"><strong>{$lang->fp_modcp_queue}</strong></div>
                                    <div class="trow1">
										<div class="fp-box">
											<ul id="fp-queue" class="fp-sortable" style="list-style:none;margin:0;padding:0;">
												{$queue}
											</ul>
										</div>
                                    </div>
                                </div>
                                <!-- Add PID box directly below Queue -->
                                {$add_by_pid}
                            </td>

                            <!-- Requested + Bulk Actions -->
                            <td valign="top" width="50%">
                                <div class="tborder" style="margin-bottom:15px;">
                                    <div class="thead"><strong>{$lang->fp_modcp_requested_polls}</strong></div>
                                    <div class="trow1">
										<div class="fp-box">
											<ul id="fp-pending" class="fp-sortable" style="list-style:none;margin:0;padding:0;">
												{$requests}
											</ul>
										</div>
                                    </div>
                                </div>

                                <!-- Bulk Actions box -->
                                <div class="tborder">
                                    <div class="thead"><strong>{$lang->fp_modcp_bulk_actions}</strong></div>
                                    <div class="trow1" style="padding:10px;">
                                        <input type="submit" class="button" name="approve" value="{$lang->fp_modcp_btn_approve}" />
                                        <input type="submit" class="button" name="queue" value="{$lang->fp_modcp_btn_queue}" />
                                        <input type="submit" class="button" name="unfeature" value="{$lang->fp_modcp_btn_unfeature}" />
                                        <input type="submit" class="button" name="expire" value="{$lang->fp_modcp_btn_expire}" />
                                        <input type="submit" class="button" name="remove" value="{$lang->fp_modcp_btn_remove}" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
    {$footer}
</body>
</html>
JLP423;

	$t['featuredpolls_modcp_item'] = <<<'JLP423'
<li class="trow2 fp-item" id="fp_{$r['pid']}" style="margin-bottom:8px;">
    <div style="display:flex; align-items:flex-start; gap:10px;">
        <input type="checkbox" class="fp-checkbox" name="selected[]" value="{$r['pid']}" />

        <div style="flex:1;">
            <div>
                <strong>{$poll_question} ({$lang->fp_modcp_pid_label} {$r['pid']})</strong> -
                <span class="fp-status {$status_class}">{$status_label}</span>
            </div>
            <div class="smalltext">
                {$lang->fp_modcp_thread_label}
                <a href="showthread.php?tid={$r['tid']}">{$thread_subject}</a>
            </div>
            <div class="smalltext">
                {$lang->fp_modcp_added_on} {$date}
            </div>
            <div class="smalltext-extra">
                {$extra_html}
            </div>
        </div>

        <div class="fp-handle" title="Drag to reorder">&#9776;</div>
    </div>
</li>
JLP423;

	$t['featuredpolls_modcp_add_pid'] = <<<'JLP423'
<div class="tborder" style="margin-top:15px;">
    <div class="thead"><strong>{$lang->fp_modcp_add_by_pid}</strong></div>
    <div class="trow1" style="padding:10px;">
        <form id="fp_add_form" action="javascript:void(0);">
            <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
            <input type="text" id="fp_add_pids" name="fp_add_pids" size="50" placeholder="{$lang->fp_modcp_add_placeholder}" />
            <button type="button" class="button" id="fp_add_btn">{$lang->fp_modcp_add}</button>
        </form>
        <div id="fp_add_msg" style="margin-top:5px; display:none;"></div>
    </div>
</div>
JLP423;

    foreach ($t as $title => $template_raw) {
        $exists = $db->fetch_field(
            $db->simple_select('templates', 'tid', "title='".$db->escape_string($title)."' AND sid='-'"),
            'tid'
        );

        $row = [
            'title'    => $db->escape_string($title),
            'template' => $db->escape_string($template_raw),
            'sid'      => -1,
            'version'  => '1800',
            'dateline' => TIME_NOW
        ];

        if ($exists) {
            $db->update_query('templates', $row, "tid=".(int)$exists);
        } else {
            $db->insert_query('templates', $row);
        }
    }
}

function featuredpolls_uninstall_templates()
{
    global $db;
    $db->delete_query('templates', "title LIKE 'featuredpolls_%'");
    $db->delete_query('templategroups', "prefix='featuredpolls'");
}


$plugins->add_hook('global_start', 'featuredpolls_load_core_lang');
$plugins->add_hook('global_start', 'featuredpolls_global_start');
$plugins->add_hook('xmlhttp', 'featuredpolls_xmlhttp');
$plugins->add_hook('modcp_start', 'featuredpolls_modcp');
$plugins->add_hook('polls_newpoll_start', 'featuredpolls_make_checkbox');
$plugins->add_hook('polls_editpoll_start', 'featuredpolls_make_checkbox');
$plugins->add_hook('polls_do_newpoll_end', 'featuredpolls_handle_request');
$plugins->add_hook('polls_do_editpoll_end', 'featuredpolls_handle_request');
$plugins->add_hook('showthread_start', 'featuredpolls_poll_status');
$plugins->add_hook('polls_showresults_start', 'featuredpolls_poll_status');


function featuredpolls_trim_and_reorder()
{
    global $db, $mybb;

    $max = (int)$mybb->settings['featuredpolls_max_display'];
    if ($max <= 0) {
        return;
    }

    $query = $db->query("
        SELECT pid
        FROM ".TABLE_PREFIX."featuredpolls
        WHERE featured=1
        ORDER BY dateline ASC
    ");
    $featured = [];
    while ($row = $db->fetch_array($query)) {
        $featured[] = (int)$row['pid'];
    }

    if (count($featured) > $max) {
        $overflow = array_slice($featured, $max);
        $db->update_query('featuredpolls', [
            'featured' => 3,
            'dateline' => TIME_NOW
        ], "pid IN (".implode(',', $overflow).")");

        $featured = array_slice($featured, 0, $max);
    }

    // Reorder disporder cleanly 1..N
    $i = 0;
    foreach ($featured as $pid) {
        $i++;
        $db->update_query('featuredpolls', ['disporder' => $i], "pid={$pid}");
    }
}

function featuredpolls_cleanup_expired()
{
    global $db, $mybb;

    // 1. Mark expired polls
    $expired = $db->simple_select('featuredpolls', 'pid', "featured=1 AND expires > 0 AND expires < ".TIME_NOW);
    $expired_pids = [];
    while ($pid = (int)$db->fetch_field($expired, 'pid')) {
        $expired_pids[] = $pid;
    }

    if ($expired_pids) {
        $in = implode(',', $expired_pids);
        $db->update_query('featuredpolls', ['featured' => 2], "pid IN ({$in})");
    }

    // 2. Handle auto-promote
    if (empty($mybb->settings['featuredpolls_auto_promote']) || !$expired_pids) {
        return;
    }

    foreach ($expired_pids as $old_pid) {
        $row = $db->fetch_array($db->query("
            SELECT pid
            FROM ".TABLE_PREFIX."featuredpolls
            WHERE featured=3
            ORDER BY disporder ASC, dateline ASC
            LIMIT 1
        "));
        if (!$row || !$row['pid']) {
            continue;
        }

        $new_pid = (int)$row['pid'];
        featuredpolls_set_featured($new_pid);
    }
}

function featuredpolls_get_status_properties($status)
{
    global $lang;
    
    $statuses = [
        1 => ['label' => $lang->fp_modcp_status_featured, 'class' => "fp-status-featured"],
        2 => ['label' => $lang->fp_modcp_status_expired,  'class' => "fp-status-expired"],
        3 => ['label' => $lang->fp_modcp_status_queued,   'class' => "fp-status-queued"],
        0 => ['label' => $lang->fp_modcp_status_pending,  'class' => "fp-status-pending"],
    ];

    return $statuses[(int)$status] ?? ['label' => 'Unknown', 'class' => ''];
}

function featuredpolls_get_poll_data($pid)
{
    global $db;

    $pid = (int)$pid;
    if ($pid <= 0) {
        return null;
    }

    return $db->fetch_array(
        $db->simple_select('featuredpolls', 'featured, expires', "pid={$pid}", ['limit' => 1])
    );
}

function featuredpolls_make_checkbox()
{
    global $mybb, $lang, $templates, $featured_checkbox;

    if (THIS_SCRIPT !== 'polls.php') {
        return;
    }
    $lang->load('featuredpolls');

    $allowed = array_map('intval', explode(',', (string)$mybb->settings['featuredpolls_request_groups']));
    if (!in_array((int)$mybb->user['usergroup'], $allowed)) {
        return;
    }

    $pid = (int)$mybb->get_input('pid', MyBB::INPUT_INT);
    $poll_data = featuredpolls_get_poll_data($pid);

    if (!$poll_data) {
        $featuredpolls_status_html = $lang->fp_not_submitted;
        $featuredpolls_status_class = 'fp-status-none';
        $featuredpolls_attrs = 'name="featuredpolls_request" value="1"';
    } else {
        $status = (int)$poll_data['featured'];
        $properties = featuredpolls_get_status_properties($status);
        
        $featuredpolls_status_html = $properties['label'];
        $featuredpolls_status_class = $properties['class'];
        
        // This logic is now correctly located in the function that builds the checkbox
        switch ($status) {
            case 1: // Featured
                $featuredpolls_attrs = 'checked="checked" disabled="disabled"';
                break;
            case 2: // Expired
                $featuredpolls_attrs = !empty($mybb->settings['featuredpolls_allow_rerequest'])
                    ? 'name="featuredpolls_request" value="1"'
                    : 'checked="checked" disabled="disabled"';
                break;
            case 0: // Pending & Queued
            case 3:
                $featuredpolls_attrs = 'name="featuredpolls_request" value="1" checked="checked"';
                break;
        }
    }

    eval('$featured_checkbox = "'.$templates->get('featuredpolls_poll_feature_checkbox').'";');
}

function featuredpolls_poll_status()
{
    global $mybb, $db, $thread, $poll_featured_status;

    if (!$mybb->user['uid'] || !$thread['poll']) {
        return;
    }

    $is_moderator = is_moderator($thread['fid']);
    if ($mybb->user['uid'] == $thread['uid'] || $is_moderator) {
        
        $pid = (int)$db->fetch_field(
            $db->simple_select('polls', 'pid', "tid = '{$thread['tid']}'", ['limit' => 1]),
            'pid'
        );

        if ($pid > 0) {
            $poll_data = featuredpolls_get_poll_data($pid);

            if ($poll_data) {
                $properties = featuredpolls_get_status_properties($poll_data['featured']);
                $poll_featured_status = "<span class=\"fp-status-badge {$properties['class']}\">{$properties['label']}</span>";
            }
        }
    }
}

function featuredpolls_handle_request()
{
    global $db, $mybb, $lang;
    $lang->load('featuredpolls');

    $pid = (int)$mybb->get_input('pid', MyBB::INPUT_INT);
    if (!$pid && isset($GLOBALS['pid'])) {
        $pid = (int)$GLOBALS['pid'];
    }
    if ($pid <= 0) {
        return;
    }

    if (!isset($mybb->input['featuredpolls_request'])) {
        return;
    }

    $request_flag = (int)$mybb->get_input('featuredpolls_request', MyBB::INPUT_INT);

    if ($request_flag === 1) {
        $row = $db->fetch_array(
            $db->simple_select('featuredpolls', 'pid,featured,expires', "pid={$pid}", ['limit' => 1])
        );

        if ($row) {
            if ((int)$row['expires'] > 0 && (int)$row['expires'] < TIME_NOW) {
                if (!empty($mybb->settings['featuredpolls_allow_rerequest'])) {
                    $db->update_query('featuredpolls', [
                        'featured' => 0,
                        'dateline' => TIME_NOW,
                        'expires'  => 0
                    ], "pid={$pid}");
                    return $lang->fp_pending_review;
                }
                return $lang->fp_expired;
            }

            $db->update_query('featuredpolls', [
                'dateline' => TIME_NOW
            ], "pid={$pid}");

            $status_map = [
                1 => $lang->fp_featured,
                2 => $lang->fp_expired,
                3 => $lang->fp_queue,
                0 => $lang->fp_pending_review
            ];

            return $status_map[(int)$row['featured']] ?? $lang->fp_pending_review;
        } else {
			$max_order = (int)$db->fetch_field(
				$db->simple_select('featuredpolls', 'MAX(disporder) as maxdisp', "featured=0"),
				'maxdisp'
			);
			$disporder = $max_order + 1;

			$db->insert_query('featuredpolls', [
				'pid'       => $pid,
				'featured'  => 0,
				'dateline'  => TIME_NOW,
				'expires'   => 0,
				'disporder' => $disporder
			]);
            return $lang->fp_pending_review;
        }
    } else {
        $row = $db->fetch_array(
            $db->simple_select('featuredpolls', 'featured', "pid={$pid}", ['limit' => 1])
        );

        if ($row && in_array((int)$row['featured'], [0,3], true)) {
            $db->delete_query('featuredpolls', "pid={$pid}");
            return $lang->fp_not_submitted;
        }

        if ($row) {
            $status_map = [
                1 => $lang->fp_featured,
                2 => $lang->fp_expired
            ];
            return $status_map[(int)$row['featured']] ?? $lang->fp_not_submitted;
        }

        return $lang->fp_not_submitted;
    }
}

function featuredpolls_load_core_lang()
{
    global $lang;
    if (empty($lang->featuredpolls_lang_loaded)) {
        $lang->load('featuredpolls');
        $lang->featuredpolls_lang_loaded = 1;
    }
}

function featuredpolls_global_start()
{
    global $mybb, $templates, $featured_polls, $headerinclude;

    $limit = (int)$mybb->settings['featuredpolls_max_display'];
    $featured_polls = featuredpolls_render_block(max(1, $limit));
}

function featuredpolls_render_block($limit)
{
    global $db, $mybb, $templates, $lang;
	
    if (empty($mybb->settings['featuredpolls_enabled'])) {
        return '';
    }
	
    $allowed = array_map('intval', explode(',', (string)$mybb->settings['featuredpolls_view_groups']));
    if (!in_array((int)$mybb->user['usergroup'], $allowed)) {
        return '';
    }
    featuredpolls_cleanup_expired();

    $items = '';
    $q = $db->query("
        SELECT f.*, p.*, t.tid, t.fid, t.subject, t.uid AS thread_uid
        FROM ".TABLE_PREFIX."featuredpolls f
        INNER JOIN ".TABLE_PREFIX."polls p ON(p.pid=f.pid)
        LEFT JOIN ".TABLE_PREFIX."threads t ON(t.tid=p.tid)
        WHERE f.featured=1
          AND (f.expires=0 OR f.expires > UNIX_TIMESTAMP())
        ORDER BY f.disporder ASC, f.dateline DESC
        LIMIT ".(int)$limit
    );
	while ($poll = $db->fetch_array($q)) {
		$fid = (int)$poll['fid'];
		$forumpermissions = forum_permissions($fid);

		if (!$forumpermissions['canview'] || !$forumpermissions['canviewthreads']) {
			continue;
		}

		$items .= featuredpolls_render_item($poll);
	}

    if ($items === '') {
        return '';
    }

    eval("\$out = \"".$templates->get('featuredpolls_container')."\";");
    return $out;
}

function featuredpolls_render_item($poll)
{
    global $mybb, $templates, $lang;

    $thread_url = 'showthread.php?tid='.(int)$poll['tid'].'#pid'.(int)$poll['pid'];
    $poll['question_html'] = htmlspecialchars_uni($poll['question']);

    $can_vote = featuredpolls_can_vote($poll);
    $has_vote_perm = (
        !empty($mybb->usergroup['canvotepolls']) ||
        !empty($mybb->usergroup['cancp']) ||
        is_moderator($poll['tid'])
    );
    $user_voted = featuredpolls_user_has_voted($poll['pid'], (int)$mybb->user['uid']);

    $can_undo = (
        !empty($mybb->usergroup['canundovotes']) ||
        !empty($mybb->usergroup['cancp']) ||
        is_moderator($poll['tid'])
    );

    $can_edit_poll = false;
    if ($mybb->usergroup['cancp'] || is_moderator($poll['tid'])) {
        $can_edit_poll = true;
    } elseif ((int)$mybb->user['uid'] === (int)$poll['thread_uid'] && !empty($mybb->usergroup['caneditpolls'])) {
        $can_edit_poll = true;
    }

    $edit_btn = $can_edit_poll
        ? '<a href="polls.php?action=editpoll&pid='.(int)$poll['pid'].'" class="fp-btn secondary">'.htmlspecialchars_uni($lang->fp_edit).'</a>'
        : '';

    $options_template_html = featuredpolls_build_options_html($poll);

    if (!$has_vote_perm) {
        $options_or_results_html = featuredpolls_build_results_html($poll);
        eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_readonly')."\";");
        $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
    } elseif ($can_vote && !$user_voted) {
        $options_or_results_html = $options_template_html;
        eval("\$actions_html = \"".$templates->get('featuredpolls_actions_vote')."\";");
    } else {
        $options_or_results_html = featuredpolls_build_results_html($poll);
        if ($user_voted && $can_undo) {
            eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_can_undo')."\";");
            $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
        } else {
            eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_readonly')."\";");
            $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
        }
    }

    eval("\$default_actions_for_back = \"".$templates->get('featuredpolls_actions_vote')."\";");
    $default_actions_escaped = htmlspecialchars_uni($default_actions_for_back);

    eval("\$item = \"".$templates->get('featuredpolls_item')."\";");

    $item = str_replace(
        ['{$options_template_html}', '{$options_or_results_html}', '{$actions_html}', '{$default_actions_escaped}', '{$thread_url}', '{$view_thread_text}'],
        [
            $options_template_html,
            $options_or_results_html,
            $actions_html,
            $default_actions_escaped,
            htmlspecialchars_uni($thread_url),
            htmlspecialchars_uni($lang->fp_view_thread)
        ],
        $item
    );

    return $item;
}

function featuredpolls_can_vote($poll)
{
    if ((int)$poll['closed'] === 1) {
        return false;
    }
    $timeout_days = (int)$poll['timeout'];
    if ($timeout_days > 0) {
        $expire_ts = (int)$poll['dateline'] + ($timeout_days * 86400);
        if (TIME_NOW > $expire_ts) {
            return false;
        }
    }
    return true;
}

function featuredpolls_xmlhttp()
{
    global $mybb;
    featuredpolls_load_core_lang();

    $action = $mybb->get_input('action');
    if($action === 'featuredpolls_vote'){ featuredpolls_ajax_vote(); exit; }
    if($action === 'featuredpolls_results'){ featuredpolls_ajax_results(); exit; }
    if($action === 'featuredpolls_undo'){ featuredpolls_ajax_undo(); exit; }
	if($action === 'featuredpolls_reorder') { featuredpolls_ajax_reorder(); exit; }
	if($action === 'featuredpolls_update_expiry'){ featuredpolls_ajax_update_expiry(); exit; }
	if ($action === 'featuredpolls_unfeature') { featuredpolls_ajax_unfeature(); exit; }
	if($action === 'featuredpolls_add_pid'){ featuredpolls_ajax_add_pid(); exit; }

}

function featuredpolls_ajax_unfeature()
{
    global $mybb, $db, $lang;
    $lang->load('featuredpolls');

    if (!is_moderator() && !$mybb->usergroup['cancp']) {
        featuredpolls_ajax_fail($lang->fp_no_permission);
    }

    $pid = (int)$mybb->get_input('pid', MyBB::INPUT_INT);
    if ($pid <= 0) {
        featuredpolls_ajax_fail($lang->fp_invalid_pid);
    }

    $db->update_query('featuredpolls', [
        'featured' => 0,
        'dateline' => TIME_NOW
    ], "pid={$pid}");

	featuredpolls_trim_and_reorder();
    featuredpolls_ajax_ok([
        'pid'     => $pid,
        'message' => $lang->fp_modcp_removed
    ]);
	
}

function featuredpolls_ajax_reorder()
{
    global $mybb, $db, $lang, $templates;

    if (!is_moderator() && !$mybb->usergroup['cancp']) {
        featuredpolls_ajax_fail($lang->fp_error_nopermission);
    }

    $payload   = json_decode($mybb->get_input('payload'), true);
    $moved_pid = (int)$mybb->get_input('moved_pid');
    if (!is_array($payload) || $moved_pid <= 0) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpayload);
    }

    $updates   = [];
    $newStatus = null;
    $newOrder  = null;

    foreach ($payload as $listId => $data) {
        if (empty($data['order'])) {
            continue;
        }

        $status = (int)$data['status'];
        $pos    = 1;

        foreach ((array)$data['order'] as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) {
                continue;
            }

            $update = [
                'disporder' => $pos,
                'featured'  => $status
            ];

            if ($status === 1 && $pid === $moved_pid) {
                $existing = $db->fetch_array(
                    $db->simple_select('featuredpolls', 'featured,expires', "pid={$pid}", ['limit' => 1])
                );
                if ($existing && (int)$existing['featured'] !== 1 && (int)$existing['expires'] === 0) {
                    $default_days = (int)$mybb->settings['featuredpolls_default_expiry_days'];
                    $update['expires']  = $default_days > 0 ? TIME_NOW + ($default_days * 86400) : 0;
                    $update['dateline'] = TIME_NOW;
                }
            }

            $db->update_query('featuredpolls', $update, "pid={$pid}");

            $r = $db->fetch_array($db->query("
                SELECT f.*, p.tid, p.question, t.subject
                FROM ".TABLE_PREFIX."featuredpolls f
                LEFT JOIN ".TABLE_PREFIX."polls p ON(p.pid=f.pid)
                LEFT JOIN ".TABLE_PREFIX."threads t ON(t.tid=p.tid)
                WHERE f.pid={$pid}
                LIMIT 1
            "));

            if ($r) {
				$poll_question  = htmlspecialchars_uni($r['question'] ?: $lang->fp_no_question);
				$thread_subject = htmlspecialchars_uni($r['subject'] ?: $lang->fp_no_subject);
                $date = my_date($mybb->settings['dateformat'].', '.$mybb->settings['timeformat'], (int)$r['dateline']);
                $properties = featuredpolls_get_status_properties($r['featured']);
				$status_label = $properties['label'];
				$status_class = $properties['class'];
						$extra_html = featuredpolls_build_extra_html($r, (int)$r['featured'], $lang, $mybb);

                eval('$updates[$pid] = "'.$templates->get('featuredpolls_modcp_item').'";');
            }

            if ($pid === $moved_pid) {
                $newStatus = $status;
                $newOrder  = $pos;
            }

            $pos++;
        }
    }

    $status_labels = [
        0 => $lang->fp_modcp_status_pending,
        1 => $lang->fp_modcp_status_featured,
        2 => $lang->fp_modcp_status_expired,
        3 => $lang->fp_modcp_status_queued
    ];

    $statusText = "";
    if ($newStatus !== null) {
        $status_name = $status_labels[$newStatus] ?? $lang->fp_unknown;
        $statusText = $lang->sprintf(
			$lang->fp_status_change,
			$moved_pid,
			$status_name,
			$newOrder
		);

    }

    featuredpolls_ajax_ok([
        'message' => $statusText,
        'time'    => date('H:i:s'),
        'updates' => $updates
    ]);
}

function featuredpolls_ajax_fail($msg, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'ok'    => false,
        'error' => htmlspecialchars_uni((string)$msg)
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function featuredpolls_ajax_ok($payload)
{
    if (isset($payload['message'])) {
        $payload['message'] = htmlspecialchars_uni((string)$payload['message']);
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => true] + (array)$payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function featuredpolls_load_poll($pid)
{
    global $db;
    $pid = (int)$pid;
    if (!$pid) return null;

    return $db->fetch_array(
        $db->simple_select('polls', '*', "pid={$pid}", ['limit' => 1])
    );
}

function featuredpolls_user_has_voted($pid, $uid)
{
    global $db;

    $pid = (int)$pid;
    $uid = (int)$uid;

    if ($uid > 0) {
        $q = $db->simple_select('pollvotes', 'vid', "pid={$pid} AND uid={$uid}", ['limit' => 1]);
        return (bool)$db->fetch_field($q, 'vid');
    }

    $ipb = $db->escape_binary(my_inet_pton(get_ip()));
    $q = $db->simple_select('pollvotes', 'vid', "pid={$pid} AND uid=0 AND ipaddress={$ipb}", ['limit' => 1]);
    return (bool)$db->fetch_field($q, 'vid');
}

function featuredpolls_parse_delimited($s)
{
    $parts = explode('||~|~||', (string)$s);
    while (count($parts) && end($parts) === '') {
        array_pop($parts);
    }
    return $parts;
}

function featuredpolls_join_delimited(array $parts)
{
    return implode('||~|~||', $parts);
}

function featuredpolls_build_options_html($poll)
{
    global $templates;

    $maxchoices = (int)$poll['maxoptions'];

    if ($maxchoices == 0 && empty($poll['multiple'])) {
        $maxchoices = 1;
    }

    $input_type = ($maxchoices == 1) ? 'radio' : 'checkbox';

    $options_html = '';
    $opts = featuredpolls_parse_delimited($poll['options']);
    $i = 1;
    foreach ($opts as $text) {
        $parser = new postParser();
		$text = $parser->parse_message($text, [
			'allow_html' => 0,
			'allow_mycode' => 1,
			'allow_smilies' => 1,
			'allow_imgcode' => 1,
			'filter_badwords' => 1
		]);
        $index = $i++;
        eval("\$options_html .= \"".$templates->get('featuredpolls_option')."\";");
    }
    return $options_html;
}

function featuredpolls_get_voters_by_option($pid)
{
    global $db, $mybb, $lang;

    $pid = (int)$pid;
    if ($pid <= 0) return [];

    $max_names = (int)($mybb->settings['featuredpolls_max_public_voters'] ?? 10);
    if ($max_names <= 0) {
        $max_names = 10;
    }

    $q = $db->query("
        SELECT v.voteoption, v.uid, u.username
        FROM ".TABLE_PREFIX."pollvotes v
        LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid = v.uid)
        WHERE v.pid = {$pid}
        ORDER BY v.voteoption ASC, u.username ASC
    ");

    $map = [];
    while ($row = $db->fetch_array($q)) {
        $opt = (int)$row['voteoption'];
        $uid = (int)$row['uid'];
        $uname = $uid > 0 ? (string)$row['username'] : ($lang->fp_guest);

        if (!isset($map[$opt])) {
            $map[$opt] = [];
        }

        if (count($map[$opt]) < $max_names + 1) {
            $map[$opt][] = ['uid' => $uid, 'username' => $uname];
        }
    }

    return $map;
}

function featuredpolls_get_user_vote_options($pid, $uid)
{
    global $db;

    $pid = (int)$pid;
    $uid = (int)$uid;
    if ($pid <= 0 || $uid <= 0) {
        return [];
    }

    $opts = [];
    $q = $db->simple_select('pollvotes', 'voteoption', "pid={$pid} AND uid={$uid}");
    while ($r = $db->fetch_array($q)) {
        $opts[] = (int)$r['voteoption'];
    }
    return $opts;
}

function featuredpolls_build_results_html($poll)
{
    featuredpolls_load_core_lang();
    global $templates, $db, $mybb, $lang;

    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser();

    $options = featuredpolls_parse_delimited($poll['options']);
    $votes   = featuredpolls_parse_delimited($poll['votes']);

    $rows  = [];
    $total = 0;
    $num   = max(count($options), count($votes));

    for ($i = 0; $i < $num; $i++) {
        $text = isset($options[$i]) ? $parser->parse_message($options[$i], [
            'allow_html' => 0,
            'allow_mycode' => 1,
            'allow_smilies' => 1,
            'allow_imgcode' => 1,
            'filter_badwords' => 1
        ]) : '';
        $count = isset($votes[$i]) ? (int)$votes[$i] : 0;
        $rows[] = ['text' => $text, 'count' => $count];
        $total += $count;
    }

    $uid = (int)$mybb->user['uid'];
    $is_public = !empty($poll['public']);
    $show_all_voters = !empty($mybb->settings['featuredpolls_show_public_voters']) && $is_public;

    $voters_by_opt = $show_all_voters ? featuredpolls_get_voters_by_option((int)$poll['pid']) : [];
    $my_vote_opts  = ($uid > 0)
        ? featuredpolls_get_user_vote_options((int)$poll['pid'], $uid)
        : [];

    $max_names = (int)($mybb->settings['featuredpolls_max_public_voters'] ?? 10);
    if ($max_names <= 0) {
        $max_names = 10;
    }

    $html = '';
    foreach ($rows as $idx => $row) {
        $percent   = ($total > 0) ? round(($row['count'] / $total) * 100) : 0;
        $text      = $row['text'];
        $count     = (int)$row['count'];

        $opt_index_1based = $idx + 1;

        if ($uid > 0 && in_array($opt_index_1based, $my_vote_opts, true)) {
            $text .= $lang->fp_voted_marker;
        }

        $voter_names = '';
        if ($show_all_voters && $count > 0 && !empty($voters_by_opt[$opt_index_1based])) {
            $voters = $voters_by_opt[$opt_index_1based];

            usort($voters, function($a, $b) {
                if ($a['uid'] == 0 && $b['uid'] > 0) return 1;
                if ($a['uid'] > 0 && $b['uid'] == 0) return -1;
                if ($a['uid'] > 0 && $b['uid'] > 0) {
                    return strcasecmp($a['username'], $b['username']);
                }
                return 0;
            });

            $names = [];
            $guest_count = 0;

            foreach ($voters as $u) {
                if ($u['uid'] > 0 && $u['username'] !== '') {
                    $un = htmlspecialchars_uni($u['username']);
                    $names[] = '<a href="member.php?action=profile&uid='.$u['uid'].'">'.$un.'</a>';
                } else {
                    $guest_count++;
                }
            }

            if ($guest_count > 0) {
                $names[] = $guest_count == 1
                    ? '1 '.$lang->fp_guest
                    : $guest_count.' '.$lang->fp_guest.'s';
            }

            $more = max(0, count($names) - $max_names);
            $names = array_slice($names, 0, $max_names);

            $voter_names  = implode(', ', $names);
            if ($more > 0) {
                $voter_names .= ' '.str_replace('{1}', $more, $lang->fp_more_voters);
            };
        }

        eval("\$html .= \"".$templates->get('featuredpolls_result')."\";");
    }

    $total_percent = ($total > 0) ? 100 : 0;
    eval("\$html .= \"".$templates->get('featuredpolls_result_total')."\";");
    return $html;
}

function featuredpolls_ajax_results()
{
    global $mybb, $db, $templates, $lang;

    $pid  = (int)$mybb->get_input('pid');
    $poll = featuredpolls_load_poll($pid);
    if (!$poll) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpoll);
    }

    $can_undo = (
        !empty($mybb->usergroup['canundovotes']) ||
        !empty($mybb->usergroup['cancp']) ||
        is_moderator($poll['tid'])
    );

    $can_edit_poll = false;
    if ($mybb->usergroup['cancp'] || is_moderator($poll['tid'])) {
        $can_edit_poll = true;
    } elseif ((int)$mybb->user['uid'] === (int)$poll['thread_uid'] && !empty($mybb->usergroup['caneditpolls'])) {
        $can_edit_poll = true;
    }

    $edit_btn = $can_edit_poll
        ? '<a href="polls.php?action=editpoll&pid='.(int)$poll['pid'].'" class="fp-btn secondary">'.htmlspecialchars_uni($lang->fp_edit).'</a>'
        : '';

    $results_html = featuredpolls_build_results_html($poll);
    $can_vote     = featuredpolls_can_vote($poll);
    $user_voted   = featuredpolls_user_has_voted($pid, (int)$mybb->user['uid']);

    if ($can_vote && !$user_voted) {
        eval("\$default_actions = \"".$templates->get('featuredpolls_actions_vote')."\";");
        $default_actions_escaped = htmlspecialchars_uni($default_actions);

        eval("\$actions_html = \"".$templates->get('featuredpolls_actions_back_to_voting')."\";");
        $actions_html = str_replace(['{$default_actions_escaped}'], [$default_actions_escaped], $actions_html);
    } else {
        if ($user_voted && $can_undo) {
            eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_can_undo')."\";");
            $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
        } else {
            eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_readonly')."\";");
            $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
        }
    }

    featuredpolls_ajax_ok([
        'results_html' => $results_html,
        'actions_html' => $actions_html
    ]);
}

function featuredpolls_ajax_vote()
{
    global $mybb, $db, $lang, $templates;

    if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
        featuredpolls_ajax_fail($lang->fp_error_postkey);
    }

    $pid  = (int)$mybb->get_input('pid');
    $poll = featuredpolls_load_poll($pid);
    if (!$poll) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpoll);
    }
    if (!featuredpolls_can_vote($poll)) {
        featuredpolls_ajax_fail($lang->fp_error_pollclosed);
    }

    if (empty($mybb->usergroup['canvotepolls']) && empty($mybb->usergroup['cancp']) && !is_moderator($poll['tid'])) {
        featuredpolls_ajax_fail($lang->fp_error_nopermission);
    }

    if (featuredpolls_user_has_voted($pid, (int)$mybb->user['uid'])) {
        $poll = featuredpolls_load_poll($pid);
        $results_html = featuredpolls_build_results_html($poll);

        $can_undo = (
            !empty($mybb->usergroup['canundovotes']) ||
            $mybb->usergroup['cancp'] ||
            is_moderator($poll['tid'])
        );

        $can_edit_poll = false;
        if ($mybb->usergroup['cancp'] || is_moderator($poll['tid'])) {
            $can_edit_poll = true;
        } elseif ((int)$mybb->user['uid'] === (int)$poll['thread_uid'] && !empty($mybb->usergroup['caneditpolls'])) {
            $can_edit_poll = true;
        }

        $edit_btn = $can_edit_poll
            ? '<a href="polls.php?action=editpoll&pid='.(int)$poll['pid'].'" class="fp-btn secondary">'.htmlspecialchars_uni($lang->fp_edit).'</a>'
            : '';

        if (featuredpolls_can_vote($poll) && $can_undo) {
            eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_can_undo')."\";");
            $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
        } else {
            eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_readonly')."\";");
            $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
        }

        $payload = [
            'results_html' => $results_html,
            'actions_html' => $actions_html,
            'message'      => ''
        ];

        if (!empty($mybb->settings['featuredpolls_redirect_after_vote'])) {
            $payload['redirect_url'] = 'showthread.php?tid='.(int)$poll['tid'].'#pid'.(int)$poll['pid'];
        }

        featuredpolls_ajax_ok($payload);
    }

    $selected = (array)$mybb->get_input('options', MyBB::INPUT_ARRAY);
    $selected = array_values(array_unique(array_map('intval', $selected)));
    if (empty($selected)) {
        featuredpolls_ajax_fail($lang->fp_error_nooption);
    }

    $maxchoices = (int)$poll['maxoptions'];
    if ($maxchoices == 0 && empty($poll['multiple'])) {
        $maxchoices = 1;
    }

    if ($maxchoices == 1 && count($selected) > 1) {
        featuredpolls_ajax_fail($lang->fp_error_onlyone);
    }
    if ($maxchoices > 1 && count($selected) > $maxchoices) {
		featuredpolls_ajax_fail($lang->sprintf($lang->fp_error_maxtoosoon, $maxchoices));
    }

    $opts       = featuredpolls_parse_delimited($poll['options']);
    $votes      = featuredpolls_parse_delimited($poll['votes']);
    $numOptions = count($opts);

    foreach ($selected as $opt) {
        if ($opt < 1 || $opt > $numOptions) {
            featuredpolls_ajax_fail($lang->fp_error_invalidoption);
        }
    }

    for ($i = 0; $i < $numOptions; $i++) {
        if (!isset($votes[$i]) || $votes[$i] === '') {
            $votes[$i] = '0';
        }
    }

    $uid = (int)$mybb->user['uid'];
    $now = TIME_NOW;
    $ipb = $db->escape_binary(my_inet_pton(get_ip()));

    foreach ($selected as $opt) {
        $votes[$opt-1] = (string)((int)$votes[$opt-1] + 1);
        $db->insert_query('pollvotes', [
            'pid'        => $pid,
            'uid'        => $uid,
            'voteoption' => (int)$opt,
            'ipaddress'  => $ipb,
            'dateline'   => $now
        ]);
    }

    $numvotes_new = array_sum(array_map('intval', $votes));

    $db->update_query('polls', [
        'votes'    => $db->escape_string(featuredpolls_join_delimited($votes)),
        'numvotes' => $numvotes_new
    ], "pid={$pid}");

    $poll['votes']    = featuredpolls_join_delimited($votes);
    $poll['numvotes'] = $numvotes_new;

    $results_html = featuredpolls_build_results_html($poll);

    $can_edit_poll = false;
    if ($mybb->usergroup['cancp'] || is_moderator($poll['tid'])) {
        $can_edit_poll = true;
    } elseif ((int)$mybb->user['uid'] === (int)$poll['thread_uid'] && !empty($mybb->usergroup['caneditpolls'])) {
        $can_edit_poll = true;
    }

    $edit_btn = $can_edit_poll
        ? '<a href="polls.php?action=editpoll&pid='.(int)$poll['pid'].'" class="fp-btn secondary">'.htmlspecialchars_uni($lang->fp_edit).'</a>'
        : '';

    $can_undo = (
        !empty($mybb->usergroup['canundovotes']) ||
        !empty($mybb->usergroup['cancp']) ||
        is_moderator($poll['tid'])
    );

    if (featuredpolls_can_vote($poll) && $can_undo) {
        eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_can_undo')."\";");
        $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
    } else {
        eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_readonly')."\";");
        $actions_html = str_replace('{$edit_btn}', $edit_btn, $actions_html);
    }

    $payload = [
        'results_html' => $results_html,
        'actions_html' => $actions_html,
        'message'      => $lang->fp_votethanks
    ];

    if (!empty($mybb->settings['featuredpolls_redirect_after_vote'])) {
        $payload['redirect_url'] = 'showthread.php?tid='.(int)$poll['tid'].'#pid'.(int)$poll['pid'];
    }

    featuredpolls_ajax_ok($payload);
}

function featuredpolls_ajax_undo()
{
    global $mybb, $db, $templates, $lang;

    if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
        featuredpolls_ajax_fail($lang->fp_error_postkey);
    }

    $pid  = (int)$mybb->get_input('pid');
    $poll = featuredpolls_load_poll($pid);
    if (!$poll) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpoll);
    }
    if (!featuredpolls_can_vote($poll)) {
        featuredpolls_ajax_fail($lang->fp_error_pollclosed);
    }

    $can_undo = (
        !empty($mybb->usergroup['canundovotes']) ||
        !empty($mybb->usergroup['cancp']) ||
        is_moderator($poll['tid'])
    );
    if (!$can_undo) {
        featuredpolls_ajax_fail($lang->fp_error_noundo);
    }

    $voted_opts = [];
    if ((int)$mybb->user['uid'] > 0) {
        $q = $db->simple_select('pollvotes','voteoption',"pid={$pid} AND uid=".(int)$mybb->user['uid']);
    } else {
        $ipb = $db->escape_binary(my_inet_pton(get_ip()));
        $q = $db->simple_select('pollvotes','voteoption',"pid={$pid} AND uid=0 AND ipaddress={$ipb}");
    }
    while ($row = $db->fetch_array($q)) {
        $voted_opts[] = (int)$row['voteoption'];
    }

    if (empty($voted_opts)) {
        $results_html = featuredpolls_build_results_html($poll);
        eval("\$actions_html = \"".$templates->get('featuredpolls_actions_results_readonly')."\";");
        $actions_html = str_replace(['{$edit_btn}'], [''], $actions_html);

        featuredpolls_ajax_ok([
            'results_html' => $results_html,
            'actions_html' => $actions_html,
            'message'      => ''
        ]);
    }

    $opts       = featuredpolls_parse_delimited($poll['options']);
    $votes      = featuredpolls_parse_delimited($poll['votes']);
    $numOptions = max(count($opts), count($votes));

    for ($i = 0; $i < $numOptions; $i++) {
        if (!isset($votes[$i]) || $votes[$i] === '') {
            $votes[$i] = '0';
        }
    }

    foreach ($voted_opts as $opt) {
        $idx = $opt - 1;
        if ($idx >= 0 && $idx < $numOptions) {
            $votes[$idx] = (string)max(0, (int)$votes[$idx] - 1);
        }
    }

    if ((int)$mybb->user['uid'] > 0) {
        $db->delete_query('pollvotes', "pid={$pid} AND uid=".(int)$mybb->user['uid']);
    } else {
        $ipb = $db->escape_binary(my_inet_pton(get_ip()));
        $db->delete_query('pollvotes', "pid={$pid} AND uid=0 AND ipaddress={$ipb}");
    }

    $numvotes_new = array_sum(array_map('intval',$votes));
    $db->update_query('polls', [
        'votes'    => $db->escape_string(featuredpolls_join_delimited($votes)),
        'numvotes' => $numvotes_new
    ], "pid={$pid}");

    $poll['votes']    = featuredpolls_join_delimited($votes);
    $poll['numvotes'] = $numvotes_new;

    $options_html = featuredpolls_build_options_html($poll);

    $vote_text    = htmlspecialchars_uni($lang->fp_vote);
    $results_text = htmlspecialchars_uni($lang->fp_results);

    eval("\$actions_html = \"".$templates->get('featuredpolls_actions_vote')."\";");
    $actions_html = str_replace(
        ['{$vote_text}', '{$results_text}'],
        [$vote_text, $results_text],
        $actions_html
    );

    featuredpolls_ajax_ok([
        'options_html' => $options_html,
        'actions_html' => $actions_html,
        'message'      => $lang->fp_unvoted
    ]);
}

function featuredpolls_ajax_update_expiry()
{
    global $mybb, $db, $lang;

    if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
        featuredpolls_ajax_fail($lang->fp_error_postkey ?? "Invalid POST key.");
    }

    $pid = (int)$mybb->get_input('pid', MyBB::INPUT_INT);
    $ts  = (int)$mybb->get_input('expires', MyBB::INPUT_INT);

    if ($pid <= 0) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpoll ?? "Invalid poll ID");
    }

    if ($ts < 0) {
        $ts = 0;
    }

    $db->update_query('featuredpolls', ['expires' => $ts], "pid={$pid}");

    if ($ts > 0) {
        $new_picker = my_date('Y-m-d\TH:i', $ts);
        $new_human  = my_date($mybb->settings['dateformat'].', '.$mybb->settings['timeformat'], $ts);
    } else {
        $new_picker = '';
        $new_human  = $lang->fp_no_expiry;
    }

    featuredpolls_ajax_ok([
        'message'    => $lang->fp_modcp_expiry_updated ?? "Expiry updated",
        'new_picker' => $new_picker,
        'new_human'  => $new_human
    ]);
}

function featuredpolls_ajax_add_pid()
{
    global $mybb, $db, $lang, $templates;
    $lang->load('featuredpolls');

    if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
        featuredpolls_ajax_fail($lang->invalid_post_code ?? "Invalid POST key");
    }

    $pids_raw = trim($mybb->get_input('pids'));
    $pids = array_unique(array_filter(array_map('intval', preg_split('/[,\s]+/', $pids_raw))));

    if (empty($pids)) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpoll);
    }

    $added      = [];
    $present    = [];
    $invalid    = [];
    $items_html = [];

    $polls = [];
    $q = $db->simple_select('polls', 'pid', "pid IN (".implode(',', $pids).")");
    while ($row = $db->fetch_array($q)) {
        $polls[(int)$row['pid']] = true;
    }

    $in_featured = [];
    $q = $db->simple_select('featuredpolls', 'pid', "pid IN (".implode(',', $pids).")");
    while ($row = $db->fetch_array($q)) {
        $in_featured[(int)$row['pid']] = true;
    }

    foreach ($pids as $pid) {
        if (!isset($polls[$pid])) {
            $invalid[] = $pid;
            continue;
        }
        if (isset($in_featured[$pid])) {
            $present[] = $pid;
            continue;
        }

		$max_order = (int)$db->fetch_field(
			$db->simple_select('featuredpolls', 'MAX(disporder) as maxdisp', "featured=3"),
			'maxdisp'
		);
		$disporder = $max_order + 1;

        $db->insert_query('featuredpolls', [
            'pid'       => $pid,
            'featured'  => 3,
            'dateline'  => TIME_NOW,
            'expires'   => 0,
            'disporder' => $disporder
        ]);
        $added[] = $pid;

        $r = $db->fetch_array($db->query("
            SELECT f.*, p.tid, p.question, t.subject
            FROM ".TABLE_PREFIX."featuredpolls f
            LEFT JOIN ".TABLE_PREFIX."polls p ON(p.pid=f.pid)
            LEFT JOIN ".TABLE_PREFIX."threads t ON(t.tid=p.tid)
            WHERE f.pid={$pid}
            LIMIT 1
        "));
        if ($r) {
			$poll_question  = htmlspecialchars_uni($r['question'] ?: $lang->fp_no_question);
			$thread_subject = htmlspecialchars_uni($r['subject'] ?: $lang->fp_no_subject);
            $date = my_date($mybb->settings['dateformat'].', '.$mybb->settings['timeformat'], (int)$r['dateline']);
            $properties = featuredpolls_get_status_properties($r['featured']);
			$status_label = $properties['label'];
			$status_class = $properties['class'];
            $extra_html = featuredpolls_build_extra_html($r, (int)$r['featured'], $lang, $mybb);

            eval('$items_html[] = "'.$templates->get('featuredpolls_modcp_item').'";');
        }
    }

    if (empty($added) && empty($present) && empty($invalid)) {
        featuredpolls_ajax_fail($lang->fp_error_invalidpoll);
    }

    featuredpolls_ajax_ok([
        'added'      => $added,
        'present'    => $present,
        'invalid'    => $invalid,
        'html_items' => implode('', $items_html)
    ]);
}

function featuredpolls_set_featured($pid)
{
    global $db, $mybb;

    $pid = (int)$pid;
    if ($pid <= 0) {
        return false;
    }

    // Respect max limit
    $max = (int)$mybb->settings['featuredpolls_max_display'];
    if ($max > 0) {
        $current = (int)$db->fetch_field(
            $db->simple_select('featuredpolls', 'COUNT(*) AS c', 'featured=1'),
            'c'
        );
        if ($current >= $max) {
            return false;
        }
    }

    // Expiry
$default_days = (int)$mybb->settings['featuredpolls_default_expiry_days'];
$default_expiry = $default_days > 0 ? TIME_NOW + ($default_days * 86400) : 0;

// Get poll’s timeout (in days)
$poll_timeout_days = (int)$db->fetch_field(
    $db->simple_select('polls', 'timeout', "pid={$pid}", ['limit' => 1]),
    'timeout'
);
$poll_timeout = $poll_timeout_days > 0 ? TIME_NOW + ($poll_timeout_days * 86400) : 0;

$expires = $default_expiry;

// Behavior setting
$behavior = $mybb->settings['featuredpolls_timeout_behavior'] ?? 'keep';

if ($behavior === 'autoexpire' && $poll_timeout > 0) {
    if ($default_expiry > 0) {
        // Use whichever expires first
        $expires = min($poll_timeout, $default_expiry);
    } else {
        $expires = $poll_timeout;
    }
}

    // Promote position
    $position = trim(strtolower($mybb->settings['featuredpolls_promote_position'] ?? 'top'));

    if ($position === 'bottom') {
        // append to end
        $maxdisp = (int)$db->fetch_field(
            $db->simple_select('featuredpolls', 'MAX(disporder) AS m', 'featured=1'),
            'm'
        );
        $disporder = $maxdisp + 1;
    } else {
        // insert at top
        $db->write_query("
            UPDATE ".TABLE_PREFIX."featuredpolls
            SET disporder = disporder + 1
            WHERE featured = 1
        ");
        $disporder = 1;
    }

    // Insert or update
    $exists = (int)$db->fetch_field(
        $db->simple_select('featuredpolls', 'pid', "pid={$pid}", ['limit' => 1]),
        'pid'
    );

    if ($exists) {
        $db->update_query('featuredpolls', [
            'featured'  => 1,
            'dateline'  => TIME_NOW,
            'expires'   => $expires,
            'disporder' => $disporder
        ], "pid={$pid}");
    } else {
        $db->insert_query('featuredpolls', [
            'pid'       => $pid,
            'featured'  => 1,
            'dateline'  => TIME_NOW,
            'expires'   => $expires,
            'disporder' => $disporder
        ]);
    }

    return true;
}

function featuredpolls_build_modcp_list($status_id, $limit = 0)
{
    global $db, $mybb, $lang, $templates;

    $items = '';
    $count = 0; // Add a counter
    $where = "f.featured = " . (int)$status_id;
    $order_by = ($status_id == 2) ? "f.expires DESC" : "f.dateline ASC";
    $limit_sql = ($limit > 0) ? "LIMIT " . (int)$limit : "";

    $q = $db->query("
        SELECT f.*, p.tid, p.question, t.subject
        FROM ".TABLE_PREFIX."featuredpolls f
        LEFT JOIN ".TABLE_PREFIX."polls p ON (p.pid=f.pid)
        LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
        WHERE {$where}
        ORDER BY f.disporder ASC, {$order_by}
        {$limit_sql}
    ");
    
    $count = $db->num_rows($q); // Get the count of items

    while ($r = $db->fetch_array($q)) {
        $poll_question  = htmlspecialchars_uni($r['question'] ?: $lang->fp_no_question);
        $thread_subject = htmlspecialchars_uni($r['subject'] ?: $lang->fp_no_subject);
        $date = my_date($mybb->settings['dateformat'].', '.$mybb->settings['timeformat'], (int)$r['dateline']);
        
        $properties = featuredpolls_get_status_properties($r['featured']);
        $status_label = $properties['label'];
        $status_class = $properties['class'];
        
        $extra_html = featuredpolls_build_extra_html($r, (int)$r['featured'], $lang, $mybb);
        eval("\$items .= \"".$templates->get('featuredpolls_modcp_item')."\";");
    }
    
    if ($items === '') {
        $items = "<li class='fp-empty'>{$lang->fp_modcp_drop_here}</li>";
    }

    // Return an array with both the HTML and the count
    return ['html' => $items, 'count' => $count];
}

function featuredpolls_modcp()
{
	global $mybb, $db, $templates, $headerinclude, $header, $theme, $footer, $modcp_nav, $lang;

	// --- 1. Add ModCP Nav Link ---
	if (strpos((string)$modcp_nav, '<!--FEATUREDPOLLS_NAV-->') !== false)
	{
		$scope = $mybb->settings['featuredpolls_manage_scope'] ?? 'cancp';
		$can_manage = false;

		if ($scope === 'admin' && !empty($mybb->usergroup['cancp']))
		{
			$can_manage = true;
		}
		elseif ($scope !== 'admin' && (is_moderator() || !empty($mybb->usergroup['cancp'])))
		{
			$can_manage = true;
		}

		if ($can_manage)
		{
			eval('$__fp_nav = "'.$templates->get('featuredpolls_modcp_nav').'";');
			$modcp_nav = str_replace('<!--FEATUREDPOLLS_NAV-->', $__fp_nav, (string)$modcp_nav);
		}
	}

	// --- 2. Check if we are on the correct page ---
	if ($mybb->get_input('action') !== 'featuredpolls')
	{
		return;
	}

	// --- 3. From this point on, we are rendering the main page ---
	
	// Permission checks for the page itself
	$scope = $mybb->settings['featuredpolls_manage_scope'] ?? 'cancp';
	if ($scope === 'admin' && empty($mybb->usergroup['cancp']))
	{
		error_no_permission();
	}
	elseif ($scope !== 'admin' && !is_moderator() && empty($mybb->usergroup['cancp']))
	{
		error_no_permission();
	}

	// Run page logic
	featuredpolls_cleanup_expired();

	add_breadcrumb($lang->fp_breadcrumb_modcp, "modcp.php");
	add_breadcrumb($lang->fp_modcp_page_title, "modcp.php?action=featuredpolls");

	if ($mybb->request_method === 'post')
	{
        verify_post_check($mybb->get_input('my_post_key'));

        if (isset($mybb->input['saveorder'])) {
            $payload = json_decode($mybb->get_input('neworder'), true);
            if (is_array($payload)) {
                foreach ($payload as $listId => $data) {
                    if (empty($data['order'])) continue;

                    $status = 0;
                    if ($listId === 'fp-featured') $status = 1;
                    elseif ($listId === 'fp-expired') $status = 2;
                    elseif ($listId === 'fp-queue') $status = 3;
                    elseif ($listId === 'fp-pending') $status = 0;

                    $pos = 1;
                    foreach ((array)$data['order'] as $pid) {
                        $pid = (int)$pid;
                        if ($pid > 0) {
                            $db->update_query('featuredpolls', [
                                'disporder' => $pos,
                                'featured'  => $status
                            ], "pid={$pid}");
                            $pos++;
                        }
                    }
                }
            }
            redirect('modcp.php?action=featuredpolls', $lang->fp_modcp_redirect_order_saved);
        }

		if (isset($mybb->input['approve'])) {
			$selected = array_map('intval', (array)$mybb->get_input('selected', MyBB::INPUT_ARRAY));
			$selected = array_filter($selected);

			if ($selected) {
				foreach ($selected as $pid) {
					featuredpolls_set_featured($pid);
				}
			}
			redirect('modcp.php?action=featuredpolls', $lang->fp_modcp_redirect_order_saved);
		}

        if (isset($mybb->input['queue'])) {
            $selected = array_map('intval', (array)$mybb->get_input('selected', MyBB::INPUT_ARRAY));
            if ($selected) {
                $in = implode(',', $selected);
                $db->update_query('featuredpolls', [
                    'featured' => 3,
                    'dateline' => TIME_NOW
                ], "pid IN ({$in})");
            }
            redirect('modcp.php?action=featuredpolls', $lang->fp_modcp_redirect_queued);
        }

        if (isset($mybb->input['unfeature'])) {
            $selected = array_map('intval', (array)$mybb->get_input('selected', MyBB::INPUT_ARRAY));
            if ($selected) {
                $in = implode(',', $selected);
                $db->update_query('featuredpolls', [
                    'featured' => 0,
                    'dateline' => TIME_NOW
                ], "pid IN ({$in})");
            }
            redirect('modcp.php?action=featuredpolls', $lang->fp_modcp_redirect_unfeatured);
        }

        if (isset($mybb->input['remove'])) {
            $selected = array_map('intval', (array)$mybb->get_input('selected', MyBB::INPUT_ARRAY));
            if ($selected) {
                $in = implode(',', $selected);
                $db->delete_query('featuredpolls', "pid IN ({$in})");
            }
            redirect('modcp.php?action=featuredpolls', $lang->fp_redirect_removed);
        }

        if (isset($mybb->input['update_expiry']) && is_array($mybb->input['expires'])) {
            foreach ($mybb->input['expires'] as $pid => $ts) {
                $pid = (int)$pid;
                $ts  = (int)$ts;
                if ($pid > 0 && $ts >= 0) {
                    $db->update_query('featuredpolls', ['expires' => $ts], "pid={$pid}");
                }
            }
            redirect('modcp.php?action=featuredpolls', $lang->fp_modcp_redirect_expiry_updated.' ('.date('Y-m-d H:i').')');
        }
		
		if (isset($mybb->input['expire'])) {
			$selected = array_map('intval', (array)$mybb->get_input('selected', MyBB::INPUT_ARRAY));
			if ($selected) {
				$in = implode(',', $selected);
				$db->update_query('featuredpolls', [
					'featured' => 2
				], "pid IN ({$in})");
			}
			redirect('modcp.php?action=featuredpolls', $lang->fp_modcp_redirect_expired);
		}
	}

	$limit = (int)$mybb->settings['featuredpolls_max_display'];
	if ($limit <= 0) $limit = 5;

	$featured_data = featuredpolls_build_modcp_list(1, $limit);
	$requests_data = featuredpolls_build_modcp_list(0);
	$expired_data  = featuredpolls_build_modcp_list(2);
	$queue_data    = featuredpolls_build_modcp_list(3);

	$featured = $featured_data['html'];
	$requests = $requests_data['html'];
	$expired  = $expired_data['html'];
	$queue    = $queue_data['html'];
	
	$featured_poll_count = $featured_data['count'];
	$max_featured = (int)$mybb->settings['featuredpolls_max_display'];
	$featured_count_html = "<span id=\"fp-featured-counter\" class=\"smalltext\" style=\"float:right;\">({$featured_poll_count}/{$max_featured} {$lang->fp_modcp_slots})</span>";

	eval("\$add_by_pid = \"".$templates->get('featuredpolls_modcp_add_pid')."\";");
	eval("\$page = \"".$templates->get('featuredpolls_modcp')."\";");
	output_page($page);
	exit;
}

function featuredpolls_build_extra_html($r, $status, $lang, $mybb)
{
    $pid = (int)$r['pid'];
    $ts  = (int)$r['expires'];

	if ($status === 1) {
		$expiry_value = (int)$r['expires'] > 0
			? my_date('Y-m-d\TH:i', (int)$r['expires'])
			: '';
		$expiry_human = (int)$r['expires'] > 0
			? my_date($mybb->settings['dateformat'].', '.$mybb->settings['timeformat'], (int)$r['expires'])
			: $lang->fp_no_expiry;

		return "
		  <div class='smalltext smalltext-extra'>
			{$lang->fp_modcp_expires}:
			<input type='datetime-local' 
				   id='fp-expiry-{$pid}' 
				   class='fp-expiry' 
				   data-pid='{$pid}' 
				   value='{$expiry_value}' 
				   autocomplete='off' />
			<button type='button' 
					class='button fp-expiry-save' 
					id='fp-expiry-save-{$pid}' 
					data-pid='{$pid}'>
			  {$lang->fp_modcp_save}
			</button>
			<span class='fp-expiry-human' id='fp-expiry-human-{$pid}'>
			  ({$expiry_human})
			</span>
		  </div>";
	}

	if ($status === 2) {
		$expiry_human = (int)$r['expires'] > 0
			? my_date($mybb->settings['dateformat'].', '.$mybb->settings['timeformat'], (int)$r['expires'])
			: $lang->fp_no_expiry;

		return "<div class='smalltext smalltext-extra'>
			{$lang->fp_modcp_expires}: <em>{$expiry_human}</em>
		</div>";
	}

    if ($status === 3) {
        $place = (int)$r['disporder'];
        return "<div class='smalltext smalltext-extra'>
            {$lang->fp_modcp_queue_place} {$place}
        </div>";
    }

    return "<div class='smalltext smalltext-extra'></div>";
}
