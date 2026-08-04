# Chapter 2 — Literature Review and Related Systems

## 2.1 Introduction

This chapter establishes the foundation on which Weblogr's requirements and design rest.
It proceeds in four parts. Section 2.2 reviews the architecture of content management
systems and the design patterns that govern data-driven web applications. Section 2.3
examines web application security, since the system handles credentials, personal data
and user-generated content. Section 2.4 reviews usability principles and the
instruments available for measuring them. Section 2.5 presents a structured comparison
of four related systems. Section 2.6 synthesises these strands into the gap this
project addresses, and Section 2.7 states the requirements derived from the review.

> **[[ACTION: Read before citing.]]** Every source referenced in this chapter is a
> standard work in its field, but you must obtain and read each one before submission.
> Verify the edition, year and publisher against your library catalogue and format the
> citations in your institution's required style. Delete any reference you have not
> read, and add papers you find during your own reading — a literature review that
> cites only textbooks is weaker than one that engages with primary research. Aim to
> add at least five peer-reviewed papers of your own.

## 2.2 Content Management System Architecture

### 2.2.1 The Content Management Problem

A content management system separates the creation and storage of content from its
presentation, allowing non-technical authors to publish without editing markup. This
separation is the defining characteristic of the category, and it implies three
architectural obligations: a persistence mechanism for content, an authoring interface
that abstracts away the storage representation, and a rendering mechanism that combines
stored content with presentation templates at request time.

Weblogr discharges all three, though as Chapter 5 documents, the third is realised by
interleaving markup generation with data access rather than by a template layer. The
consequences of that choice are analysed in Section 8.2.1.

### 2.2.2 Layered Architecture and Separation of Concerns

The layered architectural style is the conventional organisation for data-driven web
applications. Sommerville (2016) characterises it as a decomposition in which each layer
provides services to the layer above and depends only on the layer below, yielding
replaceability and testability. The canonical three-tier arrangement comprises a
presentation tier, an application or business-logic tier, and a data tier.

Fowler (2002) elaborates the patterns that populate these tiers, and two are directly
relevant here. The **Transaction Script** pattern organises business logic as a
procedure per request, each handling one user action from input through to persistence.
The **Domain Model** pattern instead builds an object graph representing the problem
domain, with behaviour distributed across it. Fowler observes that Transaction Script is
appropriate where logic is simple and largely CRUD-shaped, and that its principal
weakness is duplication: as the number of scripts grows, common logic is copied rather
than shared, and the cost of change rises.

Weblogr is an unambiguous instance of Transaction Script — each PHP file services one
user action end to end. Section 4.5 justifies this against the alternatives; Section
8.2.1 evaluates the choice against the duplication Fowler predicts, which the
implementation exhibits in a measurable form.

### 2.2.3 Model–View–Controller

MVC, originating in Smalltalk-80 and now the default organisation for web frameworks,
divides an application into a model holding data and domain logic, a view rendering it,
and a controller mediating input. Its principal benefit is that presentation can change
without disturbing logic, and logic can be tested without rendering.

Weblogr does not implement MVC. This is a significant decision with consequences for
both maintainability and testability that recur throughout this report — most concretely
in Chapter 6, where the absence of a separable logic layer is what prevents automated
unit testing and forces reliance on manual test execution. Section 3.4 justifies the
decision and Section 8.2.1 evaluates it.

### 2.2.4 Relational Design and Normalisation

Codd's (1970) relational model underpins the data tier. The normal forms provide the
criteria against which Chapter 4's schema design is assessed:

- **First normal form (1NF)** — attributes are atomic; no repeating groups.
- **Second normal form (2NF)** — 1NF, and every non-key attribute is fully functionally
  dependent on the whole primary key.
- **Third normal form (3NF)** — 2NF, and no transitive dependency exists between
  non-key attributes.

Normalisation eliminates the insertion, update and deletion anomalies that arise from
redundant storage. It trades write-time integrity against read-time join cost, and
practical designs therefore sometimes denormalise deliberately — for instance by
maintaining a materialised counter rather than aggregating on read. Where Weblogr does
this, and what it costs, is analysed in Sections 4.6.4 and 8.3.1.

Referential integrity, enforced through foreign key constraints, is the mechanism by
which the database rather than the application guarantees that relationships remain
valid. The distinction matters: constraints enforced only in application code are
bypassed by any other client of the database, and are subject to race conditions between
a check and the write that follows it.

## 2.3 Web Application Security

### 2.3.1 The OWASP Top 10

The Open Worldwide Application Security Project publishes a periodically revised
consensus list of the most critical web application security risks. The 2021 revision
is used in this project as the framework for the security review reported in Section
6.6. Five of its categories bear directly on Weblogr's design:

**A01 Broken Access Control.** Enforcement failures allowing users to act outside their
intended permissions. The characteristic web instance is the *insecure direct object
reference*, where an identifier in a request parameter is used to retrieve or modify a
record without verifying that the requester is entitled to it. Prevention requires that
authorisation be checked server-side on every request, against the authenticated
session, for every object accessed — never inferred from the interface not offering a
link.

**A02 Cryptographic Failures.** Inadequate protection of data in transit or at rest.
For an application storing credentials, this centres on password storage: passwords must
be stored as salted hashes produced by a deliberately slow, memory-hard algorithm such
as bcrypt or Argon2, never as plaintext or as fast general-purpose digests such as MD5
or SHA-1. It also encompasses the handling of secondary secrets such as one-time
passwords and API credentials.

**A03 Injection.** Untrusted input incorporated into an interpreter's command. SQL
injection is the canonical case. The definitive mitigation is the parameterised
statement, in which the query structure is transmitted to the database separately from
the data, making it impossible for data to alter structure. Escaping functions such as
`mysqli_real_escape_string` are a weaker mitigation: they are correct only when the
value is interpolated inside quotation marks, they depend on the connection character
set being set correctly, and they offer no protection in unquoted numeric contexts.

**A05 Security Misconfiguration.** Insecure defaults, unnecessary features, verbose
error messages disclosing internal detail, and credentials committed to source control.

**A07 Identification and Authentication Failures.** Weaknesses permitting credential
compromise or session hijacking, including absent rate limiting, weak recovery
mechanisms, session fixation, and inadequate session token handling.

To these the review adds **Cross-Site Scripting (XSS)**, classified under Injection in
the 2021 revision. Stored XSS — where an attacker's script is persisted and served to
subsequent viewers — is the variant most relevant to a platform whose purpose is to
store and redisplay user-authored content. The mitigation is contextual output encoding:
escaping data at the point of rendering according to the context into which it is
inserted.

**Cross-Site Request Forgery (CSRF)** completes the set. CSRF exploits the browser's
automatic transmission of cookies to cause an authenticated user's browser to issue a
state-changing request the user did not intend. The standard mitigation is a
synchroniser token bound to the session and required on every state-changing request.
A necessary precondition is that state-changing operations use POST rather than GET;
RFC 9110 defines GET as a safe method, and infrastructure throughout the web — browser
prefetchers, crawlers, proxies, link scanners — relies on that guarantee.

### 2.3.2 Authentication and Credential Handling

Password storage practice is settled: use an adaptive hash with a per-password salt and
a tunable work factor. PHP's `password_hash()` implements this, defaulting to bcrypt,
and `password_verify()` performs the comparison in constant time. Weblogr's use of these
functions is documented in Section 5.3.1.

Multi-factor and out-of-band verification introduce a second consideration. A one-time
password sent by email verifies control of the email address and is commonly used for
account activation and password recovery. Its security depends on four properties, each
of which is a potential failure point: the code must be generated by a cryptographically
secure random source, so it cannot be predicted from prior codes; it must expire within
a short window; the number of verification attempts must be limited, since a six-digit
code offers only 10⁶ possibilities and is exhaustible in minutes under unlimited
attempts; and it must be bound to the session that requested it, so that possession of
a code for one account cannot be used against another. Weblogr's OTP implementation is
described in Section 5.3.2 and assessed against these four properties in Section 6.6.2.

### 2.3.3 File Upload Security

Applications accepting file uploads face a well-characterised risk: if an attacker can
place a file with an executable extension inside the web root, the server may execute
it, yielding remote code execution. Defence in depth requires an extension allow-list
rather than a deny-list; verification of content type by inspecting file content rather
than trusting the client-supplied MIME type or extension; a server-generated filename,
which simultaneously prevents path traversal through crafted filenames and prevents one
user's upload from overwriting another's; a size limit; and ideally storage outside the
document root or in a directory configured to disable script execution. Weblogr's upload
handling is described in Section 5.4.3 and assessed in Section 6.6.3.

## 2.4 Usability and Accessibility

### 2.4.1 Heuristic Principles

Nielsen's (1994) ten usability heuristics remain the most widely applied framework for
interface evaluation. Six bear directly on Weblogr's design and are used in Section
4.8 to justify interface decisions and in Chapter 7 to interpret the evaluation results:

1. **Visibility of system status** — the system should keep users informed about what is
   happening through appropriate feedback within reasonable time.
2. **Match between system and the real world** — use the users' language and familiar
   concepts rather than system-oriented terms.
3. **User control and freedom** — provide a clearly marked exit from unwanted states;
   support undo.
4. **Consistency and standards** — the same action should be expressed the same way
   throughout, and platform conventions should be followed.
5. **Error prevention** — designs that prevent problems occurring are preferable to good
   error messages.
6. **Recognition rather than recall** — make objects, actions and options visible rather
   than requiring the user to remember information across parts of the dialogue.

Krug (2014) reduces much of this to a design injunction — that an interface should not
require conscious thought to navigate — and argues specifically that navigation should
be self-evident and that users scan rather than read. Section 8.3.3 applies this to
Weblogr's icon-only navigation, which the evaluation in Chapter 7 examines directly.

### 2.4.2 Measuring Usability

Subjective usability is most commonly measured with the **System Usability Scale**
(Brooke, 1996), a ten-item questionnaire with alternating positive and negative
statements answered on a five-point Likert scale. Its scoring produces a single value
from 0 to 100. It is widely adopted because it is short enough to complete in two
minutes, technology-agnostic, and — most importantly for this project — extensively
benchmarked, so that a score can be interpreted against published norms rather than
merely reported. Sauro and Lewis's normative work places the mean SUS score across
several hundred studies at approximately 68, which is conventionally treated as the
threshold of acceptable usability.

Nielsen's (2000) finding that a small number of evaluators identifies the majority of
usability problems provides the justification for the sample size used in Chapter 7,
where the constraints of an undergraduate project preclude a large study. The claim is
specific and should not be overstated: it concerns the discovery of usability *problems*
in formative evaluation, not the estimation of population parameters. Quantitative
measures such as SUS derived from a small sample carry wide confidence intervals, and
Chapter 7 reports them accordingly rather than presenting point estimates as precise.

Objective measures complement the subjective instrument: task completion rate, time on
task, and error rate. Chapter 7 collects all three.

### 2.4.3 Accessibility

The Web Content Accessibility Guidelines 2.1 (W3C, 2018) organise accessibility
requirements under four principles — that content be Perceivable, Operable,
Understandable and Robust — across three conformance levels. Several Level A and AA
criteria are directly applicable to Weblogr and are used as the assessment framework in
Section 8.3.3: text alternatives for non-text content (1.1.1), information not conveyed
by colour alone (1.4.1), full keyboard operability (2.1.1), descriptive page titles
(2.4.2), visible focus indication (2.4.7), and programmatically associated labels for
form inputs (3.3.2).

## 2.5 Review of Related Systems

Four systems were selected to span the design space described in Section 1.1: two hosted
platforms and two self-hosted systems, chosen for market significance and for the
contrast between their architectural positions.

### 2.5.1 WordPress

WordPress is the dominant self-hosted CMS, powering a large fraction of all websites.
It offers complete data ownership, an extensive plugin ecosystem, a theme system, a
taxonomy engine, a REST API and a mature editorial workflow including revisions,
scheduling and multi-author roles.

Its cost is complexity. Installation requires provisioning a web server, PHP runtime and
database. Operation requires ongoing security patching of core, themes and plugins —
and the plugin ecosystem that constitutes its principal advantage is also its principal
attack surface, as the majority of WordPress compromises originate in third-party
extensions rather than core. For an author who wants only to publish articles, the
system is substantially larger than the requirement, and the maintenance burden is
continuous rather than one-off.

### 2.5.2 Medium

Medium is a hosted platform providing a refined writing interface, a built-in readership
through algorithmic distribution, and social features including claps, responses and
following. It requires no technical knowledge whatsoever.

The trade-off is control. Authors publish into Medium's environment under Medium's
terms; distribution is mediated by the platform's algorithm rather than by the author's
own audience relationships; presentation is fixed; content has at times been placed
behind a paywall not of the author's choosing; and portability, while technically
supported by export, does not carry the audience with it.

### 2.5.3 Blogger

Blogger, operated by Google, is a long-established free hosted platform with a simple
post editor, template-based theming and custom domain support. Its principal
disadvantages are the platform's limited development activity, a dated template system,
weak social engagement features relative to contemporary alternatives, and the strategic
risk associated with dependence on a service the provider may discontinue.

### 2.5.4 Ghost

Ghost is a self-hosted publishing platform built on Node.js, deliberately narrower in
scope than WordPress and focused on professional publishing. It offers a Markdown-based
editor, built-in membership and newsletter functionality, and markedly better default
performance than WordPress.

Its scope discipline is the closest of the four systems to the position this project
occupies. It differs in that its installation and operation still demand competence
with Node.js runtime management, and its feature set is oriented toward professional
publishers with monetisation requirements rather than toward individual authors and
small communities.

### 2.5.5 Comparative Analysis

**Table 2.1 — Feature comparison of related systems**

| Capability | WordPress | Medium | Blogger | Ghost | **Weblogr** |
|---|---|---|---|---|---|
| Deployment model | Self-hosted | Hosted | Hosted | Self-hosted | **Self-hosted** |
| Data ownership | Full | Limited | Limited | Full | **Full** |
| Installation complexity | High | None | None | High | **Low** |
| Runtime dependencies | PHP + MySQL + plugins | — | — | Node.js + DB | **PHP + MySQL** |
| Rich-text authoring | Yes | Yes | Yes | Yes | **No — plain text** |
| Draft workflow | Yes | Yes | Yes | Yes | **Yes** |
| Categorisation | Categories + tags | Tags | Labels | Tags | **Fixed categories** |
| Comments | Via plugin | Responses | Yes | Via integration | **Yes, native** |
| Likes / reactions | Via plugin | Claps | No | No | **Yes, native** |
| Follow relationships | Via plugin | Yes | No | Members | **Yes, native** |
| In-app notifications | Via plugin | Yes | Limited | No | **Yes, native** |
| Content reporting | Via plugin | Yes | Yes | No | **Yes, native** |
| Full-text search | Yes | Yes | Yes | Yes | **No** |
| Public reading without account | Yes | Yes | Yes | Yes | **No** |
| Analytics | Via plugin | Yes | Yes | Yes | **No** |
| Mobile applications | Yes | Yes | Yes | Third-party | **No** |
| Maintenance burden | High | None | None | Moderate | **Low** |

Three observations follow from the comparison.

First, the social engagement features that Weblogr provides natively — comments, likes,
following, notifications and reporting — are available in WordPress only through
third-party plugins, and are largely absent from Ghost. Where a WordPress deployment
would assemble these from four or five separately maintained extensions, each an
independent update and security liability, Weblogr integrates them against a single
schema. This is the substantive functional argument for the system.

Second, Weblogr's omissions are concentrated in discovery and reach: no search, no
public reading, no analytics, no mobile client. These bound the system's applicability
and are stated as such in Section 8.3 rather than being minimised.

Third, no system in the comparison occupies the position of low installation complexity
combined with full data ownership and native social features. That vacancy is the design
space Weblogr addresses.

## 2.6 Synthesis and Gap

The review supports four conclusions.

1. **A structural gap exists between hosted convenience and self-hosted control.** The
   four systems reviewed cluster at the extremes. The user described in Section 1.2 —
   wanting ownership without administration — is served by none of them well.

2. **Native social engagement in a lightweight self-hosted system is uncommon.** Where
   such features exist in self-hosted platforms they are typically bolted on through
   extensions, with the integration, consistency and maintenance costs that implies.

3. **Framework-free construction is under-examined empirically.** The literature
   establishes conclusively what frameworks provide and why layered architectures are
   preferable. It is less rich in concrete accounts of what specifically fails when a
   real application is built without them. This project's systematic testing against the
   OWASP framework produces exactly such an account.

4. **Security and usability must be assessed, not asserted.** The review establishes
   both the threat framework (2.3.1) and the measurement instruments (2.4.2). Chapters 6
   and 7 apply them rather than making unevidenced claims of the form "the system is
   secure" or "the system is easy to use" — claims which, absent evidence, are worthless.

## 2.7 Requirements Derived from the Review

The review yields the following requirements, formalised with identifiers in Section 4.2:

| Source | Derived requirement |
|---|---|
| §2.5.5 — social features absent or plugin-dependent in self-hosted systems | Native comments, likes, following, notifications and reporting against a single schema |
| §2.5.1 — WordPress installation and maintenance burden | Minimal dependencies; deployable on a standard LAMP stack without package management for application code |
| §2.5.2 — Medium's constraints on control | Self-hosted deployment with the operator retaining full database access |
| §2.2.4 — normalisation theory | Schema in third normal form, with referential integrity enforced by the database |
| §2.3.1 A03 — injection | All database access via parameterised statements |
| §2.3.1 A01 — broken access control | Server-side authorisation on every request touching a user-owned object |
| §2.3.1 A02 / §2.3.2 — credential handling | Passwords stored using an adaptive hash; OTPs random, expiring, rate-limited and session-bound |
| §2.3.1 XSS | Contextual output encoding at every point where stored data is rendered |
| §2.3.1 CSRF | Synchroniser tokens on state-changing requests; POST for all state changes |
| §2.3.3 — upload risk | Extension allow-list, content-based type verification, server-generated filenames, size limit |
| §2.4.1 — Nielsen heuristics | Visible system status, consistent interaction patterns, error prevention in forms |
| §2.4.3 — WCAG 2.1 | Text alternatives, keyboard operability, labelled inputs, visible focus |

Chapter 6 reports the extent to which each was satisfied. The distance between this
table and those results is the empirical core of the project's second contribution, and
is analysed in Section 8.2.
