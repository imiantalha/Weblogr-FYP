# Appendices

---

# Appendix A — Use Case Specifications

Use cases UC1 (Register an Account) and UC6 (Publish a Post) are specified in full in
Section 4.3.2. The remaining use cases are specified here.

## UC2 — Verify Email Address

| Field | Detail |
|---|---|
| **Actor** | Unregistered Visitor |
| **Requirement** | FR-02 |
| **Precondition** | An unverified account exists and an OTP has been issued to its email address |
| **Postcondition** | `users.is_verified` set to 1; the OTP is invalidated |

**Main flow**
1. The visitor opens the verification page from the link or prompt following registration.
2. The system presents six single-character inputs.
3. The visitor enters the six digits received by email; focus advances automatically.
4. The visitor submits.
5. The system compares the concatenated value against the stored OTP for the address.
6. On match, the system sets `is_verified = 1` and redirects to the login page.

**Alternate flow A1 — code not received**
- 3a. The visitor requests a new code. The system generates and sends a replacement,
  overwriting the stored value.

**Exception flow E1 — incorrect code**
- 5a. No match. The system reports "Invalid OTP" and re-presents the form.
  *Note: the delivered implementation applies no attempt limit here — see D-15.*

**Exception flow E2 — expired code**
- Not implemented. No expiry is stored or enforced (D-14). A complete implementation would
  reject a code older than the validity window and offer reissue.

## UC3 — Log In

| Field | Detail |
|---|---|
| **Actor** | Registered Author, Administrator |
| **Requirement** | FR-04 |
| **Precondition** | A verified account exists |
| **Postcondition** | A session is established holding `username` and `user_id` |

**Main flow**
1. The actor submits username, password and account type.
2. The system retrieves the matching row.
3. The system verifies the password against the stored hash with `password_verify()`.
4. The system confirms `is_verified = 1`.
5. The system writes the session values and redirects to the feed.

**Exception flows**
- E1 No matching username: "Username does not exist".
- E2 Password mismatch: "Incorrect password".
- E3 Account unverified: "Account not verified"; reissue offered.
- *Note: step 5 of a complete implementation would call `session_regenerate_id(true)` —
  see D-16.*

## UC4 — Recover Password

| Field | Detail |
|---|---|
| **Actor** | Registered Author |
| **Requirement** | FR-05 |
| **Precondition** | A verified account exists with a reachable email address |
| **Postcondition** | The credential for **that account** is replaced |

**Main flow**
1. The actor submits the email address of the account.
2. The system confirms a verified account exists for it.
3. The system generates an OTP, stores it, and sends it to the address.
4. The actor verifies the OTP (UC2 with `reset = TRUE`).
5. The system records the verified address for the session.
6. The actor submits a new password twice.
7. The system confirms the two entries match and that a verified address is recorded.
8. The system hashes and stores the new credential against **the recorded address**, then
   clears the record.

**Exception flows**
- E1 No account for the address: "Email not registered".
- E2 Entries do not match: "Passwords do not match".
- E3 No verified address recorded: redirect to the recovery start.

> **Steps 5, 7 and 8 describe the intended mechanism. The delivered implementation does
> not resolve the target account from the verification step and does not require a recorded
> verification (D-01). FR-05 is not met; remediation is specified in Section 8.4, item 7.**

## UC5 — Log Out

| Field | Detail |
|---|---|
| **Actor** | Registered Author, Administrator |
| **Requirement** | FR-06 |
| **Main flow** | The actor selects Logout. The system clears the session array, destroys the session, and redirects to the landing page. |
| **Note** | A complete implementation also expires the session cookie (§5.3.5). |

## UC7 — Save and Publish a Draft

| Field | Detail |
|---|---|
| **Actor** | Registered Author |
| **Requirements** | FR-08, FR-09 |
| **Precondition** | Authenticated session |
| **Postcondition** | A draft row exists; on publication a `blogs` row exists and the draft row is removed |

**Main flow**
1. The author completes the post form and selects "Save as draft".
2. The system inserts into `draft_posts` with the author's `user_id`.
3. The author later opens the draft list and selects a draft.
4. The system presents the draft in the editor.
5. The author selects "Publish".
6. The system inserts into `blogs`, deletes the draft row, and performs notification
   fan-out (UC10).

**Note.** Step 6 is a delete-and-insert rather than a status change, and the identifier is
not preserved. This is the consequence of the two-relation design recorded in Section
4.9.1. See also D-06.

## UC8 — Edit or Delete a Post

| Field | Detail |
|---|---|
| **Actor** | Registered Author (owner) |
| **Requirements** | FR-10, FR-11 |
| **Precondition** | Authenticated session; **the actor authored the post** |
| **Postcondition** | The post is updated, or the post and its comments are removed |

**Main flow (edit)**
1. The author selects Edit on one of their posts.
2. The system **confirms the authenticated user authored the post**.
3. The system presents the stored values in the editor.
4. The author submits changes, optionally replacing the image.
5. The system updates the row, preserving the existing image reference if no replacement
   was supplied, and sets `updated_at`.

**Main flow (delete)**
1. The author selects Delete.
2. The system requests confirmation.
3. The system **confirms the authenticated user authored the post**.
4. The system deletes the post's comments and then the post, within a transaction.

> **Step 2 (edit) and step 3 (delete) are not implemented (D-02); `delete_post.php`
> additionally lacks any session guard (D-03); the update sets `created_date` rather than
> an `updated_at` attribute, which does not exist (D-08); and the deletion is not
> transactional (§5.4.4). Remediation: Section 8.4, items 2, 3 and 11.**

## UC9 — Browse, Filter and Sort the Feed

| Field | Detail |
|---|---|
| **Actor** | Registered Author, Administrator |
| **Requirements** | FR-13, FR-14, FR-15 |
| **Main flow** | The system presents all published posts in reverse chronological order. The actor optionally selects a category, an author, or an ordering (date ascending/descending, most/least liked). The system re-presents the feed accordingly. |
| **Business rule** | Where both a popularity ordering and a date ordering are supplied, popularity takes precedence, with date as the secondary key. |
| **Note** | No pagination; the full result set is returned (§8.3.5). |

## UC10 — Comment, Like and Follow

| Field | Detail |
|---|---|
| **Actor** | Registered Author |
| **Requirements** | FR-16, FR-17, FR-18, FR-19 |
| **Postcondition** | The interaction is recorded and a notification is created for the affected user |

**Main flow (comment)**
1. The actor enters text on a post and submits.
2. The system rejects empty or whitespace-only input.
3. The system resolves the post's author.
4. The system inserts the comment and inserts a notification addressed to the author.

**Main flow (like)**
1. The actor selects the like control on a post or comment.
2. The system increments the stored counter by a relative update.
3. The system inserts a notification addressed to the owner.
4. The displayed count updates without a page reload.

**Main flow (follow)**
1. The actor selects Follow on an author's profile.
2. The system confirms no existing edge exists.
3. The system inserts the edge and notifies the followed author.

> **Notes.** Step 4 of the like flow does not occur (D-09). Likes are unbounded and
> irreversible, and no unfollow operation exists (§4.9.2, §8.3.1). Duplicate follow
> prevention is by application check rather than constraint (§5.5.3). Client-side comment
> validation is inoperative (D-17); step 2 is enforced server-side only.

## UC11 — Report Content

| Field | Detail |
|---|---|
| **Actor** | Registered Author |
| **Requirement** | FR-22 |
| **Main flow** | The actor selects Report on a post, states a reason, and submits. The system records the report with the post, its author and the reporter, and notifies the author. |
| **Note** | The notification transmits the report's full text to its subject, which conflicts with the anonymity the interface wording implies (§8.3.4). |

## UC12 — Moderate Content

| Field | Detail |
|---|---|
| **Actor** | Administrator |
| **Requirement** | FR-23 |
| **Precondition** | Authenticated session **held by a user whose `user_type` is administrator** |
| **Postcondition** | The selected post is removed |

**Main flow**
1. The administrator opens the reports view.
2. The system **confirms the session holds the administrator role**.
3. The system lists submitted reports with the reported post, its author and the reason.
4. The administrator selects a post for removal.
5. The system removes the post and its dependent records.

> **Step 2 is not implemented (D-04). The role test in `sidebar.php` governs only link
> display and compares a username against a literal rather than reading `users.user_type`.
> Remediation: Section 8.4, item 3.**

---

# Appendix B — Complete Data Dictionary

Attribute types and constraints as delivered in `database/weblogr.sql`. All tables use the
InnoDB engine and, as delivered, the `latin1_swedish_ci` collation (D-12).

## B.1 `users`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `user_id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `fullname` | `varchar(255)` | NOT NULL | Display name |
| `username` | `varchar(255)` | NOT NULL | Login identifier. *No UNIQUE constraint (D-13)* |
| `email` | `varchar(255)` | NOT NULL | Contact and recovery address. *No UNIQUE constraint (D-13)* |
| `password` | `varchar(255)` | NOT NULL | bcrypt hash from `password_hash()` |
| `otp` | `varchar(10)` | NOT NULL | Current one-time password. *`NOT NULL` prevents invalidation by `SET otp = NULL` (§5.3.2)* |
| `is_verified` | `tinyint(1)` | NOT NULL, DEFAULT 0 | 1 once the address is verified |
| `user_type` | `varchar(50)` | DEFAULT 'user' | Role. *Not read at runtime (D-04)* |
| `bio` | `text` | NULL | Profile biography |
| `image` | `varchar(255)` | NULL | Profile photograph filename |
| `date` | `timestamp` | DEFAULT current_timestamp() | Account creation |

*Absent: `otp_expires_at`, `otp_attempts`, `last_login`.*

## B.2 `blogs`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `blog_id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `title` | `varchar(255)` | NOT NULL | Post title |
| `created_date` | `timestamp` | DEFAULT current_timestamp() ON UPDATE current_timestamp() | Publication time. *Reset on edit (D-08)* |
| `image` | `varchar(255)` | NULL | Image filename in `images/` |
| `description` | `text` | NOT NULL | Post body, plain text |
| `category` | `varchar(100)` | NOT NULL | One of seven values. *Not indexed (§8.3.1)* |
| `likes` | `int(11)` | DEFAULT 0 | Materialised counter. *No association relation (§4.9.2)* |
| `user_id` | `int(11)` | NOT NULL | Author. **No foreign key declared (D-23)** |

*Absent: `updated_at`, `status`, indexes on `category`, `created_date`, `likes`.*

## B.3 `draft_posts`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `draft_id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `title` | `varchar(255)` | NOT NULL | Draft title |
| `created_date` | `timestamp` | DEFAULT current_timestamp() | Draft creation |
| `image` | `varchar(255)` | NULL | Image filename |
| `description` | `text` | NOT NULL | Draft body |
| `category` | `varchar(100)` | NOT NULL | Category |
| `user_id` | `int(11)` | FK → `users.user_id` | Owner |

*Structurally duplicates `blogs` (§4.9.1). See also D-06.*

## B.4 `comments`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `comment_id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `blog_id` | `int(11)` | FK → `blogs.blog_id` | Commented post |
| `commenter_id` | `int(11)` | FK → `users.user_id` | Author of the comment |
| `comment_text` | `text` | NOT NULL | Comment body |
| `comment_date` | `timestamp` | DEFAULT current_timestamp() | Submission time |
| `likes` | `int(11)` | DEFAULT 0 | Materialised counter |

*No `ON DELETE` semantics declared; dependent deletion is performed in application code
(§5.4.4).*

## B.5 `followers`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `blogger_id` | `int(11)` | FK → `users.user_id` | The account being followed |
| `follower_id` | `int(11)` | FK → `users.user_id` | The account following |

*No unique constraint on `(blogger_id, follower_id)`; duplicate prevention is by
application check, leaving a check-then-act race (§5.5.3).*

## B.6 `notifications`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `notification_id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `content` | `text` | NOT NULL | **Pre-rendered message containing markup** (§5.5.4) |
| `user_id` | `int(11)` | FK → `users.user_id` | Recipient |

*Absent: `created_at`, `is_read`, `type`, `actor_id`, `target_id`. Consequences in §8.3.1;
restructuring specified in §8.4 item 8.*

## B.7 `reports`

| Attribute | Type | Constraints | Description |
|---|---|---|---|
| `report_id` | `int(11)` | PK, AUTO_INCREMENT | Surrogate key |
| `blog_id` | `int(11)` | NOT NULL | Reported post. **No foreign key (D-23)** |
| `blogger_id` | `int(11)` | NOT NULL | Author of the reported post. **No foreign key** |
| `reporter_id` | `int(11)` | NOT NULL | Submitting user. **No foreign key** |
| `content` | `text` | NOT NULL | Stated reason |

*Absent: `created_at`, `status` (open/actioned/dismissed).*

## B.8 `comment_likes` *(if present in your schema)*

> **[[ACTION]]** Confirm against your `weblogr.sql` whether comment likes are recorded in
> an association relation or only as the `comments.likes` counter. If only the counter
> exists, delete this subsection and note in §8.3.1 that comment likes share the same
> limitation as post likes.

## B.9 Category Domain

Seven values, enforced by the interface's option list rather than by a database constraint:
Technology, Travel, Food, Lifestyle, Education, Health, Business.

> **[[ACTION]]** Confirm these against the `<option>` values in `posts/new_post.php` and
> correct if they differ. An enumerated column or a lookup relation would enforce the
> domain in the database; as delivered, any value can be inserted directly.

---

# Appendix C — Third-Party Components, Attribution and Deployment

## C.1 Components

| Component | Version | Purpose | Licence | Source |
|---|---|---|---|---|
| PHPMailer | 6.9 | Authenticated SMTP delivery | LGPL-2.1 | github.com/PHPMailer/PHPMailer |
| Font Awesome Free | 5.15.2 | Interface iconography | Font Awesome Free (icons CC BY 4.0; code MIT) | fontawesome.com |

All other code in the repository is the author's own work. The repository additionally
retains a legacy PHPMailer distribution under `registration/smtp/`, which should be removed
(§8.3.6).

## C.2 Deployment

**Prerequisites**: PHP 8.2 or later with the `mysqli` and `fileinfo` extensions; MariaDB
10.4 or MySQL 8.0; Apache 2.4 with `mod_rewrite`; Composer.

1. Place the repository in the web server's document root.
2. Create the database and import `database/weblogr.sql`.
3. **Create a dedicated database account** with `SELECT`, `INSERT`, `UPDATE` and `DELETE`
   on the `weblogr` schema only. Do not use `root`.
4. **Create a configuration file** outside version control holding the database and SMTP
   credentials, and add it to `.gitignore`. Credentials must not be committed (§6.7.1).
5. Run `composer install` in `registration/`.
6. Configure the SMTP account and generate an application password at the provider.
7. Ensure `images/` and `uploads/` are writable by the web server process, and configure
   the server not to execute scripts in either directory.

> **[[ACTION]]** Steps 3, 4 and 7 describe the correct deployment. The repository as
> delivered uses the `root` account with an empty password and embeds credentials in
> source. Update `database/db.php` and the mail scripts to read from the configuration file
> before deploying anywhere reachable.

---

# Appendix D — Evaluation Instruments

## D.1 Participant Information Sheet

> **Study title:** Usability evaluation of Weblogr, a blog publishing platform
>
> **Researcher:** [[ACTION: name]] — [[ACTION: institution, department]]
>
> **What is this study about?** I have built a web application for publishing blog posts
> and I am evaluating how easy it is to use. I am evaluating the software, not you.
>
> **What will I be asked to do?** You will use the system on my computer for about 30
> minutes, attempting eight short tasks such as creating an account and publishing a post.
> I will ask you to say aloud what you are thinking as you work. Afterwards you will
> complete a ten-question questionnaire and answer four short questions.
>
> **Is it recorded?** [[ACTION: state whether you record audio. If you do, say where the
> recording is stored and when it is deleted. If you take written notes only, say so.]]
>
> **What data is collected?** Your age band, whether you have a technical background, and
> whether you have blogged before. Your name is not recorded. You are identified in the
> report only as "P1", "P2" and so on. Any quotation used is attributed only to that
> identifier.
>
> **Do I have to take part?** No. You may stop at any point, without giving a reason and
> without consequence. You may ask for your data to be removed up to [[ACTION: date]],
> after which analysis will be complete.
>
> **What happens to the results?** They appear in an anonymised form in my final year
> project report, which is assessed by my institution.
>
> **Questions?** Contact [[ACTION: your email]] or my supervisor, [[ACTION: name and
> email]].

## D.2 Consent Form

> Please initial each box.
>
> | | |
> |---|---|
> | I have read and understood the information sheet. | ☐ |
> | I have had the opportunity to ask questions. | ☐ |
> | I understand my participation is voluntary and I may withdraw at any time without giving a reason. | ☐ |
> | I understand that no identifying information about me will be recorded or published. | ☐ |
> | I understand that anonymised quotations from what I say may appear in the report. | ☐ |
> | I agree to take part in this study. | ☐ |
>
> Participant identifier (assigned by researcher): ______   Date: __________
>
> Researcher signature: ______________________   Date: __________

## D.3 Task Sheet Given to Participants

Tasks are stated as goals, not instructions, so that discoverability is under test.

1. Create an account on Weblogr and get to the point where you can see other people's
   posts.
2. Write and publish a post about a hobby or interest, with a picture.
3. Start writing a second post, but save it to finish later. Then find it again and publish
   it.
4. Find all the posts about Travel. Then find the most-liked post on the site.
5. Leave a comment on someone else's post, and show appreciation for a comment you agree
   with.
6. Follow an author whose posts you like, then find out whether the system told you
   anything about it.
7. Change your display name and profile picture.
8. You find a post you think breaks the rules. Tell the site administrator about it.

## D.4 System Usability Scale

Ten items, each rated 1 (strongly disagree) to 5 (strongly agree). Administer without the
researcher present.

1. I think that I would like to use this system frequently.
2. I found the system unnecessarily complex.
3. I thought the system was easy to use.
4. I think that I would need the support of a technical person to be able to use this
   system.
5. I found the various functions in this system were well integrated.
6. I thought there was too much inconsistency in this system.
7. I would imagine that most people would learn to use this system very quickly.
8. I found the system very cumbersome to use.
9. I felt very confident using the system.
10. I needed to learn a lot of things before I could get going with this system.

**Scoring.** For odd-numbered items, subtract 1 from the score. For even-numbered items,
subtract the score from 5. Sum the ten adjusted values (0–40) and multiply by 2.5, giving
0–100. **This is not a percentage.** Benchmark mean ≈ 68 (Brooke 1996; Sauro and Lewis
2016).

## D.5 Post-Task Interview Questions

1. What was the most difficult thing you did today, and what made it difficult?
2. Was there any point where you were not sure whether something had worked?
3. If you could change one thing about the system, what would it be?
4. Is there anything you expected to find that was not there?

---

# Appendix E — Complete Functional Test Suite

> **[[ACTION]]** Table 6.2 lists the fifteen cases that exposed defects, with full detail.
> This appendix must hold all sixty-one. Reproduce the table structure below and complete
> the remaining forty-six passing cases. Keep the ID numbering consistent with Table 6.3's
> traceability column — if you renumber, renumber both.

**Columns:** ID · Requirement · Precondition · Steps · Test data · Expected result ·
Actual result · Pass/Fail · Defect ID (if any)

**Worked example of the level of detail required:**

| Field | Content |
|---|---|
| **ID** | TC-23 |
| **Requirement** | FR-07 |
| **Precondition** | Authenticated as an author with no existing posts |
| **Steps** | 1. Select New Post. 2. Enter title. 3. Enter body. 4. Select category. 5. Attach image. 6. Submit without checking "Save as draft". |
| **Test data** | Title: "My First Post" (14 chars); body: 250 chars; category: Technology; image: `valid.jpg`, 340 KB |
| **Expected** | Redirect to feed; post appears at the top with title, image, category and author name; `blogs` gains one row |
| **Actual** | As expected |
| **Result** | Pass |
| **Defect** | — |

**Cases still to be documented**, grouped as in Table 6.1:

- **Registration (FR-01 – FR-03):** TC-01 valid registration · TC-02 duplicate verified
  username · TC-03 duplicate verified email · TC-04 mismatched password confirmation ·
  TC-06 empty required fields · TC-12 verify the stored value is a bcrypt hash, not
  plaintext
- **Verification (FR-02):** TC-07 correct OTP · TC-08 incorrect OTP · TC-10 non-numeric
  input
- **Authentication (FR-04 – FR-06):** TC-13 valid login · TC-15 unknown username · TC-16
  wrong password · TC-17 login to an unverified account · TC-18 recovery request for an
  unregistered address · TC-21 mismatched new passwords · TC-22 logout, then attempt to
  reach a protected page via the back button
- **Authoring and drafts (FR-07 – FR-09):** TC-24 post with no image · TC-25 empty title
  (boundary) · TC-26 title at maximum length and maximum + 1 · TC-28 draft visible only to
  its owner · TC-29 edit a draft · TC-30 publish a draft · TC-32 draft row removed on
  publication · TC-33 published draft appears in the feed · TC-34 delete a draft
- **Editing and deletion (FR-10 – FR-12):** TC-38 edit own post without replacing the image
  (confirm the image reference is preserved) · TC-40 own-posts list shows only own posts ·
  TC-41 own-posts list is empty for a new account · TC-42 post count on the profile matches
  the list
- **Discovery (FR-13 – FR-15):** TC-43 default reverse-chronological order · TC-44 category
  filter returns only that category · TC-46 author filter · TC-47 category and author
  filters combined · TC-48 sort by date ascending · TC-49 sort by most liked
- **Comments and likes (FR-16 – FR-18):** TC-50 submit a comment · TC-51 submit an empty
  comment · TC-53 like count persists across reload · TC-54 like a comment
- **Following and notifications (FR-19 – FR-21):** TC-55 follow an author · TC-56
  notification received by the followed author · TC-57 edit profile name and image
- **Moderation (FR-22 – FR-23):** TC-59 submit a report · TC-60 report appears in the
  administrative list · TC-61 administrator deletes a reported post

---

# Appendix F — Performance Measurement Harness

Insert at the top of each measured script:

```php
<?php
$__t0 = microtime(true);
```

and immediately before the closing tag:

```php
<?php
$__ms = (microtime(true) - $__t0) * 1000;
file_put_contents(
    __DIR__ . '/../timings.csv',
    basename($_SERVER['SCRIPT_NAME']) . ',' . number_format($__ms, 2) . PHP_EOL,
    FILE_APPEND
);
```

Discard the first two requests per script as warm-up, then take ten measurements. Compute
the mean and sample standard deviation per script and enter them in Table 6.5.

**Seed data generator** for the dataset described in Section 6.2.3 — run once against a
clean schema:

> **[[ACTION]]** Write a short script that creates 5 verified accounts, 50 posts
> distributed across the 7 categories with varied `created_date` values and varied `likes`
> counts, 120 comments spread across those posts, and 30 follow edges. Vary the values —
> uniform data will not exercise the sort and filter paths meaningfully. Include the script
> here so the measurement is reproducible.

**Remove the harness before submission**, or guard it behind a constant, so the measured
code is not the shipped code.

---

# Appendix G — Source Code

The complete source is submitted separately. Structure as listed in Section 5.2.1.

> **[[ACTION]] Before submitting the repository:**
>
> 1. Confirm the SMTP application password recorded in §6.7.1 has been revoked at the
>    provider. Deleting the line from the current files does not remove it from the git
>    history.
> 2. Remove or rewrite the committed credentials from history (`git filter-repo`, or
>    reinitialise the repository), and add the configuration file to `.gitignore`.
> 3. Replace the DDL in `README.md` with the delivered schema, or remove it (D-21).
> 4. Remove `registration/smtp/` if the Composer installation is used, or the reverse —
>    not both (D-22).
> 5. Remove the performance harness of Appendix F.
