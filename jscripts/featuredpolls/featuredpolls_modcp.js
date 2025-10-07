// featuredpolls_modcp.js
(function($) {
    "use strict";

    function showToast(msg, type) {
        const theme = type || "success";
        if (typeof MyBB !== "undefined" && typeof MyBB.jGrowl === "function") {
            MyBB.jGrowl(msg, { theme: theme });
        } else if (typeof $.jGrowl === "function") {
            $.jGrowl(msg, { theme: theme });
        } else {
            alert(msg);
        }
    }

    function refreshPlaceholders() {
        $(".fp-sortable").each(function() {
            const items = $(this).children(".fp-item");
            const placeholder = $(this).children(".fp-empty");
            if (items.length === 0) {
                if (placeholder.length === 0) {
                    $(this).append(`<li class='fp-empty'>${fpLang.dropHere}</li>`);
                }
            } else {
                placeholder.remove();
            }
        });
    }
	
	function updateFeaturedCount() {
		const counterSpan = $('#fp-featured-counter');
		if (counterSpan.length === 0) return; // Exit if the counter element doesn't exist

		const current = $('#fp-featured .fp-item').length;
		const max = (typeof FEATURED_LIMIT !== 'undefined') ? FEATURED_LIMIT : 5;

		counterSpan.text(`(${current}/${max} slots)`);
	}

    $(function() {
		updateFeaturedCount();
        // === Unfeature button ===
        $(document).on("click", ".fp-unfeature", function() {
            const pid = $(this).data("pid");
            $.post("xmlhttp.php?action=featuredpolls_unfeature", {
                my_post_key: postKey,
                pid: pid
            }, (resp) => {
                if (resp && resp.ok) {
                    $("#fp_" + pid).remove();
                    refreshPlaceholders();
					updateFeaturedCount();
                    showToast(resp.message || fpLang.removed, "success");
                } else {
                    showToast(`${fpLang.ajaxError}: ${resp.error || resp.message || fpLang.ajaxUnknown}`, "error");
                }
            }, "json").fail(() => {
                showToast(fpLang.errorNetwork, true);
            });
        });

        // === Expiry save ===
        function saveExpiry(pid, rawVal) {
            let ts = 0;
            if (rawVal) {
                const localDate = new Date(rawVal);
                ts = Math.floor(localDate.getTime() / 1000);
            }

            $.post("xmlhttp.php?action=featuredpolls_update_expiry", {
                my_post_key: postKey,
                pid: pid,
                expires: ts
            }, (resp) => {
                if (resp && resp.ok) {
                    showToast(`${resp.message}: ${resp.new_human}`);
                    $("#fp-expiry-human-" + pid).text(`(${resp.new_human})`);
                } else {
                    showToast(`${fpLang.ajaxError}: ${resp.error || resp.message || fpLang.ajaxUnknown}`, "error");
                }
            }, "json").fail(() => {
                showToast(fpLang.errorNetwork, true);
            });
        }

        $(document).on("click", ".fp-expiry-save", function() {
            const pid = $(this).data("pid");
            const rawVal = $(this).siblings(".fp-expiry").val();
            saveExpiry(pid, rawVal);
        });

        $(document).on("keydown", ".fp-expiry", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const pid = $(this).data("pid");
                const rawVal = $(this).val();
                saveExpiry(pid, rawVal);
            }
        });

        // === Add-by-PID form ===
        $("#fp_add_form").on("submit", (e) => {
            e.preventDefault();
            $("#fp_add_btn").trigger("click");
        });

        $("#fp_add_pids").on("keypress", (e) => {
            if (e.which === 13) {
                e.preventDefault();
                $("#fp_add_btn").trigger("click");
            }
        });

        $("#fp_add_btn").on("click", () => {
            const pids = $("#fp_add_pids").val().trim();
            if (!pids) {
                showToast(fpLang.errorInvalidPoll, true);
                return;
            }

            $.post("xmlhttp.php?action=featuredpolls_add_pid", {
                my_post_key: postKey,
                pids: pids
            }, (resp) => {
                if (resp && resp.ok) {
                    if (resp.added && resp.added.length) {
                        showToast(fpLang.ajaxAdded.replace("{1}", resp.added.join(", ")), "success");
                    }
                    if (resp.present && resp.present.length) {
                        showToast(fpLang.ajaxPresent.replace("{1}", resp.present.join(", ")), "warning");
                    }
                    if (resp.invalid && resp.invalid.length) {
                        showToast(fpLang.ajaxInvalid.replace("{1}", resp.invalid.join(", ")), "error");
                    }
                    if (resp.html_items) {
                        $("#fp-queue .fp-empty").remove();
                        $("#fp-queue").append(resp.html_items);
                        refreshPlaceholders();
                        $(".fp-sortable").sortable("refresh");
                    }
                    $("#fp_add_pids").val("");
                } else {
                    showToast(resp.error || resp.message || fpLang.ajaxError, "error");
                }
            }, "json").fail(() => {
                showToast(fpLang.errorNetwork, true);
            });
        });

        // === Sortable (drag + drop) ===
        $(".fp-sortable").sortable({
            connectWith: ".fp-sortable",
            placeholder: "ui-state-highlight",
            forcePlaceholderSize: true,
            tolerance: "pointer",
            revert: 150,
            cursor: "grabbing",
            handle: ".fp-handle",
            start: refreshPlaceholders,
			stop: function() { 
				refreshPlaceholders();
				updateFeaturedCount(); // Update count after dragging stops
			},
            update: function(event, ui) {
                if (ui.sender) return;

                const featuredList = $("#fp-featured");
                const featuredCount = featuredList.children(".fp-item").length;
                const maxFeatured = (typeof FEATURED_LIMIT !== "undefined" && FEATURED_LIMIT > 0) ? FEATURED_LIMIT : 5;

                if (featuredList.length && featuredCount > maxFeatured) {
                    $(this).sortable("cancel");
                    showToast(fpLang.limitReached.replace("{1}", maxFeatured), "warning");
                    return;
                }

                const movedPid = ui.item.attr("id").replace("fp_", "");
                const payload = {};
                const statusMap = {
                    "fp-featured": { status: 1, text: fpLang.statusFeatured, cls: "fp-status-featured" },
                    "fp-pending":  { status: 0, text: fpLang.statusPending,  cls: "fp-status-pending"  },
                    "fp-expired":  { status: 2, text: fpLang.statusExpired,  cls: "fp-status-expired"  },
                    "fp-queue":    { status: 3, text: fpLang.statusQueued,   cls: "fp-status-queued"   }
                };

                $(".fp-sortable").each(function() {
                    const listId = $(this).attr("id");
                    const map = statusMap[listId];
                    if (!map) return;
                    const order = $(this).sortable("toArray").map(id => id.replace("fp_", ""));
                    payload[listId] = { status: map.status, order: order };
                });

                const parentId = ui.item.parent().attr("id");
                const map = statusMap[parentId];
                if (map) {
                    const span = ui.item.find(".fp-status");
                    if (span.length) {
                        span.text(map.text)
                            .removeClass("fp-status-featured fp-status-pending fp-status-expired fp-status-queued")
                            .addClass(map.cls);
                    }
                }

                $.post("xmlhttp.php?action=featuredpolls_reorder", {
                    my_post_key: postKey,
                    payload: JSON.stringify(payload),
                    moved_pid: movedPid
                }, (resp) => {
                    if (resp && resp.ok) {
                        if (resp.updates) {
                            Object.keys(resp.updates).forEach(pid => {
                                $("#fp_" + pid).replaceWith(resp.updates[pid]);
                            });
                        }
                        if (resp.message) {
                            showToast(resp.message, "success");
                        }
                    }
                }, "json");
            }
        }).disableSelection();

        refreshPlaceholders();
    });

})(jQuery);