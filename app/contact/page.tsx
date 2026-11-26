import { photos } from "@/lib/media";
import type { Metadata } from "next";
import { ContactSide } from "@/components/ContactSide";
import { EnquiryForm } from "@/components/Forms";
import { PageHero } from "@/components/ui";

export const metadata: Metadata = {
  title: "Contact",
  description: "Call, email, WhatsApp or send the form. JENIQA replies within one working day.",
  alternates: { canonical: "/contact" },
};

export default function ContactPage() {
  return (
    <>
      <PageHero photo={photos.handshake} eyebrow="Contact" title="Let's talk" text="Call, email, WhatsApp or send the form. We reply within one working day." />
      <section className="section section--soft">
        <div className="container contact">
          <div className="contact__form reveal">
            <h2 style={{ fontSize: "1.6rem" }}>Send us a message</h2>
            <EnquiryForm kind="contact" />
          </div>
          <ContactSide />
        </div>
      </section>
    </>
  );
}
