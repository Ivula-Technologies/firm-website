import type { Metadata } from "next";
import { Legal } from "@/components/Legal";
import { legal } from "@/lib/content";

export const metadata: Metadata = { title: "Terms of use and disclaimer", alternates: { canonical: "/terms-of-use" } };

export default function Page() {
  return <Legal title="Terms of use and disclaimer" md={legal.terms} updated="26 November 2026" />;
}
