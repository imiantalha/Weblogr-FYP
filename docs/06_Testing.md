# Chapter 6 — Testing and Verification

> **[[ACTION: Re-execute before submission]]** The outcomes recorded in Tables 6.2, 6.5
> and 6.6 correspond to the state of the codebase at the point of writing. Before
> submitting, re-run every case against your deployed instance and correct any row whose
> result differs. If you remediate defects (Section 8.4), re-run the affected cases and
> record both the original and post-fix outcome — a regression column demonstrating that
> fixes were verified is worth more marks than a suite that never failed.

## 6.1 Introduction

This chapter reports the verification of Weblogr against the requirements specified in
Chapter 4, discharging objective O5. Section 6.2 states the strategy and its limits.
Sections 6.3 to 6.4 present the functional test suite and its results. Section 6.5
reports performance measurement against NFR-09 and NFR-10. Section 6.6 presents a
security assessment against the OWASP Top 10 (2021) categories identified in Section
2.3.1. Section 6.7 gives the consolidated defect register, and Section 6.8 states what
the testing establishes and what it does not.

## 6.2 Test Strategy

### 6.2.1 Levels Applied

| Level | Applied | Justification |
|---|---|---|
| Unit | **No** | Direct consequence of §3.4.4. Business logic is embedded in request-handling scripts that emit output and read superglobals; it is not addressable by a test harness without invoking the full request cycle. |
| Integration | Implicitly | Exercised through system tests, since scripts integrate with the database on every request. Not tested in isolation. |
| System | **Yes** | Primary level. Black-box execution of each functional requirement through the interface. |
| Security | **Yes** | Structured assessment against OWASP Top 10 categories (§6.6). |
| Performance | **Yes** | Response time measurement under a seeded dataset (§6.5). |
| Acceptance | **Yes** | User evaluation, reported separately in Chapter 7. |

The absence of unit testing is the most significant limitation of this strategy and it
was not an oversight. It was predicted in Section 3.6 as a consequence of the
architectural decision taken in Section 3.4, and it is the reason the defects in Section
6.7 were found by inspection and manual execution rather than by an automated suite.
Section 8.2.1 analyses this.

### 6.2.2 Techniques

Black-box techniques were applied to derive cases from the specification rather than
from the code:

- **Equivalence partitioning** — inputs divided into classes expected to be handled
  identically, with one representative tested per class. For the OTP field: correct code,
  incorrect code, non-numeric input, empty input.
- **Boundary value analysis** — values at the edges of accepted ranges. For post titles:
  empty, one character, maximum length, maximum + 1.
- **Error guessing** — cases derived from experience of where this class of application
  typically fails: direct URL access to protected resources, manipulation of identifiers
  in query strings, submission of a form twice, use of the browser back button after a
  state change.
- **State transition testing** — for the post lifecycle (Figure 4.9) and the draft-to-post
  transition.

### 6.2.3 Environment

| Element | Configuration |
|---|---|
| Server | Apache 2.4 (XAMPP), PHP 8.2 |
| Database | MariaDB 10.4 |
| Client | Chrome 12x, Firefox 12x, Edge 12x (desktop, 1920×1080) |
| Host | Windows 10, [[ACTION: your CPU and RAM]] |
| Test data | 5 accounts, 50 posts across 7 categories, 120 comments, 30 follow edges |

Testing was performed on a single machine with client and server co-located. The
consequence for the performance figures is stated in Section 6.5.3.

## 6.3 Functional Test Suite

Sixty-one cases were derived from the twenty-three functional requirements of Table 4.1.
Table 6.1 summarises the distribution; the full suite with steps, data and expected
results is in Appendix E.

**Table 6.1 — Test case distribution by module**

| Module | Requirements | Cases | Passed | Failed |
|---|---|---|---|---|
| Registration and verification | FR-01 – FR-03 | 11 | 9 | 2 |
| Authentication and recovery | FR-04 – FR-06 | 10 | 7 | 3 |
| Post authoring and drafts | FR-07 – FR-09 | 12 | 10 | 2 |
| Post editing and deletion | FR-10 – FR-12 | 8 | 5 | 3 |
| Discovery, filtering, sorting | FR-13 – FR-15 | 7 | 6 | 1 |
| Comments and likes | FR-16 – FR-18 | 6 | 4 | 2 |
| Following and notifications | FR-19 – FR-21 | 4 | 4 | 0 |
| Reporting and moderation | FR-22 – FR-23 | 3 | 2 | 1 |
| **Total** | **23** | **61** | **47** | **14** |

A pass rate of 47/61 (77.0%) was obtained on the final iteration.

### 6.3.1 Representative Cases

Table 6.2 presents the cases that exposed defects, since these carry the analytical
weight. The full suite, including passing cases, is in Appendix E.

**Table 6.2 — Cases exposing defects**

| ID | Req. | Description | Expected | Actual | Result |
|---|---|---|---|---|---|
| TC-05 | FR-01 | Register with a username already held by an unverified account | Rejection: username taken | Second account row created | **Fail** (D-13) |
| TC-09 | FR-02 | Submit an OTP 20 minutes after issue | Rejection: code expired | Accepted; account verified | **Fail** (D-14) |
| TC-11 | FR-02 | Submit 50 sequential incorrect OTPs | Lockout after n attempts | All 50 accepted for evaluation; no limit | **Fail** (D-15) |
| TC-14 | FR-04 | Compare session ID before and after login | Identifier regenerated | Identifier unchanged | **Fail** (D-16) |
| TC-19 | FR-05 | Complete recovery for account B; observe which account's credential changes | B's password updated | Update does not target the verified account | **Fail** (D-01) |
| TC-20 | FR-05 | Request `reset_password.php` directly without prior verification | Redirect to login | Page served and submission processed | **Fail** (D-01) |
| TC-27 | FR-08 | Save a second draft, then a third | Three independent drafts | Later save overwrites all existing draft rows | **Fail** (D-06) |
| TC-31 | FR-07 | Upload `test.php` as a post image | Rejection: file type not permitted | File written to `images/` and served | **Fail** (D-05) |
| TC-35 | FR-10 | Signed in as A, request `edit_post.php?blog_id=<B's post>` | Redirect or 403 | B's post rendered in the editor and updated on submit | **Fail** (D-02) |
| TC-36 | FR-11 | Signed in as A, request `delete_post.php?blog_id=<B's post>` | Redirect or 403 | B's post and its comments deleted | **Fail** (D-02) |
| TC-37 | FR-11 | Request `delete_post.php?blog_id=1` while signed out | Redirect to login | Deletion performed | **Fail** (D-03) |
| TC-39 | FR-15 | Edit an old post; observe its position in the date-ordered feed | Position unchanged | Post moves to top of feed | **Fail** (D-08) |
| TC-45 | FR-17 | Like a post; observe the displayed count without reloading | Count increments in place | Count unchanged until manual reload | **Fail** (D-09) |
| TC-52 | FR-21 | View profile of a user following three accounts | Three accounts listed | Own username listed | **Fail** (D-07) |
| TC-58 | FR-23 | Request `manage_content.php` as a non-administrator | Redirect or 403 | Administrative view served | **Fail** (D-04) |

### 6.3.2 Requirement Traceability

Table 6.3 maps every functional requirement to its cases and states its verification
status. Traceability establishes that no requirement was left untested and, equally, that
no test exercises an unspecified behaviour.

**Table 6.3 — Requirement traceability matrix**

| Req. | Description | Cases | Status |
|---|---|---|---|
| FR-01 | Account registration | TC-01 – TC-06 | Partial (TC-05) |
| FR-02 | Email verification by OTP | TC-07 – TC-11 | Partial (TC-09, TC-11) |
| FR-03 | Password hashing | TC-12 | Verified |
| FR-04 | Credential login | TC-13 – TC-17 | Partial (TC-14) |
| FR-05 | Password recovery | TC-18 – TC-21 | **Not met** (TC-19, TC-20) |
| FR-06 | Logout | TC-22 | Verified |
| FR-07 | Create post | TC-23 – TC-26, TC-31 | Partial (TC-31) |
| FR-08 | Save and manage drafts | TC-27 – TC-29 | Partial (TC-27) |
| FR-09 | Publish a draft | TC-30, TC-32 – TC-34 | Verified |
| FR-10 | Edit own post | TC-35, TC-38 | Partial (TC-35) |
| FR-11 | Delete own post | TC-36, TC-37 | **Not met** |
| FR-12 | List own posts | TC-40 – TC-42 | Verified |
| FR-13 | Reverse-chronological feed | TC-43 | Verified |
| FR-14 | Filter by category and author | TC-44, TC-46, TC-47 | Verified |
| FR-15 | Sort by date and popularity | TC-39, TC-48, TC-49 | Partial (TC-39) |
| FR-16 | Comment on a post | TC-50, TC-51 | Verified |
| FR-17 | Like a post | TC-45, TC-53 | Partial (TC-45) |
| FR-18 | Like a comment | TC-54 | Partial (D-17) |
| FR-19 | Follow an author | TC-55 | Verified |
| FR-20 | Notification feed | TC-56 | Verified |
| FR-21 | View and edit profile | TC-52, TC-57 | Partial (TC-52) |
| FR-22 | Report a post | TC-59 | Verified |
| FR-23 | Administrative moderation | TC-58, TC-60, TC-61 | Partial (TC-58) |

Nineteen of twenty-three requirements are verified fully or in part; two (FR-05, FR-11)
are not met in their specified form. Section 8.2 assesses what this means for objective
O4.

## 6.4 Compatibility and Interface Testing

Rendering and interaction were checked across three browsers and three viewport widths.

**Table 6.4 — Compatibility results**

| Environment | Layout | Interaction | Notes |
|---|---|---|---|
| Chrome, 1920 px | Correct | Correct | Reference environment |
| Firefox, 1920 px | Correct | Correct | — |
| Edge, 1920 px | Correct | Correct | — |
| Chrome, 1024 px | Correct | Correct | Sidebar retains fixed width |
| Chrome, 768 px | Degraded | Correct | Content column compressed; horizontal scrolling on wide tables |
| Chrome, 375 px | **Failed** | Partial | Fixed-width sidebar occupies most of the viewport; feed unusable |

The system carries three `@media` rules in total. NFR-11 (usable at 768 px and above) is
therefore met only at the upper end of its stated range, and the system is not usable at
handset widths. NFR-13 (browser compatibility) is met. The absence of handset support is
a substantive limitation for a publishing platform, since a large proportion of blog
reading occurs on mobile devices, and it is recorded in Section 8.3.

## 6.5 Performance Measurement

### 6.5.1 Method

Server-side execution time was measured by instrumenting each page with
`microtime(true)` at entry and exit. Each measurement is the arithmetic mean of ten
consecutive requests after two discarded warm-up requests, taken against the seeded
dataset of Section 6.2.3.

### 6.5.2 Results

**Table 6.5 — Mean server-side response time (ms)**

| Operation | Records involved | Mean | Std. dev. | NFR-09 (≤ 2000 ms) |
|---|---|---|---|---|
| Feed, unfiltered | 50 posts | [[ACTION: measure]] | | |
| Feed, category filter | ~8 posts | [[ACTION]] | | |
| Feed, popularity sort | 50 posts | [[ACTION]] | | |
| Post creation, no image | 1 insert + fan-out | [[ACTION]] | | |
| Post creation, 1 MB image | 1 insert + file write | [[ACTION]] | | |
| Login | 1 select + bcrypt verify | [[ACTION]] | | |
| Notification feed | ~40 rows | [[ACTION]] | | |
| Profile with aggregates | 4 queries | [[ACTION]] | | |

> **[[ACTION]]** Insert the timing harness shown in Appendix F into each measured script,
> run ten iterations per row, and record the mean and standard deviation. Report the
> figures whatever they are — a chapter reporting measured times that comfortably meet
> the threshold is a valid result; a chapter reporting a threshold breach with a
> diagnosis is a stronger one.

### 6.5.3 Analysis and Threats to Validity

Three properties of the measurement bound what may be concluded from it.

First, client and server were co-located, so no network latency is included. Figures
represent server-side processing only and understate real-world response time.

Second, the dataset is small. The feed query performs a full scan of `blogs` joined to
`users`; at fifty rows this is trivially fast, and the absence of indexes on `category`,
`created_date` and `likes` (§4.6.4) has no measurable effect. The measurement therefore
does not test what it appears to test, and results cannot be extrapolated. Section 8.3.5
discusses the expected behaviour at larger scale.

Third, no concurrency was applied. NFR-10 specifies acceptable behaviour under
concurrent load; single-threaded sequential measurement does not verify it. **NFR-10 is
therefore unverified**, and stating so is more defensible than presenting sequential
figures as though they addressed it. Remediation would require a load generator such as
Apache JMeter, driving a realistic request mix at increasing concurrency until response
time degrades.

## 6.6 Security Assessment

The assessment covers the OWASP Top 10 (2021) categories identified as relevant in
Section 2.3.1. Testing was performed on a local instance, against accounts created for
the purpose, with no third-party system involved.

**Table 6.6 — Assessment against OWASP Top 10 (2021)**

| Category | Assessment | Evidence |
|---|---|---|
| A01 Broken Access Control | **Present** | §6.6.1 |
| A02 Cryptographic Failures | **Partial** | Passwords hashed with bcrypt (NFR-01 met). Database and SMTP credentials stored in source; no HTTPS enforcement |
| A03 Injection | **Present** | Parameterisation applied in authentication paths; interpolation in content-management paths (§5.4.1). SQL injection reachable via `blog_id`, `title`, `description`, `comment_text` |
| A03 Cross-Site Scripting | **Present** | No output encoding applied anywhere (§6.6.4) |
| A05 Security Misconfiguration | **Present** | Database `root` account with empty password; driver errors echoed to response; upload directories within document root and not restricted from execution |
| A07 Authentication Failures | **Partial** | Hashing correct; no session regeneration, no cookie flags, no rate limiting on login or OTP (§6.6.2) |
| CSRF (2013 A08; now under A01) | **Present** | No synchronizer tokens; state-changing operations exposed over GET (§6.6.3) |

### 6.6.1 Authorisation Audit

Every server-side script was inspected for the session guard of Section 4.5 and for an
ownership or role check.

**Table 6.7 — Authorisation control coverage**

| Control | Scripts requiring it | Scripts implementing it |
|---|---|---|
| Session guard | 28 | 20 |
| Ownership verification | 6 (edit, update, delete post/draft) | 0 |
| Role verification | 3 (`manage_content`, `reports`, deletion) | 0 |

The guard is absent from `delete_post.php`, `likes.php`, `like_a_comment.php`,
`save_comment.php`, `follow.php`, `delete_notifications.php`, `report.php` and
`update_post.php`. Ownership verification is implemented in no script: every
edit and delete endpoint acts on the identifier supplied in the request without
establishing that the requester owns the record. This is the textbook insecure direct
object reference of Section 2.3.1, and TC-35, TC-36 and TC-37 demonstrate it.

Role verification is implemented in no script. The administrative privilege test in
`sidebar.php` (§5.6.2) controls only whether links are displayed, and compares a username
against a literal rather than reading `users.user_type`. TC-58 confirms that the
administrative views are reachable by any authenticated user through direct URL entry.

The pattern is diagnostic. Where the required check is *local to one script* — is a
session present — it was implemented in most places. Where the check requires *relating
the request to stored state* — does this user own this record, does this user hold this
role — it was implemented nowhere. Section 8.2 develops this.

### 6.6.2 One-Time Password Assessment

Section 2.3.2 sets out four properties an OTP mechanism requires. The implementation is
assessed against each.

| Property | Implemented | Finding |
|---|---|---|
| Cryptographically secure generation | **No** | `mt_rand()` is a Mersenne Twister, not a CSPRNG. Its internal state is recoverable from observed output, making subsequent codes predictable. `random_int()` is the correct call. |
| Time-limited validity | **No** | No issue timestamp is stored and no expiry is enforced. TC-09 confirms a code remains valid indefinitely. |
| Attempt limiting | **No** | No counter, delay or lockout. A six-digit code has 10⁶ values; at 10 requests/second the space is exhausted in under 28 hours, and the expected time to a hit is half that. Combined with the absence of expiry, this is exploitable. TC-11 confirms it. |
| Binding to the requesting session | **No** | The target email is carried in the query string, not the session. Verification is therefore not bound to the session that requested the code. |

Invalidation on use is attempted (`UPDATE users SET otp = NULL`) but the column is
declared `NOT NULL`, so the statement does not achieve invalidation. The mechanism
satisfies none of the four properties.

### 6.6.3 CSRF and HTTP Method Assessment

No form in the system carries a synchronizer token, and no handler validates one. A
cross-origin page can therefore cause an authenticated user's browser to perform any
state-changing operation.

The exposure is enlarged by method selection. RFC 9110 defines GET as a safe method that
must not carry state-changing semantics. The following endpoints violate this:

| Endpoint | Method | Effect |
|---|---|---|
| `comments/likes.php` | GET | Increments a post's like count |
| `comments/like_a_comment.php` | GET | Increments a comment's like count |
| `posts/follow.php` | GET | Creates a follow relationship |
| `posts/delete_post.php` | GET | Deletes a post and its comments |
| `posts/delete_notifications.php` | GET | Deletes notification records |

An `<img src>` tag on any page an authenticated user visits is sufficient to trigger any
of these. `delete_post.php`, which additionally omits the session guard, requires no
authenticated victim at all. Link prefetchers, browser accelerators and crawlers will
also trigger them without user intent.

### 6.6.4 Cross-Site Scripting Assessment

`<script>alert(1)</script>` was submitted into each user-controlled field and the
resulting page inspected.

| Field | Stored | Rendered as markup | Persistence |
|---|---|---|---|
| Post title | Yes | **Yes** | Every viewer of the feed |
| Post body | Yes | **Yes** | Every viewer of the post |
| Comment text | Yes | **Yes** | Every viewer of the post |
| Profile display name | Yes | **Yes** | Every viewer of the profile and of the author's posts |
| Notification content | Yes | **Yes** | The notified user |
| Report content | Yes | **Yes** | Administrator |

No field escapes on output. Every one is a stored XSS vector: the payload persists in the
database and executes in the browser of every subsequent viewer, in the context of the
application's origin, with access to the session cookie (which carries no `HttpOnly`
flag). Because the administrative report view renders unescaped reporter-supplied
content, a payload can be aimed specifically at the administrator's session.

The notification case is the most instructive. Notification content is *stored as
pre-rendered markup* containing literal `<br>` tags (§5.5.4), so applying
`htmlspecialchars()` at the output point would display those tags as text and break the
feature. The security defect cannot be fixed at the output point alone; it requires the
schema change described in Section 8.4 — storing structured fields and composing the
message at render time. A presentation decision made for convenience has propagated into
a security control, which is a clearer illustration of coupling than any textbook
example.

## 6.7 Defect Register

**Table 6.8 — Consolidated defect register**

| ID | Severity | Location | Description | Status |
|---|---|---|---|---|
| D-01 | Critical | `registration/reset_password.php` | Reset does not resolve the account from the verification step, and the page is reachable without prior verification. FR-05 not met. | Open |
| D-02 | Critical | `posts/edit_post.php`, `update_post.php`, `delete_post.php` | No ownership verification; any authenticated user may edit or delete any post (IDOR) | Open |
| D-03 | Critical | `posts/delete_post.php` | No `session_start()` and no session guard; deletion performable unauthenticated | Open |
| D-04 | Critical | `posts/manage_content.php`, `reports.php` | No role verification; administrative views reachable by any authenticated user | Open |
| D-05 | Critical | `posts/save_post.php`, `registration/edit_profile.php` | No upload validation of extension, content type, size or filename; executable content may be written to the document root | Open |
| D-06 | Critical | `posts/save_post.php` | Draft `UPDATE` issued without a `WHERE` clause; every draft row in the table is overwritten | Open |
| D-07 | Major | `registration/profile.php` | The "following" query joins and filters on the same column, so it returns the viewing user rather than the accounts followed | Open |
| D-08 | Major | `posts/update_post.php` | `created_date` reset to `NOW()` on edit; no `updated_at` attribute exists, so publication chronology is lost | Open |
| D-09 | Major | `comments/likes.php`, `posts/index.js` | Endpoint returns a redirect where the client expects a success token; the like count does not update without reload | Open |
| D-10 | Major | `posts/sidebar.php` | Emits a complete HTML document rather than a fragment; every including page contains nested document structures and does not validate | Open |
| D-11 | Major | `posts/index.php` | Filter clause assembly emits `AND` without a preceding `WHERE` when the author filter is applied alone | Open |
| D-12 | Major | `database/weblogr.sql`, `db.php` | Tables collated `latin1_swedish_ci` while pages declare UTF-8, and the connection charset is unset; non-Latin-1 content is corrupted | Open |
| D-13 | Major | `registration/signup.php` | Uniqueness checked only against verified accounts and not enforced by constraint; duplicate rows creatable | Open |
| D-14 | Major | `registration/otp_verification.php` | No OTP expiry | Open |
| D-15 | Major | `registration/otp_verification.php`, `login.php` | No attempt limiting on OTP verification or login | Open |
| D-16 | Major | `registration/login.php` | `session_regenerate_id()` not called on authentication; session fixation possible | Open |
| D-17 | Major | `comments/comments.php` | Client-side comment validator reads `.value` from a `NodeList` and tests an undeclared identifier; the function throws and validation never runs | Open |
| D-18 | Major | `posts/blog_poster.php` | `fetch_assoc()` called once outside the render loop, discarding the first post of every author profile | Open |
| D-19 | Major | `registration/login.php` | Two status flags are tested but never assigned, so their guard condition is always true; four others are assigned twice | Open |
| D-20 | Minor | `posts/index.php` | `header('Locaton: ...')` — misspelled header name; the redirect is not issued | Open |
| D-21 | Minor | `README.md` | Contains DDL contradicting the delivered schema (misspelled column, wrong type, dropped constraint) | Open |
| D-22 | Minor | `registration/` | Two copies of PHPMailer retained (Composer and legacy) | Open |
| D-23 | Minor | Schema | `blogs.user_id` and all `reports` foreign keys absent; no `ON DELETE` semantics defined | Open |

Twenty-three defects: six critical, thirteen major, four minor. Sixteen were identified
by inspection and seven by execution — a distribution that is itself evidence for the
argument of Section 8.2.1 regarding the cost of an untestable architecture.

### 6.7.1 Note on Credential Exposure

Two source files were found to contain a live SMTP application password in plaintext,
committed to version control. This is not a defect in the running system's logic but an
operational exposure of a real credential. It has been recorded, the credential revoked
at the provider, and the deployment instructions in Appendix C updated to require
externalised configuration. It is reported here rather than silently removed because the
class of mistake — secrets in source, committed to a repository — is one the literature
identifies as endemic (§2.3.1 A05), and a report that conceals its own instance of it
would be less useful than one that documents it.

## 6.8 Discussion

### 6.8.1 What the Testing Establishes

The functional suite establishes that nineteen of twenty-three requirements are satisfied
in whole or in part, that the account lifecycle, authoring, draft, discovery, comment,
follow and notification paths operate as specified under normal use, and that the system
is usable end to end on desktop browsers.

### 6.8.2 What It Does Not Establish

The suite does not establish correctness under concurrency, behaviour at realistic data
volume, or the absence of defects in paths not exercised. With no unit tests, code
coverage is unmeasured and unmeasurable. Absence of evidence of a defect in an untested
path is not evidence of its absence.

### 6.8.3 The Shape of the Defect Distribution

The defects are not uniformly distributed, and their distribution is the most analytically
useful result in this chapter. Grouping them:

- **Defects a framework would have prevented by default** (D-02, D-04, and every finding
  in §6.6.3 and §6.6.4): 4 defects plus 11 injection and XSS vectors. Output escaping,
  CSRF tokens, route-level authorisation middleware and parameterised query builders are
  standard framework provisions.
- **Defects an automated test suite would have caught** (D-06, D-07, D-09, D-11, D-17,
  D-18, D-19, D-20): 8 defects. Each produces an observably wrong result on first
  execution of the affected path.
- **Defects of specification or schema** (D-08, D-12, D-21, D-23): 4 defects.
- **Defects of security knowledge** (D-14, D-15, D-16, D-05): 4 defects, where the
  required control was known from the literature but not implemented.

Fewer than a fifth of the defects arise from not knowing what to do. The large majority
arise from the absence of two things the architectural decision of Section 3.4 excluded:
framework defaults and a testable structure. Section 8.2 makes this the report's
principal conclusion.
