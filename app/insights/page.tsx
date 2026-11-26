import type { Metadata } from "next";
import { InsightsList } from "@/components/InsightsList";
import { Cta, PageHero } from "@/components/ui";
import { posts } from "@/lib/content";

export const metadata: Metadata = {
  title: "Insights",
  description: "Tax alerts, tax case analyses, ESG reporting updates, guides and news from JENIQA.",
  alternates: { canonical: "/insights" },
};

export default function InsightsPage() {
  return (
    <>
      <PageHero eyebrow="Insights" title="Tax, ESG and business, explained plainly" text="Tax alerts, case analyses, ESG reporting updates, practical guides and news from our teams." />
      <section className="section section--soft">
        <div className="container"><InsightsList posts={posts} /></div>
      </section>
      <Cta title="Want these in your inbox?" text="Sign up in the footer for tax alerts and ESG updates about twice a month, or talk to us about your own situation." />
    </>
  );
}
