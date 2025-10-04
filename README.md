# Featured Polls for MyBB

***(Still in very early stage of  development. Code logic and structure might be reworked for performance, compatibility, and security purposes***
Promote thread polls into a global **Featured Polls** block with rich management tools, AJAX-powered voting, and moderator controls — all while keeping the plugin lightweight, clean, and secure.
 * Featured Polls (MyBB 1.8.39+ / PHP 8.4)

---

## ✨ Features

- **Global Featured Polls Block**  
  Display selected polls anywhere (e.g. forum index) with integrated vote/results toggle.

- **AJAX Voting**  
  Seamless vote submission, undo vote, and results toggle without full page reload.  
  Includes fallback for older browsers.

- **Poll Management (Mod CP)**  
  - Add/remove polls from the featured list by PID  
  - Bulk approve or unfeature polls  
  - Drag & drop reorder with live updates  
  - Expiry date picker with AJAX save  
  - Auto-promote queued polls when slots free up  
  - Enforces a maximum number of featured polls

- **Poll Request System**  
  Users can request their poll be featured. Moderators can approve, queue, or reject.

- **Expiry Handling**  
  - Polls auto-expire after configurable days  
  - Expired polls can be auto-replaced with queued ones  
  - Cleanup trims extra polls if settings change (e.g. max display lowered)

- **User Feedback**  
  - Inline status display for poll owners (“Pending Review”, “Featured until …”, “Expired”)  
  - Optional toast notifications in ModCP actions

- **Lightweight & Secure**  
  - Minimal SQL footprint (one extra table)  
  - No core file edits  
  - Uses MyBB’s post key system to prevent CSRF  
  - Enforces permissions (only moderators/admins can manage)  
  - Trims and reorders automatically to keep DB clean and fast

---

## 📦 Installation

1. Upload `featuredpolls.php` to `inc/plugins/`  
2. Upload `featuredpolls.lang.php` to `inc/languages/english/` (and your other languages if desired)  
3. Activate the plugin in **ACP → Configuration → Plugins**

---

## ⚙️ Configuration

Settings available under **ACP → Configuration → Settings → Featured Polls**:

- **Max Featured Polls Displayed** (limit slots globally)  
- **Default Expiry Days** (auto-expire polls after N days)  
- **Auto-Promote Queue** (replace expired polls with queued ones)  
- **Promote Position** (add new polls to top or bottom of list)  
- **Group Permissions** (who can request / view featured polls)
- **And many more

---

## 🔒 Security

- All actions require moderator/admin privileges  
- Every AJAX call validated with MyBB’s `my_post_key`  
- User input sanitized with strict integer casting  
- Expiry dates normalized server-side (no client tampering)

---

## ⚡ Performance

- One small extra table (`featuredpolls`)  
- Simple indexed queries  
- Auto-trim keeps featured list size small  
- Inline JS moved to a single minified file for speed  
- Fully compatible with PHP 8.x  

---

## 📸 Screenshots (optional)

_TO BE UPDATED_

---

## 📝 License

This plugin is open-source software released under the **MIT License**.  
You are free to use, modify, and share it.

---
