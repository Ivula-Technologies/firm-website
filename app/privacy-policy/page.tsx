import type { Metadata } from "next";
import { Legal } from "@/components/Legal";
import { legal } from "@/lib/content";

export const metadata: Metadata = { title: "Privacy policy", alternates: { canonical: "/privacy-policy" } };

export default function Page() {
  return <Legal title="Privacy policy" md={legal.privacy} updated="26 November 2026" />;
}
