import { site } from "@/lib/site";

/**
 * Form handler: contact, proposal and newsletter forms post here.
 * Sends an email to ENQUIRY_TO through Resend (RESEND_API_KEY, RESEND_FROM),
 * plus a short acknowledgement to the sender for contact and proposal forms.
 */

const LABELS: Record<string, string> = {
  name: "Name",
  organisation: "Organisation",
  email: "Email",
  phone: "Phone",
  service: "Service",
  size: "Organisation size",
  timing: "Timing",
  message: "Message",
};
const TITLES: Record<string, string> = {
  contact: "New enquiry",
  proposal: "New proposal request",
  newsletter: "Newsletter sign-up",
};

function clean(v: unknown, max = 5000) {
  return typeof v === "string" ? v.replace(/\r/g, "").trim().slice(0, max) : "";
}

async function resend(payload: Record<string, unknown>) {
  const res = await fetch("https://api.resend.com/emails", {
    method: "POST",
    headers: { Authorization: `Bearer ${process.env.RESEND_API_KEY}`, "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error(`Resend ${res.status}: ${await res.text()}`);
}

export async function POST(req: Request) {
  let body: Record<string, unknown>;
  try {
    body = await req.json();
  } catch {
    return Response.json({ error: "That did not look like a form." }, { status: 400 });
  }

  // Bots fill the hidden field; pretend it worked.
  if (clean(body.website)) return Response.json({ ok: true });

  const kind = ["contact", "proposal", "newsletter"].includes(String(body.kind)) ? String(body.kind) : "contact";
  const fields: Record<string, string> = {};
  for (const k of Object.keys(LABELS)) fields[k] = clean(body[k], k === "message" ? 5000 : 200);

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email)) {
    return Response.json({ error: "Please enter a valid email address." }, { status: 400 });
  }
  if (kind !== "newsletter" && (!fields.name || !fields.message)) {
    return Response.json({ error: "Please fill in your name and message." }, { status: 400 });
  }
  if (clean(body.consent) !== "yes") {
    return Response.json({ error: "Please tick the consent box." }, { status: 400 });
  }

  if (!process.env.RESEND_API_KEY || !process.env.RESEND_FROM) {
    console.error("[enquiry] RESEND_API_KEY or RESEND_FROM is not set; form could not be emailed", { kind, email: fields.email });
    return Response.json({ error: "Our form is not connected yet." }, { status: 503 });
  }

  const to = (process.env.ENQUIRY_TO || site.email).split(",").map((s) => s.trim()).filter(Boolean);
  const lines = Object.entries(LABELS)
    .filter(([k]) => fields[k])
    .map(([k, label]) => `${label}: ${k === "message" ? "\n" + fields[k] : fields[k]}`);

  try {
    await resend({
      from: process.env.RESEND_FROM,
      to,
      reply_to: fields.email,
      subject: `[${site.brand}] ${TITLES[kind]}${fields.name ? `: ${fields.name}` : ""}`,
      text: `${lines.join("\n")}\n\nSent from ${site.url}`,
    });
    if (kind !== "newsletter") {
      await resend({
        from: process.env.RESEND_FROM,
        to: [fields.email],
        reply_to: to[0],
        subject: `We have your message: ${site.brand}`,
        text: `Dear ${fields.name},\n\nThank you for contacting ${site.brand}. We have received your message and will reply within one working day.\n\nIf it is urgent, call us on ${site.phone}.\n\nKind regards,\n${site.legalName}\n${site.url}`,
      }).catch((e) => console.error("[enquiry] acknowledgement failed", e));
    }
  } catch (e) {
    console.error("[enquiry] send failed", e);
    return Response.json({ error: "We could not send your message." }, { status: 502 });
  }
  return Response.json({ ok: true });
}
