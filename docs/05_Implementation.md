# Chapter 5 — Implementation

## 5.1 Introduction

This chapter describes how the design specified in Chapter 4 was realised. It is
organised by subsystem, and each section identifies the source files implementing the
relevant requirements, presents the significant code, and explains the technique
employed. Section 5.8 gives a consolidated account of the security controls implemented
and, equally importantly, those specified in Section 4.2.2 that were not.

Code extracts are abridged for readability; presentation markup is omitted where it does
not bear on the mechanism under discussion. Full sources are in the accompanying
repository.

## 5.2 Implementation Overview

### 5.2.1 Codebase Structure

```
Weblogr/
├── index.html                  Static landing page
├── database/
│   ├── db.php                  Connection establishment
│   └── weblogr.sql             Schema and seed data
├── registration/               Account lifecycle — 11 scripts
│   ├── signup.php              FR-01, FR-03
│   ├── otp_verification.php    FR-02
│   ├── login.php               FR-04
│   ├── logout.php              FR-06
│   ├── forgot_password.php     FR-05
│   ├── reset_password.php      FR-05
│   ├── mail.php                OTP delivery — registration
│   ├── pass_mail.php           OTP delivery — recovery
│   ├── profile.php             FR-21
│   ├── edit_profile.php        FR-21
│   ├── success.php             Registration confirmation
│   ├── style.css
│   ├── index.js                Client-side validation
│   └── vendor/                 PHPMailer 6.9 (Composer)
├── posts/                      Content and social graph — 15 scripts
│   ├── index.php               FR-13, FR-14, FR-15
│   ├── new_post.php            FR-07
│   ├── save_post.php           FR-07, FR-08, FR-09
│   ├── edit_post.php           FR-10
│   ├── update_post.php         FR-10
│   ├── delete_post.php         FR-11
│   ├── user_posts.php          FR-12
│   ├── draft_posts.php         FR-08
│   ├── edit_draft.php          FR-08
│   ├── blog_poster.php         Author profile view
│   ├── follow.php              FR-19
│   ├── notifications.php       FR-20
│   ├── delete_notifications.php FR-20
│   ├── report.php              FR-22
│   ├── reports.php             FR-23
│   ├── manage_content.php      FR-23
│   ├── sidebar.php             Shared navigation
│   ├── logout.php              FR-06
│   ├── style.css
│   └── index.js
├── comments/                   Commenting — 4 scripts
│   ├── comments.php            FR-16
│   ├── save_comment.php        FR-16
│   ├── likes.php               FR-17 (post likes)
│   ├── like_a_comment.php      FR-18
│   └── style.css
├── images/                     Post images
├── uploads/                    Profile images
└── styles/style.css            Landing page styles
```

### 5.2.2 Database Connectivity

All data access originates from a single connection script included by every server-side
file (`database/db.php`):

```php
$server   = "localhost";
$username = "root";
$password = "";
$dbname   = "weblogr";

$con = mysqli_connect($server, $username, $password, $dbname);

if (!$con) {
    die("Connection failed due to " . mysqli_connect_error());
}
```

Centralising connection establishment means a change of credentials or host requires a
single edit. Three deficiencies in this implementation are noted for Chapter 8:
credentials are embedded in source rather than externalised to configuration; the
connection uses the `root` account rather than a least-privilege account restricted to
the application's schema; and the failure handler emits the driver's diagnostic to the
response, disclosing internal detail (§2.3.1 A05). The connection character set is also
not set explicitly, whose consequence is discussed in Section 5.7.2.

### 5.2.3 Request Handling Pattern

Every protected script opens with the guard described in Section 4.5:

```php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: ../registration/login.php");
    exit;
}
include '../database/db.php';
```

Establishing the session precedes any output, since PHP transmits session cookies as
headers. Scripts that emit markup before calling `session_start()` produce a
"headers already sent" warning; instances of this are recorded in Chapter 6.

### 5.2.4 Third-Party Components

| Component | Version | Purpose | Licence |
|---|---|---|---|
| PHPMailer | 6.9 | Authenticated SMTP delivery | LGPL-2.1 |
| Font Awesome | 5.15.2 | Interface iconography | Font Awesome Free |

PHPMailer is installed through Composer. The repository additionally contains a
legacy PHPMailer distribution under `registration/smtp/`, retained from initial
development before Composer was adopted; the mail scripts reference this legacy copy
while `signup.php` loads the Composer autoloader. Maintaining two copies of one library
is a dependency management fault recorded in Section 8.3.6.

## 5.3 Authentication Subsystem

### 5.3.1 Registration and Password Storage (FR-01, FR-03)

`signup.php` checks availability, then hashes and stores the credential:

```php
$select = "SELECT username FROM users WHERE username = ? AND is_verified = 1";
$statement = $con->prepare($select);
$statement->bind_param("s", $username);
$statement->execute();
$result = $statement->get_result();
if ($result->num_rows > 0) { $username_already_exist = true; }

// ... equivalent check on email ...

$otp = mt_rand(100000, 999999);
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO `users` (`fullname`,`username`,`email`,`password`,`otp`,`date`,`is_verified`)
        VALUES (?, ?, ?, ?, ?, current_timestamp(), 0)";
$statement = $con->prepare($sql);
$statement->bind_param("sssss", $fullname, $username, $email, $password_hash, $otp);
$statement->execute();
```

Two techniques satisfy requirements from Section 4.2.2.

**Parameterised statements (NFR-02).** The query text is transmitted to the server
separately from the values, which are then bound by type. Because the parser has already
processed the statement structure before the data arrives, no input can alter that
structure. This is the definitive mitigation for SQL injection identified in Section
2.3.1.

**Adaptive password hashing (NFR-01).** `password_hash()` with `PASSWORD_DEFAULT`
applies bcrypt with a randomly generated per-password salt and a configurable work
factor. The salt is embedded in the resulting 60-character string, so no separate salt
column is needed, and the `VARCHAR(255)` column allows migration to a longer algorithm
output without schema change. Verification uses `password_verify()`, which extracts the
salt and cost from the stored hash and performs a timing-safe comparison.

**Deficiencies.** Uniqueness is checked only against *verified* accounts, and no `UNIQUE`
constraint exists on `users.username` or `users.email`. Repeated registration attempts
against an unverified address therefore create duplicate rows. Server-side validation of
password length and of the confirmation match is also absent, being performed only in
client-side JavaScript. Chapter 6 records the resulting defects.

### 5.3.2 Email Verification (FR-02)

An OTP is generated at registration and delivered by `mail.php` through PHPMailer:

```php
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->SMTPAuth  = true;
$mail->SMTPSecure = 'tls';
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;
$mail->Username = "[account]";
$mail->Password = "[application password]";
$mail->setFrom("[account]", "Weblogr");
$mail->Subject = $subject;
$mail->Body    = $message;
$mail->addAddress($to);
```

Authenticated SMTP over TLS on port 587 is used rather than PHP's `mail()`, which offers
no authentication and whose output is widely filtered as spam.

`otp_verification.php` collects six digits from six single-character inputs, concatenates
them, and compares against the stored value:

```php
$entered_otp = $digit1 . $digit2 . $digit3 . $digit4 . $digit5 . $digit6;

$select = ($reset == TRUE)
    ? "SELECT otp FROM users WHERE email = ? AND is_verified = 1"
    : "SELECT otp FROM users WHERE email = ? AND is_verified = 0";

$statement = $con->prepare($select);
$statement->bind_param("s", $email);
$statement->execute();
// ... on match:
$update = "UPDATE users SET is_verified = 1 WHERE email = ?";
```

The same script serves both registration and recovery, discriminated by a `reset` flag.
Reuse avoids duplicating the verification interface, at the cost of a control-flow
dependency on a request parameter.

Section 2.3.2 identifies four properties an OTP mechanism requires. This implementation
satisfies none of them fully: `mt_rand()` is not a cryptographically secure generator
and should be `random_int()`; no expiry is stored or enforced; no attempt limit exists,
leaving a six-digit code exhaustible under automated attack; and the target email is read
from the query string rather than bound to the session that requested the code. The
invalidation step (`UPDATE users SET otp = NULL`) additionally targets a `NOT NULL`
column and does not achieve invalidation. These are assessed in Section 6.6.2.

### 5.3.3 Login and Session Establishment (FR-04)

```php
$select = "SELECT username, password, is_verified, user_type, user_id
           FROM users WHERE username = ? AND user_type = ?";
$statement = $con->prepare($select);
$statement->bind_param("ss", $username, $user_type);
$statement->execute();
$result = $statement->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            if ($row['is_verified']) {
                $_SESSION["username"] = $username;
                $_SESSION["user_id"]  = $row['user_id'];
                header("Location: ../posts/index.php");
                exit;
            } else { $account_not_verified = true; }
        } else { $incorrect_password = true; }
    }
}
```

The credential is verified with `password_verify()`, and only `username` and `user_id`
are placed in the session — the password hash is never held in session state.

**Deficiencies.** `session_regenerate_id()` is not called on authentication, leaving the
session identifier unchanged across the privilege transition and permitting session
fixation (§2.3.1 A07). Session cookie flags (`HttpOnly`, `Secure`, `SameSite`) are not
configured. `user_type` is supplied by a form control and incorporated into the lookup;
although this does not permit escalation — a non-administrator selecting 'Admin' matches
no row and authentication fails — accepting a security-relevant attribute from the client
is a design fault. The role is also not stored in the session, with consequences
described in Section 5.6.2. Finally, several status flags in this script are assigned but
never read, and two are tested while never being assigned; Chapter 6 records the
resulting defect.

### 5.3.4 Password Recovery (FR-05)

`forgot_password.php` issues a fresh OTP to a registered address:

```php
$otp = mt_rand(100000, 999999);
$sql = "UPDATE users SET `otp` = ? WHERE email = ?";
$statement = $con->prepare($sql);
$statement->bind_param("ss", $otp, $email);
$statement->execute();
$reset = TRUE;
include 'pass_mail.php';
```

Following verification, `reset_password.php` sets the new credential. **The delivered
implementation of this script does not resolve the target account from the verification
step**, and consequently does not satisfy FR-05. The defect is recorded as D-01 in Table
6.3 and its remediation specified in Section 8.4. The intended mechanism is that
`otp_verification.php`, on successful verification, writes the verified address to the
session, and that `reset_password.php` reads it from there, requires its presence, and
clears it after use — so that the reset is bound to a completed verification and cannot
be invoked directly.

### 5.3.5 Session Termination (FR-06)

```php
session_start();
$_SESSION = array();
session_destroy();
header("Location: ../index.html");
exit;
```

Both the session array and the server-side session record are cleared. The session
cookie itself is not expired, which a complete implementation would do with
`setcookie()` against the parameters returned by `session_get_cookie_params()`.

## 5.4 Content Management Subsystem

### 5.4.1 Post Creation and the Draft Workflow (FR-07, FR-08, FR-09)

`save_post.php` handles publication and drafting from one submission, discriminated by a
checkbox:

```php
$is_draft = isset($_POST["draft"]);
$title       = $_POST["title"];
$description = $_POST["description"];
$category    = $_POST["category"];

if ($is_draft) {
    $sql_insert_draft = "INSERT INTO draft_posts
        (`title`,`created_date`,`image`,`description`,`category`,`user_id`)
        VALUES ('$title', NOW(), '$filename', '$description', '$category', '$user_id')";
    $con->query($sql_insert_draft);
} else {
    $sql_insert_blogs = "INSERT INTO blogs
        (`title`,`created_date`,`image`,`description`,`category`,`user_id`)
        VALUES ('$title', NOW(), '$filename', '$description', '$category', '$user_id')";
    $con->query($sql_insert_blogs);
    // followed by notification fan-out — §5.5.3
}
```

Publishing a draft inserts into `blogs` and deletes from `draft_posts`, the two-relation
consequence of the design decision recorded in Section 4.9.1.

**Deficiency.** These statements interpolate request data directly into SQL rather than
using the parameterised form applied in the authentication subsystem (§5.3.1), and are
therefore injectable (NFR-02 unmet). The inconsistency between the two subsystems is
analysed in Section 8.2.2 — it is the clearest single illustration of the cost identified
in Section 3.4.4, that framework-free development replaces correctness by construction
with correctness by discipline.

### 5.4.2 Feed Retrieval, Filtering and Sorting (FR-13, FR-14, FR-15)

`posts/index.php` composes a query incrementally from the submitted filter criteria:

```php
$sql = "SELECT b.blog_id, b.title, b.created_date, b.image, b.description,
               b.likes, b.user_id, u.username
        FROM blogs b JOIN users u ON b.user_id = u.user_id";

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selected_category = mysqli_real_escape_string($con, $_GET['category']);
    $sql .= " WHERE b.category = '$selected_category'";
}
if (isset($_GET['username']) && !empty($_GET['username'])) {
    $selected_user = mysqli_real_escape_string($con, $_GET['username']);
    $sql .= " AND u.username = '$selected_user'";
}
if (isset($_GET['popularity']) && !empty($_GET['popularity'])) {
    switch ($popularity_option) {
        case 'popular':   $sql .= " ORDER BY b.likes DESC, b.created_date DESC"; break;
        case 'unpopular': $sql .= " ORDER BY b.likes ASC,  b.created_date DESC"; break;
    }
} elseif (isset($_GET['sort']) && !empty($_GET['sort'])) {
    // ORDER BY b.created_date ASC | DESC
} else {
    $sql .= " ORDER BY b.created_date DESC";
}
```

The inner join to `users` resolves the author's display name in a single round trip,
avoiding a per-post lookup. Ordering defaults to reverse chronological, satisfying FR-13
when no criteria are supplied. Popularity takes precedence over date ordering when both
are supplied, resolving the conflict deterministically.

**Deficiencies.** Escaping is applied rather than parameterisation, contrary to NFR-02.
Additionally the clause-assembly logic emits `WHERE` only in the category branch, so an
author filter applied alone appends `AND` to the join condition rather than opening a
`WHERE` clause. This produces correct results for an inner join but does so incidentally
rather than by design, and it is fragile under modification. Recorded as D-11.

### 5.4.3 Image Upload

```php
if (isset($_FILES['uploadimage'])) {
    $filename = $_FILES['uploadimage']['name'];
    $tempname = $_FILES['uploadimage']['tmp_name'];
    move_uploaded_file($tempname, "../images/" . $filename);
}
```

Post images are written to `images/`, profile images to `uploads/`, and the filename is
persisted in the database while the file itself resides on disk — avoiding storage of
binary data in the relational store and allowing the web server to serve images directly.

**Deficiencies.** NFR-06 requires an extension allow-list, content-based type
verification, a server-generated filename and a size limit. None is implemented. The
client-supplied filename is used verbatim, which permits an executable file to be placed
in the document root, permits traversal sequences in the filename, and allows one user's
upload to overwrite another's file of the same name. This is assessed in Section 6.6.3
and remediation is specified in Section 8.4.

### 5.4.4 Editing and Deletion (FR-10, FR-11)

`edit_post.php` renders a form populated from the stored record; `update_post.php`
applies the change, branching on whether a replacement image was supplied:

```php
if (isset($_FILES['uploadimage']) && $_FILES['uploadimage']['error'] === UPLOAD_ERR_OK) {
    // ... store new image ...
    $sql = "UPDATE `blogs` SET `title`='$title', `created_date`=NOW(), `image`='$filename',
            `description`='$description', `category`='$category' WHERE `blog_id`='$blog_id'";
} else {
    $sql = "UPDATE `blogs` SET `title`='$title', `created_date`=NOW(),
            `description`='$description', `category`='$category' WHERE `blog_id`='$blog_id'";
}
```

Testing `UPLOAD_ERR_OK` distinguishes a genuine upload from an unset file control, so
that editing without replacing the image preserves the existing reference.

Deletion removes dependent comments before the post itself, respecting the foreign key
constraint on `comments.blog_id`:

```php
$sql = "DELETE FROM comments WHERE blog_id = $blog_id";
if ($con->query($sql) === TRUE) {
    $sql = "DELETE FROM blogs WHERE blog_id= $blog_id";
    $con->query($sql);
}
```

**Deficiencies.** Three, all consequential. Neither script verifies that the
authenticated user authored the record being modified, contrary to NFR-04 —
authorisation is inferred from the interface not offering a link, which Section 2.3.1
identifies as precisely the reasoning that produces insecure direct object references.
`delete_post.php` additionally omits the session guard entirely. Both interpolate the
identifier directly into SQL. And setting `created_date = NOW()` on update conflates
creation with modification: the schema provides no `updated_at` attribute, and the
column additionally carries `ON UPDATE CURRENT_TIMESTAMP`, so publication chronology is
not preserved across edits — undermining FR-15's date ordering. Recorded as D-02, D-03
and D-08.

The two-statement deletion is also not transactional. Should the second statement fail,
the comments are already removed. Wrapping both in a transaction would make the
operation atomic.

## 5.5 Social Engagement Subsystem

### 5.5.1 Comments (FR-16)

`save_comment.php` resolves the post's author before inserting, so that the notification
can be addressed:

```php
$select_user_id = "SELECT user_id FROM blogs WHERE blog_id = ?";
$select_stmt = $con->prepare($select_user_id);
$select_stmt->bind_param("i", $blog_id);
$select_stmt->execute();
$row = $select_stmt->get_result()->fetch_assoc();
$user_id = $row['user_id'];

$save_comment = "INSERT INTO comments (blog_id, commenter_id, comment_text, comment_date)
                 VALUES ('$blog_id', '$commenter_id', '$comment_text', NOW())";
$con->query($save_comment);

$notification_content = "$username commented on your post (Id: $blog_id) <br> '$comment_text'";
$stmt = $con->prepare("INSERT INTO notifications (content, user_id) VALUES (?, ?)");
$stmt->bind_param("si", $notification_content, $user_id);
$stmt->execute();
```

Empty comments are rejected after `trim()`, preventing whitespace-only submissions.

### 5.5.2 Likes (FR-17, FR-18)

Likes increment the materialised counter described in Section 4.9.2:

```php
$sql = "UPDATE blogs SET likes = likes + 1 WHERE blog_id = $blog_id";
```

Performing the increment as a relative update within a single statement rather than
reading, incrementing and writing back avoids a lost-update race between concurrent
requests. This is the correct technique for the design chosen; the limitation is the
design itself, since without an association relation the operation is unbounded and
irreversible (§4.9.2).

`posts/index.js` invokes the endpoint asynchronously:

```js
function likeBlog(blog_id) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "../comments/likes.php?blog_id=" + blog_id, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if (xhr.responseText === "success") { /* update counter */ }
        }
    };
    xhr.send();
}
```

The intent was to update the displayed count without a page reload (NFR-07). The endpoint
responds with a redirect rather than the expected token, so the update branch is never
entered; the defect is recorded as D-09. The endpoint is also invoked by GET, contrary to
NFR-05.

### 5.5.3 Following and Notification Fan-Out (FR-19, FR-20)

`follow.php` checks for an existing edge before inserting, then notifies:

```php
$check_follow = "SELECT * FROM followers WHERE blogger_id = ? AND follower_id = ?";
// ... if no row exists:
$insert_follow = "INSERT INTO followers (blogger_id, follower_id) VALUES (?, ?)";
$stmt_follow = $con->prepare($insert_follow);
$stmt_follow->bind_param("ii", $user_id, $follower_id);
```

Fan-out on publication retrieves the author's followers and inserts one notification per
follower, preparing the statement once and re-binding within the loop:

```php
$stmt_notification = $con->prepare("INSERT INTO notifications (content, user_id) VALUES (?, ?)");

$stmt_get_followers = $con->prepare("SELECT follower_id FROM followers WHERE blogger_id = ?");
$stmt_get_followers->bind_param("i", $user_id);
$stmt_get_followers->execute();
$result_get_followers = $stmt_get_followers->get_result();

while ($follower_row = $result_get_followers->fetch_assoc()) {
    $follower_user_id = $follower_row['follower_id'];
    $stmt_notification->bind_param("si", $notification_content, $follower_user_id);
    $stmt_notification->execute();
}
```

Preparing once outside the loop means the statement is parsed and planned a single time
regardless of follower count — the correct pattern for repeated execution.

**Deficiencies.** Duplicate follow edges are prevented in application code rather than by
a composite unique constraint, leaving a check-then-act race between concurrent requests.
No unfollow operation exists. The fan-out executes one round trip per follower inside the
request; at the scale assumed in Section 1.4.3 this is acceptable, but it does not scale,
and Section 8.3.5 discusses the alternatives — a multi-row insert, or a fan-out-on-read
model.

### 5.5.4 Notification Storage

Notifications are stored as pre-rendered strings:

```php
$notification_content = "$username likes your comment <br> '$comment_text'";
```

This makes retrieval a single unqualified select with no joins. The cost is significant
and is discussed in Section 8.3.1: the message embeds a display name captured at the time
of the event, so it does not track subsequent changes; the relation carries no read
state, timestamp or type, so notifications cannot be marked read, ordered chronologically
or filtered; and because the stored value contains markup, it cannot be escaped on output
without breaking the intended rendering — coupling a presentation decision to a security
control, as Section 5.8.2 explains.

## 5.6 Moderation Subsystem

### 5.6.1 Content Reporting (FR-22)

```php
$insert = "INSERT INTO reports (`blog_id`, `blogger_id`, `reporter_id`, `content`)
           VALUES (?, ?, ?, ?)";
$stmt_report = $con->prepare($insert);
$stmt_report->bind_param("iiis", $blog_id, $blogger_id, $reporter_id, $content);
```

Recording the reporter's identity alongside the report supports later accountability. The
subject author is also notified. Notifying the reported party of the report's full text
was intended to provide transparency, but it discloses the complaint to its subject and
undermines the anonymity the wording ("Someone reported your post") implies; Section
8.3.4 discusses this.

### 5.6.2 Administrative Access (FR-23)

Administrative controls are presented conditionally in the shared navigation:

```php
$username = $_SESSION["username"];
if (isset($username) && $username == 'admin') {
    $manage_posts   = '<li><a href="../posts/manage_content.php">...</a></li>';
    $manage_reports = '<li><a href="../posts/reports.php">...</a></li>';
} else {
    $manage_posts = "";
    $manage_reports = "";
}
```

**Deficiencies.** Two, both significant. First, the privilege test compares the username
against a literal rather than consulting `users.user_type`, so the schema's role model is
not used at runtime — a consequence of the role not being stored in the session at login
(§5.3.3). Second, and more seriously, this test governs only the *display* of the
navigation links; `manage_content.php` and `reports.php` themselves perform no role
check, so either is reachable by direct URL entry by any authenticated user. Hiding a
control is not an access control, and NFR-04 is unmet. Recorded as D-04.

## 5.7 Presentation Layer

### 5.7.1 Shared Navigation

`sidebar.php` is included by every authenticated view, providing the consistent
navigation specified in Section 4.8.1 from a single source. The file emits a complete
HTML document rather than a fragment, so pages including it contain nested document
structures and do not validate; this is recorded as D-10 and its remediation — reducing
the file to a `<nav>` element — specified in Section 8.4.

### 5.7.2 Character Encoding

Pages declare UTF-8 and PHPMailer is configured for UTF-8, but the database tables use
`latin1_swedish_ci` and the connection character set is not set explicitly in `db.php`
(§5.2.2). Content outside the Latin-1 repertoire is therefore not stored faithfully. The
correction is to convert the schema to `utf8mb4_unicode_ci` and call
`mysqli_set_charset($con, 'utf8mb4')` on connection. Recorded as D-12.

### 5.7.3 Client-Side Behaviour

JavaScript provides three functions: form validation before submission, confirmation
dialogues on destructive actions (NFR-08), and the asynchronous like request (§5.5.2).
The OTP form advances focus between digit fields automatically as each is completed,
reducing the interaction cost of entering a six-digit code — an application of the error
prevention heuristic (§2.4.1).

## 5.8 Security Controls: Implemented and Absent

This section consolidates the security position, stated against the requirements in
Table 4.2. A candid account is given because Chapter 6 tests these requirements and
Chapter 8 analyses the result; an implementation chapter that asserted security not
present in the code would be contradicted by its own test results.

### 5.8.1 Controls Implemented

| Control | Requirement | Implementation |
|---|---|---|
| Adaptive password hashing | NFR-01 | `password_hash()` / `password_verify()` with bcrypt (§5.3.1) |
| Parameterised statements | NFR-02 | Applied in authentication, follow, notification and report paths |
| Session-based access control | NFR-04 (partial) | Guard present in 20 of 28 scripts (§4.5) |
| Authenticated transport for email | — | SMTP over TLS (§5.3.2) |
| Destructive action confirmation | NFR-08 | Client-side confirmation dialogues (§5.7.3) |
| Race-free counter increment | — | Relative `UPDATE` (§5.5.2) |

### 5.8.2 Controls Specified but Not Implemented

| Requirement | Status | Consequence |
|---|---|---|
| NFR-02 — parameterisation throughout | **Partial.** Content-management and comment paths interpolate input | SQL injection reachable via post, comment, like and delete endpoints |
| NFR-03 — output encoding | **Absent.** No escaping is applied at any output point | Stored XSS in post titles, bodies, comments and notifications |
| NFR-04 — authorisation on every request | **Partial.** Session presence is checked in most scripts; ownership is checked nowhere | Any authenticated user may edit or delete any post; administrative views reachable directly |
| NFR-05 — CSRF tokens; POST for state change | **Absent.** No tokens; like, follow and delete use GET | State-changing requests forgeable; GET endpoints reachable by prefetchers and crawlers |
| NFR-06 — upload validation | **Absent** | Executable content may be written to the document root |
| NFR-11 — usable at 768 px and above | **Partial.** Three `@media` rules in total | Layout degrades below 768 px and fails at handset widths |
| NFR-12 — WCAG Level A | **Partial.** Icon-only controls lack text alternatives | Assistive technology cannot convey control purpose |

The distribution here is the empirical finding anticipated in Sections 1.5 and 3.4.4.
Controls that are *localised* — hashing, applied at two points; parameterisation within a
single subsystem — were implemented correctly. Controls that are *cross-cutting* —
output encoding at every render, authorisation on every request, tokens on every form —
were implemented inconsistently or not at all. These are precisely the controls a
framework applies by default through templating, middleware and form helpers. Section
8.2 develops this argument, which is the project's principal transferable conclusion.
