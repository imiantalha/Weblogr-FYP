# Chapter 8 — Conclusion and Future Work

## 8.1 Summary of the Work

This project set out to determine to what extent a focused, minimal-dependency web
application can deliver the publishing and community-engagement capabilities that
individual and small-community blog authors require, and what the architectural, security
and usability consequences are of building such a system without an application
framework.

Chapter 2 reviewed content management architecture, web application security and usability
measurement, and derived a requirement set from a structured comparison of four
comparable systems. Chapter 3 selected an iterative and incremental process model and
justified a framework-free PHP implementation, recording in advance the four costs that
decision was expected to incur. Chapter 4 specified twenty-three functional and fourteen
non-functional requirements and presented the design: a three-tier architecture, an
eight-relation schema in third normal form with two documented departures, and the
supporting use case, data flow, sequence and state models. Chapter 5 described the
implementation across five subsystems and stated which specified security controls were
realised and which were not. Chapter 6 verified the implementation against its
specification through sixty-one functional test cases, a performance measurement and an
OWASP-based security assessment, registering twenty-three defects. Chapter 7 evaluated
the system against its objectives and against the comparator systems.

The delivered artefact satisfies nineteen of twenty-three functional requirements in whole
or in part. Two — password recovery and authorised post deletion — are not met in their
specified form. Of the fourteen non-functional requirements, four are met (NFR-01, NFR-08,
NFR-13, NFR-14), five are partially met (NFR-02, NFR-04, NFR-07, NFR-11, NFR-12), three
are not met (NFR-03, NFR-05, NFR-06), and two remain unverified (NFR-09, NFR-10). The
unmet requirements are concentrated in security and accessibility.

## 8.2 Discussion: The Framework-Free Decision

Section 3.4 recorded the decision to build without a framework, together with four costs
accepted in advance: that security would become the developer's responsibility, that
cross-cutting concerns could not be centralised, that business logic would not be
unit-testable, and that duplication was likely. Risk R4 in Section 3.10 rated the
probability and impact of security defects arising from this decision as High/High.

**R4 materialised, and it materialised in the precise form predicted.** This section
examines why the mitigations recorded against it — "apply prepared statements
consistently; escape all output; review each script against the OWASP list before
sign-off" — failed, because that analysis is the project's principal transferable result.

The mitigations failed because all three depended on the developer performing the same
action correctly at every one of a large and growing number of independent points, with
no mechanism to detect an omission. Consider what each required:

- *Prepared statements consistently* — correct execution at every one of some forty query
  sites, in scripts written weeks apart.
- *Escape all output* — correct execution at every one of roughly ninety points where a
  stored value reaches the response.
- *Review each script against OWASP* — a manual audit of twenty-eight scripts, performed
  by the person who wrote them, with no checklist automation.

A framework does not make the developer more disciplined; it removes the requirement for
discipline by changing where the control lives. Automatic output escaping in a template
engine means the escape happens because rendering happens, not because the developer
remembered. CSRF middleware means the token is validated because the request was routed,
not because the developer added a check. Authorisation middleware attached to a route
group means the check runs on every route in the group, including routes added later. In
each case the control is applied *by the mechanism that must run anyway*, so omission is
not possible without also breaking the feature.

Section 6.6.1 shows exactly this. The session guard — a local check, visible at the top of
each file, whose absence is noticeable when reading the file — was implemented in twenty
of twenty-eight scripts. Ownership verification and role verification — checks requiring
the developer to relate the request to stored state, invisible by their absence — were
implemented in *none*. The difference between 71% and 0% is not a difference in
knowledge. Both controls are described in Section 2.3.1 and both were specified in Table
4.2. It is a difference in whether omission is *visible*.

This generalises: **the framework-free approach failed not at the controls that were hard
to understand, but at the controls that were easy to forget.** The distribution in Section
6.8.3 supports it — fewer than a fifth of the registered defects arise from not knowing
what to do.

### 8.2.1 Consequences of an Untestable Architecture

The second predicted cost was that business logic embedded in request-handling scripts
would not be unit-testable. This too materialised, and its cost is measurable in the
defect register.

Eight of the twenty-three defects (D-06, D-07, D-09, D-11, D-17, D-18, D-19, D-20) produce
an observably incorrect result on the *first* execution of the affected path. A draft
`UPDATE` with no `WHERE` clause overwrites every draft row. A "following" query that joins
and filters on the same column returns the viewing user. A validator that reads `.value`
from a `NodeList` throws on invocation. A misspelled `Locaton` header issues no redirect.
Each of these is caught by a single assertion. None survives a test that calls the logic
and inspects the result.

They survived because there was nothing to call. Every one is embedded in a script that
begins by reading superglobals and ends by emitting markup; exercising the logic requires
issuing an HTTP request and parsing a page. This is why sixteen of twenty-three defects
were found by inspection rather than execution: inspection was the only instrument
available at the granularity where these faults live.

The absence of MVC separation compounds this. Section 4.5 describes the delivered pattern
— guard, query, render — within a single file. In `posts/index.php`, filter parsing, query
composition, execution and HTML generation occupy one script with no boundary between
them. The clause-assembly defect D-11 is a direct consequence: query construction is
interleaved with presentation concerns, so the logic that decides between `WHERE` and
`AND` is not separable, not readable in isolation, and not testable.

Duplication, the fourth predicted cost, is present and quantifiable: the four-line session
guard appears in twenty files; connection inclusion appears in twenty-eight; the
notification insert pattern appears in five; category option lists appear in four. A
single change to session handling requires twenty coordinated edits, and an omitted one
produces exactly the class of gap Table 6.7 records.

### 8.2.2 Was the Decision Correct?

Two criteria apply, and they give different answers.

**As an engineering decision, no.** The costs materialised in full and the anticipated
benefits did not offset them. The stated benefits were deployment simplicity and the
absence of a build step. Laravel or CodeIgniter would have added a Composer install to
deployment — a marginal cost against fourteen failing test cases, six critical defects
and eight unmet or unverified non-functional requirements. Four of the six critical defects (D-02, D-04,
and the CSRF and XSS exposures underlying the assessment in §6.6) would not have existed
in a framework implementation, not because the developer would have been more careful but
because the framework applies those controls by default.

The inconsistency *within* the implementation is the sharpest evidence. The same developer
used correctly parameterised statements throughout the authentication subsystem (§5.3.1)
and direct interpolation throughout content management (§5.4.1). The knowledge was present
and demonstrably applied. What was absent was any mechanism ensuring it was applied
*everywhere*. That is precisely what a framework supplies.

**As a learning decision, yes, and this is not a consolation.** Building without a
framework made each control's necessity explicit rather than automatic. Writing
`password_hash()` by hand, constructing the fan-out loop, and then discovering by
systematic assessment that output escaping was omitted at ninety points produces an
understanding of *why* frameworks exist that using one would not. Section 8.5 develops
this.

The recommendation that follows is specific rather than general. It is not "always use a
framework"; it is: **where an application accepts untrusted input and renders stored
state, adopt a framework or replicate its cross-cutting controls structurally — through a
single rendering function that escapes by default, a single request-entry point that
validates tokens, and a single authorisation function every handler must call — rather
than relying on per-site discipline.** The controls need not come from a framework; they
must come from somewhere other than memory.

## 8.3 Limitations

### 8.3.1 Data Model

The **draft duplication** (§4.9.1) stores drafts in a separate relation with the same
attributes as `blogs`. Every schema change must be applied twice, publication is a
delete-and-insert rather than a status change, and the `blog_id` is not preserved across
publication. A `status` enumeration on `blogs` would have avoided all three.

The **materialised like counter** (§4.9.2) stores an integer on `blogs` with no
association relation recording who liked what. Consequently a user may like a post
without limit, no like can be withdrawn, and no personalised view ("posts you liked") is
possible. The counter is not a cache of anything and cannot be recomputed if it drifts.

**Notification storage** (§5.5.4) holds pre-rendered strings embedding a display name
captured at event time, with no read state, timestamp or type attribute. Notifications
cannot be marked read, ordered chronologically, or filtered, and — as Section 6.6.4 shows
— cannot be escaped on output without breaking their rendering.

**Referential integrity** is incomplete: `blogs.user_id` carries no foreign key and the
`reports` relation carries none at all, so orphaned rows are creatable. No `ON DELETE`
semantics are declared anywhere, so account deletion is not expressible as a single
operation.

**Indexing** is limited to primary and the declared foreign keys. `blogs.category`,
`blogs.created_date` and `blogs.likes` are unindexed despite appearing in the `WHERE` and
`ORDER BY` clauses of the most frequently executed query in the system (§4.6.4). At the
tested volume this is unmeasurable; at scale it is the first bottleneck.

**Character encoding**: tables collated `latin1_swedish_ci` against UTF-8 page declarations
(D-12) means the system cannot faithfully store content outside the Latin-1 repertoire.

### 8.3.2 Public Reading

Every content view sits behind the session guard, so no post is readable without an
account. For a publishing platform this is the most consequential functional limitation
in the system: an author using Weblogr cannot share a post with anyone who has not
registered. It arose from applying one uniform access policy to every route rather than
distinguishing public resources from protected ones, and it is a design omission rather
than a defect — no requirement in Table 4.1 specifies public reading, which is itself the
error. Remediation is specified in Section 8.4.

### 8.3.3 Accessibility

Navigation controls are icon-only with no text alternative (§4.8.1), failing WCAG 2.1
criterion 1.1.1 and, for controls conveyed by icon shape alone, 1.4.1. Form fields are
not consistently associated with labels (3.3.2), focus indicators are suppressed by the
stylesheet in places (2.4.7), and no skip-navigation mechanism exists (2.4.1). NFR-12
(Level A conformance) is not met. This affects users of assistive technology absolutely,
not marginally — an icon-only control with no accessible name is announced by a screen
reader as an unlabelled link.

Section 7.6 anticipates that icon-only navigation will also affect sighted first-time
users, which would make the fix beneficial on two independent grounds.

### 8.3.4 Specification and Presentation Discrepancies

The static landing page advertises **performance analytics**, a feature that does not exist
in the system. Advertising unimplemented functionality is a defect of the product as
delivered, and the copy should be removed before any use of the system. It is recorded
here rather than quietly corrected because an examiner comparing the landing page against
the feature set will find it.

The **reporting mechanism** transmits the report's full text to the reported author while
the interface wording implies anonymity (§5.6.1). Either the disclosure or the wording
must change; the two cannot both stand.

The **README** contains DDL contradicting the delivered schema (D-21), which would mislead
anyone attempting to deploy from it.

### 8.3.5 Scalability

The system is designed for the small population assumed in Section 1.4.3 and three
properties prevent it from exceeding that.

*Notification fan-out* executes one insert per follower synchronously within the
publication request (§5.5.3). Publication latency is therefore linear in follower count.
At a thousand followers the request will approach or exceed the PHP execution limit.
Remedies, in ascending order of effort: a single multi-row insert; a queued job; or
fan-out-on-read, where notifications are derived at retrieval time from the follow graph
rather than materialised at write time.

*The feed query* scans `blogs` joined to `users` with no pagination and no index on the
filter or sort columns. Every request retrieves every post. At ten thousand posts both
the query and the response are untenable.

*Session storage* uses PHP's default filesystem handler, which does not permit horizontal
scaling without either sticky sessions or a shared session store.

*Images* are served from the application's document root by the same Apache process that
executes PHP, so static and dynamic load are not separable.

None of these is a defect against the stated assumption. All are limits on exceeding it,
and the assumption should be read as a genuine constraint rather than a disclaimer.

### 8.3.6 Technology and Dependency Choices

**mysqli rather than PDO.** PDO provides a uniform interface across drivers, named
parameters, and a consistent exception mode. Named parameters would materially have
reduced the risk of the binding errors that make parameterisation feel costly at
multi-parameter query sites — plausibly a contributing factor to why interpolation was
used in the content-management subsystem. PDO would have been the better choice and
migration is a bounded task, since the query text is largely unchanged.

**Two copies of PHPMailer** are retained (§5.2.4), a Composer installation and a legacy
directory, with the mail scripts referencing the legacy copy. Only one should exist, and
it should be the managed one, so that security updates apply.

**No configuration externalisation.** Database and SMTP credentials are embedded in source
(§5.2.2, §5.3.2). Configuration should be read from an environment file excluded from
version control. Section 6.7.1 records the concrete consequence.

**No dependency on a build or lint step.** No static analysis tool was applied. PHPStan or
Psalm at even a permissive level would have detected D-19 (variables tested but never
assigned) and D-20 (unreachable redirect), and ESLint would have detected D-17. Adding
static analysis is the single cheapest improvement available to this project, and its
absence is why those defects reached the register.

### 8.3.7 Evaluation

The usability evaluation is limited by sample size, by the developer acting as
researcher, by the absence of task counterbalancing and by single-coder qualitative
analysis; these are stated in full in Section 7.9. The performance measurement does not
address concurrency and therefore leaves NFR-10 unverified (§6.5.3). No longitudinal use
was observed, so nothing is known about the system's suitability for sustained authoring.

With n < 10 no inferential statistics were performed. This is a deliberate choice rather
than an omission: applying a significance test to eight participants would produce a
number without evidential value, and reporting one would be a stronger claim than the
data supports.

### 8.3.8 Data Protection

The system holds identifying data — email address, display name, profile photograph — with
no account deletion facility, no data export, no privacy notice and no retention policy.
Under GDPR these correspond to the rights of erasure, portability and information. No
deletion is expressible in any case, since no `ON DELETE` semantics are declared (§8.3.1).
For a system holding personal data of real users this would be a compliance failure, not
merely a missing feature.

### 8.3.9 Development Process Evidence

The repository holds a single commit. Consequently the iterative process described in
Section 3.2 is asserted in this report but not evidenced by the version history, and no
record exists of when each defect was introduced or how each iteration progressed. An
examiner is entitled to treat an undocumented process claim sceptically. Future work of
any kind should commit incrementally with descriptive messages from the outset; the
history is itself a deliverable.

## 8.4 Future Work

Ordered by priority. Items in Priority 1 are prerequisites for any deployment holding real
user data.

### Priority 1 — Security remediation

1. **Escape all output.** Apply `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` at every point
   where a stored value reaches the response. Introduce a single helper (`e($v)`) and use
   it uniformly so that the escaped form is shorter to write than the unescaped one.
   Notification content requires the schema change in item 8 first, since its stored value
   contains markup (§6.6.4).
2. **Enforce ownership on every mutating operation.** Add `AND user_id = ?` bound to the
   session user in every `UPDATE` and `DELETE`, and verify affected-row count before
   reporting success. Fixes D-02.
3. **Add the session guard where it is missing**, and add a role check reading
   `users.user_type` — stored in the session at login — to the administrative views. Fixes
   D-03 and D-04.
4. **Parameterise every remaining query.** Convert the content-management and comment
   subsystems to prepared statements, matching the pattern already used in authentication.
5. **Add CSRF tokens** to every form and validate on every handler; convert the like,
   follow, delete and report endpoints from GET to POST (§6.6.3).
6. **Validate uploads**: extension allow-list, content-based type verification via
   `finfo`, size limit, server-generated filename, and an upload directory configured
   without execute permission. Fixes D-05.
7. **Harden authentication**: `random_int()` for OTP generation; an `otp_expires_at`
   column with enforced expiry; attempt counters on login and verification;
   `session_regenerate_id(true)` on authentication; `HttpOnly`, `Secure` and
   `SameSite=Lax` cookie flags; a nullable `otp` column. Fixes D-14 to D-16 and completes
   §6.6.2. Repair FR-05 by binding the reset to a session-recorded verification (§5.3.4).

### Priority 2 — Data model

8. **Restructure notifications**: replace the pre-rendered string with `actor_id`,
   `type`, `target_id`, `created_at` and `is_read`, and compose the message at render
   time. This enables read state, chronological ordering, filtering, and correct escaping
   in one change (§8.3.1).
9. **Introduce a `post_likes` association relation** with a composite primary key on
   `(post_id, user_id)`, making likes idempotent by constraint, reversible, and
   recomputable, and enabling a "liked posts" view (§4.9.2).
10. **Replace `draft_posts` with a `status` attribute on `blogs`**, eliminating the
    duplicated relation and making publication a single update (§4.9.1).
11. **Complete referential integrity**: foreign key on `blogs.user_id`, foreign keys on
    `reports`, and explicit `ON DELETE` semantics throughout; add `updated_at` distinct
    from `created_date`, fixing D-08; convert the schema to `utf8mb4_unicode_ci` and set
    the connection charset, fixing D-12; add indexes on `category`, `created_date` and
    `likes`; add unique constraints on `users.username`, `users.email` and
    `followers(blogger_id, follower_id)`, fixing D-13.

### Priority 3 — Architecture and testability

12. **Extract data access into repository classes** — `UserRepository`, `PostRepository`,
    `CommentRepository` — so that query construction is addressable independently of the
    request cycle. This is the prerequisite for item 13 and would have made the eight
    defects of Section 8.2.1 catchable.
13. **Introduce PHPUnit** with tests over the extracted repositories and validators, and
    add static analysis (PHPStan, ESLint) to catch D-17, D-19 and D-20 automatically.
14. **Introduce a single front controller** so that authentication, CSRF validation and
    error handling run once per request rather than being repeated per script (§8.2.1).
15. **Externalise configuration** to an environment file excluded from version control,
    and remove the duplicate PHPMailer installation (§8.3.6).

### Priority 4 — Function and reach

16. **Public reading.** Separate public from protected routes so that posts are readable
    without an account, with authoring and engagement remaining authenticated (§8.3.2).
17. **Accessibility remediation**: accessible names on all icon controls, correct label
    association, visible focus indicators, a skip link, and verification against WCAG 2.1
    Level A (§8.3.3). Retain the existing visual design — this requires markup and
    attribute changes, not a redesign.
18. **Responsive layout at handset widths**, addressing the failure recorded at 375 px in
    Table 6.4.
19. **Pagination on the feed**, and an unfollow operation, and comment editing and
    deletion, each currently absent.
20. **Account deletion, data export and a privacy notice**, addressing §8.3.8. Depends on
    item 11.
21. **Full-text search**, **rich-text authoring with server-side sanitisation** (which
    depends on item 1 being complete), and **a REST API** exposing the resource set,
    which would in turn permit the native mobile client excluded in Section 1.4.2.
22. **Remove the analytics claim from the landing page**, or implement it (§8.3.4).

## 8.5 Reflection

Three things would be done differently.

**A framework would be adopted, and the reason is now precise rather than general.** Not
because frameworks are better in the abstract, but because this project produced direct
evidence that the specific class of control a framework centralises — escaping,
authorisation, token validation — is the class this developer, holding the correct
knowledge, nonetheless failed to apply uniformly across twenty-eight files. The failure
was not of understanding but of coverage, and coverage is what centralisation buys.

**Testing would begin with the first feature rather than after the last.** The eight
defects of Section 8.2.1 were latent for the entire development period and were found
only when systematic testing began. Writing the test alongside the feature would have
caught each within minutes of its introduction, and — more importantly — would have forced
the logic out of the request-handling scripts, since untestable code cannot be tested. The
architectural improvement would have been a consequence of the testing discipline rather
than a separate undertaking.

**Version history would be treated as a deliverable.** A single commit provides no
evidence of the process this report describes and no ability to bisect a regression.

The most valuable outcome of this project is not the artefact. It is Section 6.8.3: the
observation that the defects cluster not where the problem was hard but where the
discipline was distributed. Fewer than a fifth arise from not knowing what to do. That
finding is only obtainable by building the system the difficult way and then examining the
result honestly, and it transfers to any project where a correctness requirement is
discharged by remembering to do something at every one of many independent points.

## 8.6 Concluding Remarks

Weblogr delivers a coherent blog publishing and community-engagement platform in
twenty-eight server-side scripts and eight relations, with two third-party dependencies
and no build step. It satisfies nineteen of twenty-three functional requirements, and it
provides in a single system the engagement features that its established comparators
distribute across plugins. Judged as a working artefact against its own specification, it
is a partial success with two requirements unmet and a specified remediation path.

Judged against the research question of Section 1.2, it produces a clearer answer than
the artefact alone would suggest. A focused, minimal-dependency application *can* deliver
the required capabilities: the feature comparison in Table 7.10 establishes that. The
consequences of doing so without a framework are that security controls requiring uniform
application across many independent points will be applied inconsistently, that business
logic embedded in request handlers will not be verifiable at the granularity where its
faults occur, and that the resulting defects will cluster in exactly those areas rather
than distributing evenly across the system. Six critical defects, eight unmet or
unverified non-functional
requirements and twenty-three registered faults are the measured cost, and their
distribution — concentrated in cross-cutting concerns, sparse in localised ones — is the
finding that gives that cost meaning.
