# Chapter 1 — Introduction

## 1.1 Background

The weblog, or blog, emerged in the late 1990s as a chronologically ordered personal
publishing format and has since become one of the most durable genres of web content.
Its persistence is explained by a low barrier to entry: a blog requires no editorial
gatekeeper, no fixed publication schedule, and no specialist technical knowledge of the
author. What began as a format for personal journals now underpins corporate
communication, technical documentation, journalism, education and community organisation.

The software supporting this activity has consolidated significantly. Contemporary blog
authors typically choose between two models. The first is the **hosted platform** —
services such as Medium, Blogger, Substack and Tumblr — where the provider operates all
infrastructure and the author writes into a managed environment. The second is the
**self-hosted content management system**, of which WordPress is overwhelmingly dominant,
with Ghost, Drupal and Joomla occupying smaller shares. Here the author installs and
maintains the software on infrastructure they control.

Each model resolves the same tension differently. Hosted platforms optimise for
immediacy and require the author to accept the provider's terms regarding content
ownership, monetisation, audience access and algorithmic distribution. Self-hosted
systems optimise for control and require the author to accept responsibility for
installation, database provisioning, security patching, backup and plugin management.
WordPress in particular has grown into a general-purpose content management framework;
an author who wants only to publish articles and interact with readers inherits a
plugin architecture, a theme system, a taxonomy engine and a REST API that they will
never use, along with the maintenance and attack surface those subsystems carry.

## 1.2 Problem Statement

There exists a class of user for whom neither model is well matched: the individual
author or small community that requires blog publishing with social interaction, wants
to retain control of its data, and lacks either the technical capacity or the appetite
to administer a general-purpose content management system.

For such a user the hosted model concedes too much control, while the self-hosted CMS
model imposes operational complexity that is disproportionate to the requirement. The
practical consequence is that the user either accepts a hosted platform's constraints or
maintains software substantially larger than their need.

This project addresses the resulting question:

> **To what extent can a focused, minimal-dependency web application deliver the
> publishing and community-engagement capabilities that individual and small-community
> blog authors require, and what are the architectural, security and usability
> consequences of building such a system without an application framework?**

The second clause is essential and distinguishes this project from a straightforward
implementation exercise. The decision to build without a framework — justified in
Section 3.4 — is treated here as an experimental condition rather than merely a
technical preference. Frameworks such as Laravel and Symfony supply parameterised query
builders, automatic output escaping, CSRF token management, role-based authorisation
middleware, session hardening and a routing layer that enforces separation of concerns.
Building without them makes the resulting system's architecture and data flow fully
explicit, which is pedagogically valuable, but it transfers responsibility for every one
of those protections to the developer. A rigorous account of which of those
responsibilities were discharged successfully and which were not is a legitimate and
transferable finding, and it is one of the two contributions this report claims.

## 1.3 Aims and Objectives

### 1.3.1 Aim

To design, implement and critically evaluate Weblogr, a web-based blog publishing
platform providing content authoring, categorised discovery and social engagement for
individual and small-community authors, and to analyse the engineering consequences of
implementing it as a framework-free PHP application.

### 1.3.2 Objectives

The aim is decomposed into seven objectives. Each is measurable, and each maps to the
chapter in which it is discharged.

| # | Objective | Success criterion | Chapter |
|---|-----------|-------------------|---------|
| O1 | Analyse comparable blog and CMS platforms to establish a baseline feature set and identify their limitations for the target user | A structured feature comparison of at least four systems, yielding an explicit requirement set | 2 |
| O2 | Specify functional and non-functional requirements for the platform | At least 20 functional and 6 non-functional requirements, each uniquely identified, prioritised and traceable | 4 |
| O3 | Design a normalised relational schema and a layered application architecture | An ERD in at least third normal form, a documented architecture, and supporting UML models | 4 |
| O4 | Implement the specified requirements as a working web application | A deployable system exercising every specified functional requirement | 5 |
| O5 | Verify the implementation against its specification through systematic functional testing | A documented test suite with per-requirement traceability and recorded outcomes | 6 |
| O6 | Evaluate the system's usability with representative users | A task-based study with a standardised instrument (SUS), reporting quantitative results | 7 |
| O7 | Critically appraise the design decisions, in particular the framework-free architecture, and identify remediation priorities | An evidence-based discussion of limitations grounded in the results of O5 and O6 | 8 |

## 1.4 Scope

### 1.4.1 In Scope

The delivered system provides:

- **Account management** — self-service registration; email address verification by
  six-digit one-time password; credential-based login; password recovery by email OTP;
  session-based authentication; logout.
- **Profile management** — display name, biography and profile photograph; aggregate
  counts of posts, followers and accounts followed.
- **Content authoring** — creation of posts comprising a title, body text, a single
  image and one of seven categories; saving posts as private drafts; promoting a draft
  to a published post; editing and deleting posts and drafts.
- **Content discovery** — a reverse-chronological feed of all published posts, filterable
  by category and by author, and sortable by publication date or by like count.
- **Social engagement** — commenting on posts; liking posts and comments; following
  other authors; an in-application notification feed generated by follow, like and
  comment events.
- **Moderation** — user submission of reports against posts; an administrative view
  listing submitted reports and permitting post removal.

### 1.4.2 Out of Scope

The following were excluded by deliberate decision, and the rationale is recorded here
so that their absence is not read as oversight:

- **Rich-text and Markdown authoring.** Post bodies are plain text. Rich text would
  require an editor component and a sanitisation pipeline for author-supplied HTML;
  the latter is a substantial security problem in its own right and would have consumed
  time allocated to the core feature set.
- **Full-text search.** Discovery is by category, author and ordering. Search would
  require either full-text indexing or an external search service.
- **Multi-image and video content.** One image per post.
- **Native mobile applications.** The system is a web application. A REST API layer,
  which a mobile client would require, is discussed as future work in Section 8.4.
- **Public unauthenticated reading.** All content requires authentication to view. This
  is a consequence of the session guard placed on the feed rather than a considered
  product decision, and it is discussed critically in Section 8.3.2.
- **Analytics and audience statistics.** The marketing copy on the static landing page
  (`index.html`) advertises performance analytics; this feature was not implemented.
  The discrepancy is acknowledged in Section 8.3.4 and its removal is recommended.
- **Monetisation, subscriptions, email digests, and internationalisation.**

### 1.4.3 Assumptions

1. Users have an operational email account reachable by SMTP, since account verification
   and password recovery both depend on email delivery.
2. Users access the system with a modern browser supporting HTML5, CSS3 and ES6.
3. The deployment target is a single-server LAMP/WAMP stack. Horizontal scaling is not
   assumed and is discussed as a limitation in Section 8.3.5.
4. The expected concurrent user population is small — of the order of tens rather than
   thousands. Design decisions predicated on this assumption, and their consequences,
   are examined in Section 8.3.5.

## 1.5 Contributions

This report claims two contributions:

1. **A working artefact.** Weblogr implements a coherent publishing and engagement
   feature set across eight relational entities and twenty-eight server-side scripts,
   validated against a sixty-one-case functional test suite and evaluated with
   representative users.

2. **An empirical account of framework-free web development.** By testing the artefact
   systematically against its own specification and against the OWASP Top 10, this
   project produces concrete evidence of which classes of defect arise when a data-driven
   application is built without framework-provided protections. Chapter 6 reports these
   findings, and Section 8.2 argues that their distribution is not random but
   concentrated in precisely those areas a framework would have handled by default. This
   is a transferable finding of more general interest than the artefact itself.

## 1.6 Report Structure

**Chapter 2** reviews the literature on content management architecture, web application
security and usability measurement, and presents a structured comparison of four related
systems from which the requirement set is derived.

**Chapter 3** justifies the choice of an iterative and incremental process model, the
technology stack, and the framework-free architectural decision, and states the research
methods used for evaluation.

**Chapter 4** presents the requirements specification and the system design: use cases,
the entity relationship model, the architecture, data flow models, interaction sequences
and the interface design.

**Chapter 5** describes the implementation, organised by subsystem, with reference to the
source files that realise each design element. It gives particular attention to the
authentication and notification mechanisms, and documents the security controls that
were implemented and those that were not.

**Chapter 6** presents the test strategy, the test suite, and the results, including a
full register of defects identified.

**Chapter 7** presents the usability evaluation: study design, participants, tasks,
instrument and results.

**Chapter 8** concludes by assessing each objective against its success criterion,
analysing the limitations of the work, and setting out a prioritised programme of future
work.
