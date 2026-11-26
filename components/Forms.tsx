"use client";

import Link from "next/link";
import { useState } from "react";
import { Icon, WhatsAppIcon } from "./Icon";
import { site, whatsappUrl } from "@/lib/site";

type State = { status: "idle" | "sending" | "sent" | "error"; message?: string };

async function send(kind: string, form: HTMLFormElement): Promise<State> {
  const data = Object.fromEntries(new FormData(form).entries());
  try {
    const res = await fetch("/api/enquiry", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ kind, ...data }),
    });
    const json = await res.json().catch(() => ({}));
    if (res.ok) return { status: "sent" };
    return { status: "error", message: json.error || "Something went wrong." };
  } catch {
    return { status: "error", message: "We could not reach the server." };
  }
}

function Fallback({ message }: { message?: string }) {
  return (
    <div className="form__alert form__alert--error" role="alert">
      <p><strong>{message || "Your message did not send."}</strong> Please reach us directly instead:</p>
      <p className="form__alt">
        <a href={`mailto:${site.email}`}><Icon name="mail" size={16} /> {site.email}</a>
        <a href={whatsappUrl()} target="_blank" rel="noopener"><WhatsAppIcon size={16} /> WhatsApp</a>
      </p>
    </div>
  );
}

const SERVICES = [
  "Accounting and bookkeeping",
  "Payroll and HR support",
  "Tax",
  "Company secretarial and compliance",
  "Business advisory",
  "ESG and sustainability advisory",
  "Not sure yet",
];

export function EnquiryForm({ kind = "contact", service = "" }: { kind?: "contact" | "proposal"; service?: string }) {
  const [state, setState] = useState<State>({ status: "idle" });

  if (state.status === "sent") {
    return (
      <div className="form__done" role="status">
        <span className="form__done-icon"><Icon name="circle-check" size={28} /></span>
        <h3>Thank you. We have your message.</h3>
        <p>We will reply within one working day. If it is urgent, call us on {site.phone} or message us on WhatsApp.</p>
      </div>
    );
  }

  return (
    <form
      className="form"
      onSubmit={async (e) => {
        e.preventDefault();
        setState({ status: "sending" });
        setState(await send(kind, e.currentTarget));
      }}
    >
      <div className="form__grid">
        <label className="field">
          <span>Your name <b aria-hidden="true">*</b></span>
          <input name="name" required autoComplete="name" maxLength={120} />
        </label>
        <label className="field">
          <span>{kind === "proposal" ? "Organisation" : "Organisation (optional)"}{kind === "proposal" && <b aria-hidden="true"> *</b>}</span>
          <input name="organisation" required={kind === "proposal"} autoComplete="organization" maxLength={160} />
        </label>
        <label className="field">
          <span>Email <b aria-hidden="true">*</b></span>
          <input name="email" type="email" required autoComplete="email" maxLength={160} />
        </label>
        <label className="field">
          <span>Phone (optional)</span>
          <input name="phone" type="tel" autoComplete="tel" maxLength={40} />
        </label>
        <label className="field field--full">
          <span>Service</span>
          <select name="service" defaultValue={service || ""}>
            <option value="">Choose a service</option>
            {SERVICES.map((s) => <option key={s}>{s}</option>)}
          </select>
        </label>
        {kind === "proposal" && (
          <>
            <label className="field">
              <span>Organisation size</span>
              <select name="size" defaultValue="">
                <option value="">Choose</option>
                <option>Just starting</option>
                <option>1 to 10 staff</option>
                <option>11 to 50 staff</option>
                <option>51 to 200 staff</option>
                <option>More than 200 staff</option>
              </select>
            </label>
            <label className="field">
              <span>When do you need help?</span>
              <select name="timing" defaultValue="">
                <option value="">Choose</option>
                <option>As soon as possible</option>
                <option>Within a month</option>
                <option>In the next three months</option>
                <option>Just exploring</option>
              </select>
            </label>
          </>
        )}
        <label className="field field--full">
          <span>{kind === "proposal" ? "What do you need help with?" : "Your message"} <b aria-hidden="true">*</b></span>
          <textarea name="message" required rows={5} maxLength={5000} />
        </label>
        <label className="hp" aria-hidden="true">
          Leave this empty <input name="website" tabIndex={-1} autoComplete="off" />
        </label>
      </div>
      <label className="check">
        <input type="checkbox" name="consent" value="yes" required />
        <span>I agree to {site.legalName} using these details to respond to me, as set out in the <Link href="/privacy-policy">privacy policy</Link>.</span>
      </label>
      {state.status === "error" && <Fallback message={state.message} />}
      <button className="btn btn--primary btn--lg" disabled={state.status === "sending"}>
        {state.status === "sending" ? "Sending…" : kind === "proposal" ? "Request my proposal" : "Send message"}
        <Icon name="send" size={18} />
      </button>
    </form>
  );
}

export function NewsletterForm() {
  const [state, setState] = useState<State>({ status: "idle" });
  if (state.status === "sent") return <p className="newsletter__done" role="status"><Icon name="circle-check" size={18} /> You are on the list. Thank you.</p>;
  return (
    <form
      className="newsletter"
      onSubmit={async (e) => {
        e.preventDefault();
        setState({ status: "sending" });
        setState(await send("newsletter", e.currentTarget));
      }}
    >
      <label className="sr-only" htmlFor="nl-email">Your email address</label>
      <input id="nl-email" name="email" type="email" required placeholder="Your email address" autoComplete="email" />
      <input type="hidden" name="consent" value="yes" />
      <label className="hp" aria-hidden="true">Leave this empty <input name="website" tabIndex={-1} autoComplete="off" /></label>
      <button className="btn btn--primary" disabled={state.status === "sending"}>{state.status === "sending" ? "…" : "Subscribe"}</button>
      {state.status === "error" && <p className="newsletter__err" role="alert">That did not go through. Email us at {site.email} instead.</p>}
      <p className="newsletter__fine">By subscribing you agree to our <Link href="/privacy-policy">privacy policy</Link>. Unsubscribe at any time.</p>
    </form>
  );
}
