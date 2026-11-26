import { photos } from "@/lib/media";
import type { Metadata } from "next";
import { Icon } from "@/components/Icon";
import { Cta, PageHero, SectionHead } from "@/components/ui";
import { promises } from "@/lib/content";
import { site } from "@/lib/site";

export const metadata: Metadata = {
  title: "About us",
  description: `${site.legalName} is a Kenyan professional services firm for accounting, tax, advisory and ESG.`,
  alternates: { canonical: "/about" },
};

const THREE = [
  { icon: "coins", title: "Money", text: "Accurate books, management accounts and payroll, so you always know where you stand." },
  { icon: "scale", title: "Obligations", text: "Tax, company filings and reporting, met in full and on time, so nothing comes back to bite you." },
  { icon: "sprout", title: "Impact", text: "Advice on where to go next, and help measuring and reporting the difference you make." },
];
const STEPS = [
  { title: "Listen", text: "We start with your business, not our checklist." },
  { title: "Clarify", text: "We show you where you stand, in plain figures." },
  { title: "Deliver", text: "We do the work, on time and to standard." },
  { title: "Stay", text: "We remain available after the report is signed." },
];

export default function AboutPage() {
  return (
    <>
      <PageHero photo={photos.nairobi} eyebrow="About us" title="A Kenyan firm that counts what matters" text="We keep the books, handle the tax, advise on the next move, and help our clients measure and report their impact." />
      <section className="section">
        <div className="container split">
          <div className="prose reveal">
            <p>{site.legalName} is a Kenyan professional services firm. We keep the books, handle the tax, advise on the next move, and help our clients measure and report their impact.</p>
            <p>We work with businesses, institutions and individuals who want their affairs in order and their decisions well informed. Whatever the assignment, we do the same thing: we count what matters, and we explain it plainly.</p>
          </div>
          <div className="mv reveal">
            <div className="mv__card"><h3>Our mission</h3><p>We help businesses count what matters: their money, their obligations and their impact.</p></div>
            <div className="mv__card"><h3>Our vision</h3><p>Kenyan businesses that are profitable, compliant and proud of how they got there.</p></div>
          </div>
        </div>
      </section>
      <section className="section section--soft">
        <div className="container">
          <SectionHead eyebrow="What we do" title="Three things to account for" />
          <div className="three">
            {THREE.map((t) => (
              <div key={t.title} className="svc-item reveal">
                <h3>{t.title}</h3>
                <p>{t.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
      <section className="section section--ink">
        <div className="container">
          <SectionHead eyebrow="Our values" title="Four promises we keep" light />
          <div className="promises">
            {promises.map((p, i) => (
              <div key={p.title} className="promise reveal">
                <span className="promise__n">0{i + 1}</span>
                <h3>{p.title}</h3>
                <p>{p.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
      <section className="section" id="our-approach">
        <div className="container">
          <SectionHead eyebrow="Our approach" title="Four steps, the same on every assignment" text="These apply whether we are closing your monthly books, defending a KRA assessment or preparing your first sustainability report." />
          <ol className="timeline timeline--light">
            {STEPS.map((s) => <li key={s.title} className="reveal"><h3>{s.title}</h3><p>{s.text}</p></li>)}
          </ol>
        </div>
      </section>
      <Cta />
    </>
  );
}
