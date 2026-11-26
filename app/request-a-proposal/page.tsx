import type { Metadata } from "next";
import { ContactSide } from "@/components/ContactSide";
import { EnquiryForm } from "@/components/Forms";
import { PageHero } from "@/components/ui";

export const metadata: Metadata = {
  title: "Request a proposal",
  description: "Tell us about your organisation and what you need. We will come back with a clear scope and fee.",
  alternates: { canonical: "/request-a-proposal" },
};

export default async function ProposalPage({ searchParams }: { searchParams: Promise<{ service?: string }> }) {
  const { service } = await searchParams;
  return (
    <>
      <PageHero eyebrow="Request a proposal" title="Tell us what you need" text="Tell us about your organisation and what you need. We will come back within one working day with a clear scope and a fixed fee." />
      <section className="section section--soft">
        <div className="container contact">
          <div className="contact__form reveal">
            <h2 style={{ fontSize: "1.6rem" }}>Your details</h2>
            <EnquiryForm kind="proposal" service={service} />
          </div>
          <ContactSide />
        </div>
      </section>
    </>
  );
}
