# Chapter 7 — Evaluation

> ## ⚠ THIS CHAPTER CONTAINS NO DATA YET
>
> Every results table below is deliberately empty. **Do not fabricate values.** Invented
> participant data is academic misconduct, and it is the single easiest thing for an
> examiner to detect — fabricated SUS responses have implausibly low variance,
> fabricated qualitative quotes read uniformly, and a viva question as simple as "what did
> participant 4 struggle with?" cannot be answered.
>
> What follows is a complete, ready-to-run study design. Recruit participants, run the
> sessions, and fill in the marked slots. If you cannot run the study before the deadline,
> **keep the design and state plainly in §7.7 that it was not executed and why** — a
> rigorous unexecuted design with an honest statement of that fact is credited; invented
> data, if detected, fails the project.
>
> Minimum viable version if time is short: 5 participants, 6 tasks, SUS only, one
> afternoon. That is enough to report.

## 7.1 Introduction

Chapter 6 established what the system does. This chapter evaluates how well it serves its
users and whether it meets its objectives, discharging objective O6. Section 7.2 states
the evaluation questions. Section 7.3 gives the study design. Sections 7.4 to 7.6 report
task performance, perceived usability and qualitative findings. Section 7.7 evaluates the
system against its objectives and Section 7.8 against the comparable systems of Section
2.5.

## 7.2 Evaluation Questions

| ID | Question | Instrument | Reported in |
|---|---|---|---|
| EQ1 | Can users unfamiliar with the system complete core publishing and engagement tasks without assistance? | Task-based observation | §7.4 |
| EQ2 | How do users rate the system's usability relative to an established benchmark? | System Usability Scale | §7.5 |
| EQ3 | Which interface elements cause difficulty, and why? | Think-aloud protocol; post-task interview | §7.6 |
| EQ4 | Does the delivered system satisfy the objectives of Section 1.3? | Requirements and test evidence | §7.7 |
| EQ5 | How does it compare with existing platforms on the dimensions of Table 2.1? | Comparative analysis | §7.8 |

EQ1–EQ3 require participants. EQ4 and EQ5 are answerable from Chapters 4 and 6 and are
written below without pending data.

## 7.3 Study Design

### 7.3.1 Participants

Target: **8–10 participants**, purposively sampled to include both people who have
published a blog before and people who have not, since the two groups encounter the
authoring interface differently.

**Table 7.1 — Participant characteristics**

| P | Age band | Technical background | Prior blogging | Prior use of Weblogr |
|---|---|---|---|---|
| P1 | | | | None |
| P2 | | | | None |
| P3 | | | | None |
| P4 | | | | None |
| P5 | | | | None |
| P6 | | | | None |
| P7 | | | | None |
| P8 | | | | None |

> **[[ACTION]]** Complete after recruitment. Use age *bands* (18–24, 25–34, …) and
> general descriptors, never names or identifying detail.

Sample size is discussed as a limitation in Section 7.9. It is adequate for identifying
usability problems — Nielsen's observation that five evaluators surface the majority of
issues (§2.4.2) applies — and inadequate for any inferential claim, which is why none is
made.

### 7.3.2 Procedure

Each session ran approximately 30 minutes on the researcher's machine with a clean
database instance seeded per Section 6.2.3:

1. **Briefing (3 min)** — purpose explained, information sheet provided, written consent
   obtained, right to withdraw stated. Participants were told the system was being
   evaluated, not them.
2. **Task sequence (18 min)** — the tasks of Table 7.2, attempted without assistance.
   Participants were asked to think aloud. Intervention occurred only after a participant
   stated they could not proceed, and was recorded as a failure.
3. **SUS questionnaire (4 min)** — completed independently, without the researcher
   present, to reduce acquiescence bias.
4. **Semi-structured interview (5 min)** — four open questions (Appendix D).

Session order was not counterbalanced, as the tasks are sequentially dependent
(registration precedes publishing). Learning effects across tasks are therefore
confounded with task difficulty, and this is stated in Section 7.9.

### 7.3.3 Tasks

**Table 7.2 — Evaluation tasks**

| ID | Task | Requirements exercised | Success criterion |
|---|---|---|---|
| T1 | Register an account and verify it by email | FR-01, FR-02 | Reaches the authenticated feed |
| T2 | Publish a post with a title, category and image | FR-07 | Post visible in the feed |
| T3 | Begin a post, save it as a draft, then publish it later | FR-08, FR-09 | Post published from the draft list |
| T4 | Find all posts in a chosen category, then the most-liked post | FR-14, FR-15 | Correct filtered and sorted views reached |
| T5 | Comment on another author's post and like a comment | FR-16, FR-18 | Comment appears; like registered |
| T6 | Follow an author and locate the resulting notification | FR-19, FR-20 | Notification found |
| T7 | Edit your profile display name and picture | FR-21 | Change reflected on the profile |
| T8 | Report a post you consider inappropriate | FR-22 | Report submitted |

Tasks were stated as goals, not as instructions ("publish a post about a hobby", not
"click New Post"), so that navigation discoverability was itself under test.

### 7.3.4 Measures

- **Task completion** — completed unaided / completed after intervention / abandoned.
- **Time on task** — from task statement to success criterion, by stopwatch.
- **Error count** — actions not on a path to the goal (wrong navigation, form
  resubmission, use of the back button to recover).
- **SUS** — ten items, five-point Likert. Scored per Brooke (1996): subtract 1 from
  odd-numbered item scores, subtract even-numbered scores from 5, sum, multiply by 2.5.
  Yields 0–100; benchmark mean ≈ 68 (§2.4.2). **The score is not a percentage** — a
  common misinterpretation an examiner may probe.
- **Think-aloud utterances** — recorded verbatim, coded thematically.

### 7.3.5 Ethics

Written informed consent was obtained; participation was voluntary and withdrawable
without consequence; no personal data beyond age band and technical background was
recorded; participants are identified only as P1–Pn; and sessions used disposable test
accounts on a local instance, so no participant published content to any live service.
The information sheet and consent form are in Appendix D.

Ethical approval: **[[ACTION: reference number, or the statement that departmental policy
exempts low-risk usability studies]]**.

## 7.4 Task Performance (EQ1)

**Table 7.3 — Task completion**

| Task | Unaided | After intervention | Abandoned | Completion rate |
|---|---|---|---|---|
| T1 Register and verify | | | | |
| T2 Publish a post | | | | |
| T3 Draft and publish | | | | |
| T4 Filter and sort | | | | |
| T5 Comment and like | | | | |
| T6 Follow and notification | | | | |
| T7 Edit profile | | | | |
| T8 Report a post | | | | |
| **Overall** | | | | |

**Table 7.4 — Time on task (seconds)**

| Task | Min | Max | Median | Mean | IQR |
|---|---|---|---|---|---|
| T1 | | | | | |
| T2 | | | | | |
| T3 | | | | | |
| T4 | | | | | |
| T5 | | | | | |
| T6 | | | | | |
| T7 | | | | | |
| T8 | | | | | |

Report the **median and interquartile range** as the primary statistics, not the mean and
standard deviation. Time-on-task distributions are right-skewed — one struggling
participant inflates the mean — and with n < 10 the median is the more honest summary.

**Table 7.5 — Errors by task**

| Task | Total errors | Participants affected | Most frequent error |
|---|---|---|---|
| T1 | | | |
| T2 | | | |
| T3 | | | |
| T4 | | | |
| T5 | | | |
| T6 | | | |
| T7 | | | |
| T8 | | | |

> **[[ACTION: §7.4.1 Analysis]]** Write 250–350 words. Identify the tasks with the lowest
> completion rate and the highest error count, and *explain the mechanism* rather than
> restating the numbers. Anticipate two findings the design predicts, and confirm or
> refute each against your data:
>
> 1. **T6 (follow and notification) and T4 (filter and sort)** are likely to show the
>    highest error counts, because both depend on the icon-only sidebar controls
>    identified in Section 4.8.1 as carrying no text label. If participants hover, pause,
>    or click the wrong icon, say so and cite it as evidence for the recommendation in
>    Section 8.3.3.
> 2. **T1 (registration)** may show high times without high errors, since it includes
>    waiting for an email to arrive. Separate interaction time from waiting time, or the
>    figure measures your mail provider rather than your interface.
>
> If a defect from Table 6.8 surfaced during a session — a participant liking a post and
> seeing no change (D-09), or reaching an empty "following" list (D-07) — report it here
> as independent confirmation. A defect found by inspection *and* observed to affect a
> real user is a stronger finding than either alone.

## 7.5 Perceived Usability (EQ2)

**Table 7.6 — SUS item responses** (1 = strongly disagree, 5 = strongly agree)

| Item | P1 | P2 | P3 | P4 | P5 | P6 | P7 | P8 | Mean |
|---|---|---|---|---|---|---|---|---|---|
| 1. I would like to use this system frequently | | | | | | | | | |
| 2. I found the system unnecessarily complex | | | | | | | | | |
| 3. I thought the system was easy to use | | | | | | | | | |
| 4. I would need technical support to use this system | | | | | | | | | |
| 5. The functions were well integrated | | | | | | | | | |
| 6. There was too much inconsistency | | | | | | | | | |
| 7. Most people would learn this system quickly | | | | | | | | | |
| 8. I found the system cumbersome to use | | | | | | | | | |
| 9. I felt confident using the system | | | | | | | | | |
| 10. I needed to learn a lot before I could get going | | | | | | | | | |

**Table 7.7 — SUS scores**

| Participant | Score |
|---|---|
| P1 – P8 | |
| **Mean** | |
| **Median** | |
| **Std. dev.** | |
| **Range** | |

> **[[ACTION: §7.5.1 Interpretation]]** Write 200–300 words. State the mean score,
> compare it to the benchmark of 68, and place it on the adjective scale (Bangor et al.
> 2009: < 51 poor, 51–68 OK, 68–80.3 good, > 80.3 excellent). Three requirements:
>
> - **Report the spread, not only the mean.** A mean of 72 from scores of 70–74 and a mean
>   of 72 from scores of 45–95 mean entirely different things. If the range is wide,
>   examine whether it splits by prior blogging experience — a genuine finding if it does.
> - **Do not perform a significance test.** With n < 10 no inferential claim is warranted
>   (§3.7). Report descriptive statistics and say explicitly that no inferential test was
>   performed because the sample does not support one. Stating this pre-empts the viva
>   question rather than inviting it.
> - **Reconcile SUS with §7.4.** If completion rates were high but SUS was middling, or
>   the reverse, explain the discrepancy — participants often rate a system they completed
>   tasks on poorly if the route felt uncertain. That reconciliation is the analysis;
>   the numbers alone are not.

## 7.6 Qualitative Findings (EQ3)

Think-aloud utterances and interview responses were coded thematically. Four themes were
anticipated from the design; report the themes your data actually produced, whether or not
they match.

> **[[ACTION: §7.6.1–7.6.4]]** For each theme write 150–250 words with **at least one
> verbatim quotation** attributed to a participant identifier. Anticipated themes:
>
> **Theme 1 — Navigation discoverability.** The icon-only sidebar (§4.8.1). Did
> participants know what each icon did before clicking?
>
> **Theme 2 — Feedback on action.** Nielsen's visibility of system status (§2.4.1). Post
> publication redirects to the feed with no confirmation; the like count does not update
> (D-09). Did participants know their action had succeeded?
>
> **Theme 3 — The draft model.** Did participants understand that a draft is separate from
> a published post, and could they find their drafts again? This tests whether the
> two-relation design of Section 4.9.1 is visible to users as complexity.
>
> **Theme 4 — Trust and control.** Did anything cause hesitation — the absence of an
> unfollow control, deletion without an undo, uncertainty about who can see a report?

**Table 7.8 — Usability problems identified, by severity**

| ID | Problem | Heuristic violated (§2.4.1) | Participants affected | Severity |
|---|---|---|---|---|
| U-01 | | | | |
| U-02 | | | | |
| U-03 | | | | |

Rate severity on Nielsen's 0–4 scale (0 not a problem, 4 usability catastrophe) using
frequency, impact and persistence. Carry every problem rated 3 or 4 into Section 8.4 as a
recommendation; a problem identified and not acted on is an incomplete finding.

## 7.7 Evaluation Against Objectives (EQ4)

This section is answerable from Chapters 4 and 6 and is stated now.

**Table 7.9 — Objective attainment**

| Obj. | Statement | Evidence | Attainment |
|---|---|---|---|
| O1 | Analyse comparable blog and CMS platforms to establish a baseline feature set and identify their limitations for the target user | Chapter 2; four systems compared in Table 2.1; requirements traced to sources in Tables 4.1 and 4.2 | **Met** |
| O2 | Specify functional and non-functional requirements with priorities | 23 FRs, 14 NFRs, MoSCoW-prioritised and uniquely identified (§4.2) | **Met** |
| O3 | Design a normalised relational schema and a layered application architecture | 8 relations in 3NF with two documented departures (§4.6.3); three-tier architecture (§4.4); use case, DFD, sequence and state models (§4.3, §4.7) | **Met** |
| O4 | Implement the specified requirements as a working web application | 19 of 23 FRs verified in whole or part; FR-05 and FR-11 not met in specified form (Table 6.3) | **Partially met** |
| O5 | Verify the implementation against its specification through systematic functional testing | 61 functional cases with full traceability (Table 6.3), OWASP assessment, 23 defects registered (Chapter 6) | **Met** |
| O6 | Evaluate the system's usability with representative users | [[ACTION: Met if the study runs; **Not met** and stated as such if it does not]] | |
| O7 | Critically appraise the design decisions, in particular the framework-free architecture, and identify remediation priorities | §6.8.3, §8.2, §8.3, and the prioritised programme in §8.4 | **Met** |

O4 is partially met and saying so is deliberate. FR-05 (password recovery) does not
function as specified, and FR-11 (delete own post) is implemented without the ownership
constraint its specification requires. Two of twenty-three requirements unmet, with the
cause diagnosed and the remediation specified, is a defensible engineering outcome; a
claim that all twenty-three were met would be contradicted by Table 6.3 in the same
document.

O5 and O7 are the objectives on which this project is strongest, and they are strongest
*because* O4 is weakest. The defect distribution of Section 6.8.3 is only available as a
finding because the implementation was built without a framework and then examined
honestly. This is developed in Section 8.2.

## 7.8 Comparative Evaluation (EQ5)

Weblogr is compared against the systems of Section 2.5 on the dimensions of Table 2.1.

**Table 7.10 — Comparative position**

| Dimension | WordPress | Medium | Blogger | Ghost | Weblogr |
|---|---|---|---|---|---|
| Self-hosted / data ownership | Yes | No | No | Yes | **Yes** |
| Installation complexity | High | None | None | Moderate | **Low** |
| External dependencies | Many | n/a | n/a | Many | **Two** |
| Post authoring with media | Yes | Yes | Yes | Yes | **Yes** |
| Draft workflow | Yes | Yes | Yes | Yes | **Yes** |
| Categorisation | Yes | Yes | Yes | Yes | **Yes (fixed set)** |
| Comments | Yes | Yes | Yes | Plugin | **Yes** |
| Likes on posts and comments | Plugin | Yes | No | No | **Yes** |
| Follow authors | Plugin | Yes | No | Yes | **Yes** |
| In-app notifications | Plugin | Yes | No | No | **Yes** |
| Content reporting | Plugin | Yes | Yes | No | **Yes** |
| Moderation interface | Yes | Yes | Yes | Yes | **Partial** |
| Public reading without account | Yes | Yes | Yes | Yes | **No** |
| Responsive to handset widths | Yes | Yes | Yes | Yes | **No** |
| Output escaping by default | Yes | Yes | Yes | Yes | **No** |
| CSRF protection | Yes | Yes | Yes | Yes | **No** |
| Rich-text editing | Yes | Yes | Yes | Yes | **No** |
| Full-text search | Yes | Yes | Yes | Yes | **No** |

### 7.8.1 Analysis

Weblogr's defensible position is *scope*. It delivers the engagement features — likes on
both posts and comments, following, in-app notifications, reporting — that WordPress and
Ghost require plugins for, in a system with two third-party dependencies and no build
step. For the small-community deployment of Section 1.4.3, that combination is not
available off the shelf.

Three deficits are structural rather than cosmetic.

**Public reading requires an account.** Every content view sits behind the session guard
of Section 4.5. A blog platform whose posts cannot be read by an unauthenticated visitor
does not perform the primary function of a blog. This is the most serious functional gap
in the system, more so than any single defect in Table 6.8, and it follows from applying
one uniform access control to every route rather than distinguishing public from protected
resources. Section 8.3.2 addresses it.

**Absence of framework-default security controls.** The two rows on which Weblogr differs
from all four comparators — output escaping and CSRF protection — are exactly the controls
frameworks supply automatically. The comparison table independently reproduces the finding
of Section 6.8.3, from a different direction.

**No handset support.** Confirmed at 375 px in Table 6.4.

The comparison does not support a claim of superiority on any dimension other than
dependency footprint and installation simplicity, and no such claim is made. Its value is
in locating the system precisely: a functionally broad, architecturally simple, security-
immature implementation of a well-understood problem.

## 7.9 Threats to Validity

**Construct validity.** SUS measures perceived usability after brief exposure; it does not
measure whether the system supports sustained authoring over weeks. Task success measures
first-use discoverability, not efficiency in habitual use.

**Internal validity.** The researcher was also the developer and present during sessions,
introducing demand characteristics: participants may have understated difficulty. The
SUS was administered without the researcher present to reduce this, but the interview was
not. Tasks were not counterbalanced (§7.3.2), so learning effects confound task
difficulty.

**External validity.** Participants were purposively rather than randomly sampled and are
not representative of any defined population. Sessions ran on the researcher's machine on
a local instance, so no network conditions or device diversity were represented. Findings
identify usability problems; they do not estimate their prevalence.

**Statistical validity.** With n < 10, no inferential test is warranted and none was
performed. All quantitative results are descriptive. Any apparent difference between
participant subgroups is an observation for future work, not a finding.

**Reliability.** Qualitative coding was performed by a single coder, so no inter-rater
agreement can be reported. A second coder on a subset would be the standard remedy.

Stating these limits does not weaken the evaluation. An evaluation that reported an SUS
mean without acknowledging that eight participants cannot support an inferential claim
would be making a stronger assertion than its data permits, and would invite exactly the
viva question this section forecloses.
