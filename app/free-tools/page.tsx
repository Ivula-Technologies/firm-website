import type { Metadata } from "next";
import { Icon } from "@/components/Icon";
import { Cta, PageHero, SectionHead } from "@/components/ui";
import { Deadlines, EsgCheck, PayeTool, VatTool } from "@/components/Tools";
import { upcomingDeadlines } from "@/lib/deadlines";
import { rates } from "@/lib/site";

export const metadata: Metadata = {
  title: "Free tools: PAYE, VAT, KRA deadlines and ESG check",
  description: "Free calculators for Kenyan businesses: PAYE and take-home pay, VAT, a live KRA deadline countdown and an ESG readiness check.",
  alternates: { canonical: "/free-tools" },
};

export default function ToolsPage() {
  return (
    <>
      <PageHero eyebrow="Free tools" title="Work it out in a minute" text="Quick, free calculators and reminders for Kenyan businesses. Nothing to sign up for.">
        <nav className="chipnav" aria-label="Tools on this page">
          <a href="#paye"><Icon name="calculator" size={16} /> PAYE</a>
          <a href="#vat"><Icon name="receipt-text" size={16} /> VAT</a>
          <a href="#deadlines"><Icon name="calendar" size={16} /> KRA deadlines</a>
          <a href="#esg-check"><Icon name="leaf" size={16} /> ESG check</a>
        </nav>
      </PageHero>
      <section className="section section--soft">
        <div className="container">
          <div className="tool-block" id="paye">
            <SectionHead eyebrow="Payroll" title="PAYE and take-home pay" text="Type a gross monthly salary. We take off NSSF, SHIF and the housing levy, then PAYE after personal relief." />
            <div className="panel reveal"><PayeTool /></div>
          </div>
          <div className="tool-block" id="vat">
            <SectionHead eyebrow="VAT" title="VAT calculator" text="Add VAT to a price, or take it out of an amount that already includes it." />
            <div className="panel reveal"><VatTool /></div>
          </div>
          <div className="tool-block" id="deadlines">
            <SectionHead eyebrow="Compliance" title="KRA deadlines coming up" text="The main filing and payment dates, counted down from today in Nairobi time." />
            <div className="deadlines-wrap reveal"><Deadlines initial={upcomingDeadlines()} /></div>
          </div>
          <div className="tool-block" id="esg-check">
            <SectionHead eyebrow="ESG" title="How ready are you for ESG reporting?" text="Lenders, investors and large customers are asking. Answer six questions to see where you stand and what to do next." />
            <EsgCheck />
          </div>
          <aside className="disclaimer">
            <Icon name="info" size={20} />
            <p>Rates checked as at <strong>{rates.label}</strong>. These tools give an estimate for a typical case and are not tax advice. Allowances, reliefs, exemptions and your own facts can change the answer, so talk to us before you rely on a figure.</p>
          </aside>
        </div>
      </section>
      <Cta title="Want us to run the numbers properly?" text="Send us your figures and we will come back within one working day with a clear answer and a fixed quote." />
    </>
  );
}
