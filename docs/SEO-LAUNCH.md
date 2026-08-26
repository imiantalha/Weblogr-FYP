# Weblogr SEO launch checklist

The current production site is deployed at **https://weblogr.up.railway.app/**. This repository contains the technical SEO foundation. Ranking is still dependent on the production domain, crawlability, useful content, links, and search-engine indexing.

## Before launch

- Set `APP_URL=https://weblogr.up.railway.app` in the production environment.
- Confirm `robots.txt` is reachable at `https://weblogr.up.railway.app/robots.txt`.
- Confirm `https://weblogr.up.railway.app/sitemap.php` returns valid XML and uses the production host.
- Confirm `https://weblogr.up.railway.app/seo-links.html` is reachable and describes Muhammad Talha consistently.
- Confirm `https://weblogr.up.railway.app/.well-known/security.txt` is reachable.
- Use one canonical production hostname and redirect alternate hosts to it.
- Keep registration, authenticated pages, admin tools, database files, uploads, and internal endpoints out of search results.

## Search engines

1. Verify `https://weblogr.up.railway.app/` in Google Search Console.
2. Submit `https://weblogr.up.railway.app/sitemap.php`.
3. Verify `https://weblogr.up.railway.app/` in Bing Webmaster Tools.
4. Submit the same sitemap.
5. Inspect the homepage and creator profile URL after deployment.
6. Request indexing only after the production URLs return `200` and HTTPS is working.

## Content strategy

Weblogr should earn search visibility through real public content rather than keyword stuffing. When public posts are introduced, give every public post a stable URL, unique title, useful description, canonical URL, Open Graph metadata, author information, publication/update dates, and `BlogPosting` structured data.

Do not add fake stories or sample records to the production database merely for SEO.

## Creator identity

Keep the following profiles consistent across the site and external profiles:

- https://imiantalha.vercel.app/
- https://github.com/imiantalha
- https://www.fiverr.com/imiantalha
- https://www.upwork.com/freelancers/~0129afd82850749f05?viewMode=1

## Performance and accessibility

Before launch, test mobile layout, keyboard navigation, page titles, image alternative text, Core Web Vitals, compressed assets, HTTPS, and server error handling.
