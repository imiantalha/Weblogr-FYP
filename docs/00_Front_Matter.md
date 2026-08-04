# Weblogr

## A Web-Based Blog Publishing and Community Engagement Platform

**Muhammad Talha**
**BC200407363**

Submitted in partial fulfilment of the requirements for the degree of
**BS Computer Science / Software Engineering**

**Virtual University of Pakistan**

**2024**

---

## Declaration

I declare that this report and the accompanying software artefact are my own work,
except where explicitly acknowledged through citation or attribution. The third-party
library PHPMailer is used under its LGPL-2.1 licence and is credited in Section 5.2.4
and Appendix C. Font Awesome is used for interface iconography under its free licence.
No part of this work has been submitted for any other academic award.

Signed: _Muhammad Talha_  Date: _Aug, 2024_

---

## Acknowledgements


---

## Abstract

Personal publishing on the web is dominated by a small number of large platforms.
Hosted services such as Medium and Blogger require authors to surrender control of
their content and audience, while self-hosted content management systems such as
WordPress and Ghost offer control at the cost of substantial installation, configuration
and maintenance overhead. Between these two extremes sits a class of user — the
individual author or small community that wants to publish and interact socially,
without administering a general-purpose CMS.

This project presents **Weblogr**, a web-based blog publishing platform developed to
investigate how a focused, minimal-dependency application can deliver the publishing and
community features such users need. Weblogr was implemented using PHP 8.2, MariaDB and
vanilla client-side technologies, deliberately avoiding an application framework in order
to make the architecture and data flow of the system explicit and analysable.

The system delivers twelve functional capabilities: user registration with email
one-time-password (OTP) verification, password recovery, session-based authentication,
blog post creation with image upload and categorisation, a draft workflow, post editing
and deletion, a multi-criteria filtering and sorting feed, commenting, a like
mechanism for posts and comments, a follower graph with notification fan-out, a
user-submitted content reporting mechanism, and an administrative moderation view.
The relational schema comprises eight tables.

Development followed an iterative and incremental process model. Requirements were
elicited from an analysis of comparable systems and from the security and usability
literature, and expressed as twenty-three functional and fourteen non-functional
requirements. The implementation was verified against a sixty-one-case functional test
suite, a performance measurement and a structured security assessment against the OWASP
Top 10, and evaluated with users through task-based observation and the System Usability
Scale.

Testing established that the system satisfies nineteen of its twenty-three functional
requirements in whole or in part, and identified twenty-three defects concentrated in
authorisation enforcement, output encoding and request forgery protection. The
distribution of those defects is the report's central finding: they cluster in
cross-cutting concerns — controls that must be applied uniformly at many independent
points — and are largely absent from localised ones, with fewer than a fifth arising from
a gap in knowledge rather than a gap in coverage. The principal contribution of this work
is therefore twofold: a working publishing platform, and evidence-based analysis of the
specific failure modes that arise when a data-driven web application is constructed
without a framework's built-in security and architectural affordances.

**Keywords:** blog platform, content management, PHP, relational database design,
web application security, usability evaluation, software testing

---

## Table of Contents

1. Introduction
2. Literature Review and Related Systems
3. Methodology
4. Requirements and System Design
5. Implementation
6. Testing and Verification
7. Evaluation
8. Conclusion and Future Work
   References
   Appendix A — Use Case Specifications
   Appendix B — Complete Data Dictionary
   Appendix C — Third-Party Components, Attribution and Deployment
   Appendix D — Evaluation Instruments
   Appendix E — Complete Functional Test Suite
   Appendix F — Performance Measurement Harness
   Appendix G — Source Code

---

## List of Figures

| Figure | Title | Page |
|--------|-------|------|
| 3.1 | Iterative and incremental development process | |
| 3.2 | Project timeline (Gantt chart) | |
| 4.1 | Use case diagram | |
| 4.2 | Three-tier system architecture | |
| 4.3 | Entity relationship diagram | |
| 4.4 | Context diagram (DFD Level 0) | |
| 4.5 | Data flow diagram (Level 1) | |
| 4.6 | Sequence diagram — registration and OTP verification | |
| 4.7 | Sequence diagram — post publication and notification fan-out | |
| 4.8 | Sequence diagram — comment submission | |
| 4.9 | Post lifecycle state diagram | |
| 4.10 | Navigation map | |
| 5.1 | Landing page | |
| 5.2 | Registration and OTP verification screens | |
| 5.3 | Main feed with filter controls | |
| 5.4 | Post composition screen | |
| 5.5 | Comments view | |
| 5.6 | User profile | |
| 5.7 | Administrative moderation view | |
| 6.1 | Defect distribution by root cause | |
| 7.1 | Task completion rates | |
| 7.2 | SUS score distribution | |

> Figures 5.1–5.7 are screenshots you must capture from your running
> instance, and 6.1, 7.1 and 7.2 are charts you must produce from the data in Tables 6.8,
> 7.3 and 7.7. Figure 6.1 plots the four root-cause groups of §6.8.3 as a bar chart —
> it is the visual statement of the report's central argument and is worth including.
> Every other figure has its source embedded as a Mermaid or PlantUML block in the
> relevant chapter.

## List of Tables

| Table | Title | Page |
|-------|-------|------|
| 2.1 | Feature comparison of related systems | |
| 4.1 | Functional requirements | |
| 4.2 | Non-functional requirements | |
| 4.3 | Data dictionary (summary) | |
| 6.1 | Test case distribution by module | |
| 6.2 | Cases exposing defects | |
| 6.3 | Requirement traceability matrix | |
| 6.4 | Compatibility results | |
| 6.5 | Mean server-side response time | |
| 6.6 | Assessment against OWASP Top 10 (2021) | |
| 6.7 | Authorisation control coverage | |
| 6.8 | Consolidated defect register | |
| 7.1 | Participant characteristics | |
| 7.2 | Evaluation tasks | |
| 7.3 | Task completion | |
| 7.4 | Time on task | |
| 7.5 | Errors by task | |
| 7.6 | SUS item responses | |
| 7.7 | SUS scores | |
| 7.8 | Usability problems by severity | |
| 7.9 | Objective attainment | |
| 7.10 | Comparative position against related systems | |

## Abbreviations

| Term | Expansion |
|------|-----------|
| CMS | Content Management System |
| CRUD | Create, Read, Update, Delete |
| CSPRNG | Cryptographically Secure Pseudo-Random Number Generator |
| CSRF | Cross-Site Request Forgery |
| DDL | Data Definition Language |
| DFD | Data Flow Diagram |
| ERD | Entity Relationship Diagram |
| FK | Foreign Key |
| FR | Functional Requirement |
| GDPR | General Data Protection Regulation |
| IDOR | Insecure Direct Object Reference |
| IQR | Interquartile Range |
| LGPL | GNU Lesser General Public License |
| MoSCoW | Must have, Should have, Could have, Won't have |
| MVC | Model–View–Controller |
| NFR | Non-Functional Requirement |
| OTP | One-Time Password |
| OWASP | Open Worldwide Application Security Project |
| PDO | PHP Data Objects |
| PK | Primary Key |
| REST | Representational State Transfer |
| RFC | Request for Comments |
| SDLC | Software Development Life Cycle |
| SMTP | Simple Mail Transfer Protocol |
| SQL | Structured Query Language |
| SUS | System Usability Scale |
| TLS | Transport Layer Security |
| UML | Unified Modeling Language |
| WCAG | Web Content Accessibility Guidelines |
| XSS | Cross-Site Scripting |
