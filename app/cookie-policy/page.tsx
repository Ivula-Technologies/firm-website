import type { Metadata } from "next";
import { Legal } from "@/components/Legal";
import { legal } from "@/lib/content";

export const metadata: Metadata = { title: "Cookie policy", alternates: { canonical: "/cookie-policy" } };

export default function Page() {
  return <Legal title="Cookie policy" md={legal.cookies} updated="26 November 2026" />;
}
