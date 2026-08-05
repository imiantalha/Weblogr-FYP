# Weblogr — Final Year Project Report

This folder contains the FYP report for **Weblogr**, written by reverse-engineering the
implementation in this repository. Every technical claim is traceable to a source file.

## Files

| File | Chapter | Target words |
|------|---------|--------------|
| `00_Front_Matter.md` | Title, abstract, declaration, contents | ~800 |
| `01_Introduction.md` | Ch 1 — Introduction | ~1,800 |
| `02_Literature_Review.md` | Ch 2 — Literature Review & Related Systems | ~3,000 |
| `03_Methodology.md` | Ch 3 — Methodology | ~2,000 |
| `04_Requirements_And_Design.md` | Ch 4 — Requirements & System Design | ~3,500 |
| `05_Implementation.md` | Ch 5 — Implementation | ~3,200 |
| `06_Testing.md` | Ch 6 — Testing | ~2,400 |
| `07_Evaluation.md` | Ch 7 — Evaluation & Results | ~2,000 |
| `08_Conclusion.md` | Ch 8 — Conclusion & Future Work | ~1,600 |
| `09_References.md` | References | — |
| `10_Appendices.md` | Appendices | — |

Approx. 20,000 words when assembled. **All chapters are drafted.**

## ⚠️ Before you submit — read this

Search for `[[ACTION` and resolve every hit. In priority order:

0. **Revoke the SMTP application password** found committed in `registration/mail.php`
   and `registration/pass_mail.php` (Google Account → Security → 2-Step Verification →
   App passwords). This is a live credential in a pushed repository. Deleting the line is
   not sufficient — it remains in git history. Do this before anything else. Recorded in
   §6.7.1 and Appendix G.

1. **Chapter 7 contains no results data.** The full study design is written — protocol,
   participant brief, task list, SUS questionnaire, consent form, and empty results
   tables. You must run it and enter real numbers. Do not invent participants or scores;
   fabricated data is academic misconduct and is trivially exposed in a viva. If you
   cannot run it before the deadline, say so plainly in §7.7 and mark O6 as not met — an
   unexecuted design honestly declared is credited; invented data fails the project.

2. **Re-run the Chapter 6 tests against your deployed instance.** The results in Tables
   6.2, 6.5 and 6.6 reflect the code as reviewed. Correct any row whose outcome differs.
   Table 6.5 (performance) is empty — use the harness in Appendix F.

3. **Complete Appendix E.** Table 6.2 documents the 15 failing cases in full; the
   remaining 46 passing cases are listed by title and need their steps, data and results
   filled in.

4. **Verify every reference in `09_References.md` before citing it.** Confirm edition,
   year and publisher; format in your institution's required style; **remove anything you
   have not read.** Add at least five peer-reviewed papers from 2018 onward and cite them
   in Chapter 2 — the list is currently standards- and textbook-heavy.

5. **Fill in the placeholders** — your name, student ID, supervisor, institution,
   submission date, module code, ethics reference, machine specification, and the
   screenshot figures (§4.8).

## Assembling into one document

Markdown → Word/PDF via [Pandoc](https://pandoc.org):

```bash
pandoc 00_Front_Matter.md 01_Introduction.md 02_Literature_Review.md \
       03_Methodology.md 04_Requirements_And_Design.md 05_Implementation.md \
       06_Testing.md 07_Evaluation.md 08_Conclusion.md 09_References.md \
       10_Appendices.md \
  --toc --number-sections -o Weblogr_FYP_Report.docx
```

Then apply your university's template (margins, line spacing, font, header/footer,
page numbering) in Word. Most institutions require a specific template — check your
handbook before formatting.

## Diagrams

Diagram sources are embedded as [Mermaid](https://mermaid.live) and PlantUML code
blocks in Chapter 4. To produce images for the report:

- **Mermaid** — paste into <https://mermaid.live>, export PNG/SVG.
- **PlantUML** — paste into <https://www.plantuml.com/plantuml>, export PNG/SVG.
- Alternatively redraw in draw.io if your handbook requires a specific notation.

Export each at 300 DPI minimum, place in `docs/figures/`, and caption them
"Figure 4.x — ..." consistently.

## A note on tone

Chapter 6 reports the defects found during testing rather than claiming the system is
flawless, and Chapter 8 discusses them as limitations with a remediation plan. This is
deliberate. Examiners test the artefact; undisclosed defects that they discover
themselves are far more damaging than defects you identified, documented, and analysed.
Self-identified defects demonstrate testing rigour and earn marks in the Testing and
Critical Evaluation criteria.

The report's spine is the argument in §6.8.3 and §8.2: the 23 defects cluster in
cross-cutting concerns (escaping, authorisation, CSRF) and are sparse in localised ones
(hashing, parameterisation within one subsystem), and fewer than a fifth arise from not
knowing what to do. That is a genuine, transferable finding, and it converts what would
otherwise read as a list of mistakes into the project's main contribution. **Do not soften
it** — if you remove the defect register, the argument in Chapter 8 has no evidence and
the report loses more than it gains.

## Likely viva questions, and where the answers are

| Question | Answer in |
|---|---|
| Why not use a framework? | §3.4, §8.2.2 |
| Your system has SQL injection — did you know? | §5.4.1, §6.6, §8.4 item 4 |
| Why are there no unit tests? | §3.6, §6.2.1, §8.2.1 |
| Is a blog platform novel enough for an FYP? | §1.2 (the research question is the framework-free consequence, not the blog), §1.5 |
| Why can't people read posts without an account? | §8.3.2 — acknowledged as the most serious functional gap |
| Is a SUS score of *n* a percentage? | §7.3.4 — it is not |
| Why no statistical significance test? | §7.9, §8.3.7 — n < 10 does not support one |
| Your repository has one commit — did you really iterate? | §8.3.9 — declared as a process limitation |
| The landing page advertises analytics you didn't build. | §1.4.2, §8.3.4 — acknowledged, removal recommended |
| Why is the database schema latin1 when the pages are UTF-8? | D-12, §5.7.2, §8.4 item 11 |
