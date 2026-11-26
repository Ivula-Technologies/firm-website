# Hosting the JENIQA site on Vercel

## How it deploys

The Vercel project is linked to this GitHub repository. Every push builds a preview, and every merge to `main` updates the live site. `vercel.json` tells Vercel it is a Next.js app, so no build settings need changing.

## Forms (contact, request a proposal, newsletter)

The forms send email through [Resend](https://resend.com). In Vercel, open **Project > Settings > Environment Variables** and add:

| Name | Value |
|---|---|
| `RESEND_API_KEY` | API key from Resend |
| `RESEND_FROM` | A sender on a domain verified in Resend, for example `JENIQA Website <website@jeniqa.com>` |
| `ENQUIRY_TO` | Where enquiries go. Defaults to `info@jeniqa.com` |
| `NEXT_PUBLIC_SITE_URL` | `https://www.jeniqa.com` once the domain is connected |

Redeploy after adding them. Until they are set, the forms say they could not send and show the email address and WhatsApp button instead, so no enquiry is silently lost.

## Custom domain

In Vercel, **Project > Settings > Domains**, add `jeniqa.com` and `www.jeniqa.com`, then set the DNS records Vercel shows at the domain registrar. Email for info@jeniqa.com stays with the current mail host; only the website records change (A record for the root and CNAME for www), never the MX records.

## Before launch

Fill in the placeholders in `lib/site.ts`: phone, WhatsApp number, street address, map search text, office hours and social links. Confirm the tax rates in the same file (preset to February 2025).

## Home page video

The home page background is a free Mixkit clip (commercial use allowed, no credit needed), set in `lib/site.ts` as `heroVideo`. To use the client's own footage, add an .mp4 (720p, under about 10 MB, no sound) to `public/media/` and set `heroVideo: "/media/your-file.mp4"`. People who ask for reduced motion or data saving see the plain navy background instead.

## Photos

Photos are free Unsplash photos (free for commercial use, no credit required), listed with their photographers in `lib/media.ts`. `servicePhoto` and `industryPhoto` there decide which photo goes with each service and industry. To use the client's own photos, add them to `public/media/` and change the `src` of the matching entry to `/media/<file>.jpg`.
