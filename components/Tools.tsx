"use client";

import Link from "next/link";
import { useEffect, useMemo, useState } from "react";
import { Icon } from "./Icon";
import { rates } from "@/lib/site";
import { longDate, phrase, shortDate, upcomingDeadlines, type Deadline } from "@/lib/deadlines";

const nf = new Intl.NumberFormat("en-KE", { maximumFractionDigits: 0 });
const money = (n: number) => nf.format(Math.round(n));
const num = (v: string) => {
  const n = parseFloat(v.replace(/,/g, ""));
  return isFinite(n) && n > 0 ? n : 0;
};

export function paye(gross: number) {
  const r = rates.nssf / 100;
  const nssf = r * Math.min(gross, rates.lel) + r * Math.max(0, Math.min(gross, rates.uel) - rates.lel);
  const shif = gross > 0 ? Math.max(rates.shifMin, (gross * rates.shif) / 100) : 0;
  const ahl = (gross * rates.ahl) / 100;
  const taxable = Math.max(0, gross - nssf - shif - ahl);
  let tax = 0;
  let lower = 0;
  for (const b of rates.bands) {
    const upper = b.upto === null ? Infinity : b.upto;
    if (taxable > lower) tax += ((Math.min(taxable, upper) - lower) * b.rate) / 100;
    lower = upper;
    if (taxable <= upper) break;
  }
  const p = Math.max(0, tax - rates.relief);
  return { gross, nssf, shif, ahl, paye: p, net: gross - nssf - shif - ahl - p, employer: gross + nssf + ahl };
}

export function PayeTool() {
  const [value, setValue] = useState("100000");
  const g = num(value);
  const r = paye(g);
  const tot = g || 1;
  const stat = r.nssf + r.shif + r.ahl;
  return (
    <div className="tool tool--paye">
      <div className="tool__inputs">
        <label className="field">
          <span>Gross monthly salary (KES)</span>
          <input inputMode="numeric" value={value} onChange={(e) => setValue(e.target.value)} />
        </label>
        <input
          type="range" min={10000} max={1000000} step={5000} aria-label="Salary slider"
          value={Math.min(1000000, Math.max(10000, g))}
          onChange={(e) => setValue(e.target.value)}
          style={{ ["--fill" as string]: `${((Math.min(1000000, Math.max(10000, g)) - 10000) / 990000) * 100}%` }}
        />
        <div className="chips">
          {[30000, 50000, 100000, 250000, 500000].map((v) => (
            <button key={v} type="button" className={g === v ? "is-on" : ""} onClick={() => setValue(String(v))}>{money(v)}</button>
          ))}
        </div>
      </div>
      <div className="tool__result">
        <p className="tool__label">Take-home pay</p>
        <p className="tool__big">KES {money(r.net)}</p>
        <p className="tool__sub">KES {money(r.net * 12)} a year · effective PAYE rate {(g ? (r.paye / g) * 100 : 0).toFixed(1)}%</p>
        <div className="stackbar" aria-hidden="true">
          <span className="stackbar__net" style={{ width: `${Math.max(0, (r.net / tot) * 100)}%` }} />
          <span className="stackbar__paye" style={{ width: `${(r.paye / tot) * 100}%` }} />
          <span className="stackbar__stat" style={{ width: `${(stat / tot) * 100}%` }} />
        </div>
        <dl className="breakdown">
          <div><dt><i className="dot dot--paye" />PAYE</dt><dd>{money(r.paye)}</dd></div>
          <div><dt><i className="dot dot--stat" />NSSF</dt><dd>{money(r.nssf)}</dd></div>
          <div><dt><i className="dot dot--stat" />SHIF</dt><dd>{money(r.shif)}</dd></div>
          <div><dt><i className="dot dot--stat" />Housing levy</dt><dd>{money(r.ahl)}</dd></div>
          <div className="breakdown__total"><dt>Total cost to employer</dt><dd>{money(r.employer)}</dd></div>
        </dl>
      </div>
    </div>
  );
}

export function VatTool() {
  const [value, setValue] = useState("10000");
  const [mode, setMode] = useState<"add" | "remove">("add");
  const a = num(value);
  const rate = rates.vat / 100;
  const net = mode === "remove" ? a / (1 + rate) : a;
  const vat = net * rate;
  return (
    <div className="tool tool--vat">
      <div className="tool__inputs">
        <div className="seg" role="radiogroup" aria-label="Calculation">
          <button type="button" role="radio" aria-checked={mode === "add"} className={mode === "add" ? "is-on" : ""} onClick={() => setMode("add")}>Add VAT</button>
          <button type="button" role="radio" aria-checked={mode === "remove"} className={mode === "remove" ? "is-on" : ""} onClick={() => setMode("remove")}>Remove VAT</button>
        </div>
        <label className="field">
          <span>{mode === "add" ? "Price before VAT (KES)" : "Amount including VAT (KES)"}</span>
          <input inputMode="decimal" value={value} onChange={(e) => setValue(e.target.value)} />
        </label>
      </div>
      <div className="tool__result">
        <dl className="vatsum">
          <div><dt>Net amount</dt><dd>{money(net)}</dd></div>
          <div><dt>VAT at {rates.vat}%</dt><dd>{money(vat)}</dd></div>
          <div className="vatsum__total"><dt>Total</dt><dd>KES {money(net + vat)}</dd></div>
        </dl>
      </div>
    </div>
  );
}

/** Deadlines: rendered from the build date, then recounted in the browser so a cached page stays right. */
export function Deadlines({ initial, limit = 0, compact = false }: { initial: Deadline[]; limit?: number; compact?: boolean }) {
  const [items, setItems] = useState(initial);
  useEffect(() => setItems(upcomingDeadlines()), []);
  const [next, ...rest] = items;
  if (!next) return null;
  const wd = new Date(next.when).getUTCDay();
  const list = limit ? rest.slice(0, limit) : rest;
  return (
    <div className={`deadlines${compact ? " deadlines--compact" : ""}`}>
      <div className="deadlines__next">
        <p className="deadlines__label">Next KRA deadline</p>
        <p className="deadlines__count"><strong>{next.days}</strong> <span>{next.days === 1 ? "day" : "days"}</span></p>
        <p className="deadlines__name">{next.name}</p>
        <p className="deadlines__day">{longDate(next.when)}</p>
        {(wd === 0 || wd === 6) && <p className="deadlines__flag">Falls on a {wd === 0 ? "Sunday" : "Saturday"}: file early</p>}
      </div>
      <ul className="deadlines__list">
        {list.map((d) => (
          <li key={d.name}>
            <span className="deadlines__date">{shortDate(d.when)}</span>
            <span className="deadlines__what"><strong>{d.name}</strong><small>{d.note}</small></span>
            <span className="deadlines__when">{phrase(d.days)}</span>
          </li>
        ))}
      </ul>
    </div>
  );
}

const QUESTIONS = [
  ["Do you know where your business uses the most energy, fuel and water?", "Measure your energy, fuel and water use for one year. It is the base of your carbon footprint."],
  ["Have you agreed which ESG topics matter most to your business and its stakeholders?", "Run a short materiality assessment so you report on what matters, not on everything."],
  ["Do you have written policies on the environment, health and safety, and anti-bribery?", "Put the basic policies in writing and have the board approve them."],
  ["Does a named person own ESG, with management or the board reviewing progress?", "Name an owner and put ESG on the board or management agenda each quarter."],
  ["Do you collect ESG data regularly, the same way each time?", "Set up a simple data sheet with sources and owners, filled in every quarter."],
  ["Have you shared ESG information with a lender, investor, customer or in an annual report?", "Prepare a short ESG summary you can send when lenders or customers ask."],
];
const BANDS = [
  { max: 4, name: "Getting started", text: "You have the basics to put in place. That is normal for most Kenyan businesses today, and a few steps make a big difference." },
  { max: 8, name: "Building up", text: "Good foundations. Close the gaps below and you will be able to answer most lender and customer questions with confidence." },
  { max: 12, name: "Report-ready", text: "You are well placed to publish an ESG report or a sustainability section in your annual report." },
];

export function EsgCheck() {
  const [answers, setAnswers] = useState<(number | null)[]>(QUESTIONS.map(() => null));
  const answered = answers.filter((a) => a !== null).length;
  const score = answers.reduce<number>((s, a) => s + (a ?? 0), 0);
  const done = answered === QUESTIONS.length;
  const band = BANDS.find((b) => score <= b.max) || BANDS[2];
  const tips = useMemo(
    () => answers.map((a, i) => ({ a, t: QUESTIONS[i][1] })).filter((x) => x.a !== null && x.a < 2).sort((x, y) => (x.a as number) - (y.a as number)).slice(0, 3),
    [answers],
  );
  const circ = 2 * Math.PI * 52;
  const level = score <= 4 ? "low" : score <= 8 ? "mid" : "high";
  return (
    <div className="esg">
      <ol className="esg__qs">
        {QUESTIONS.map(([q], i) => (
          <li key={i} className={answers[i] !== null ? "is-answered" : ""}>
            <p>{q}</p>
            <div className="esg__opts" role="radiogroup" aria-label={q}>
              {["No", "Partly", "Yes"].map((label, v) => (
                <button
                  key={label} type="button" role="radio" aria-checked={answers[i] === v}
                  className={answers[i] === v ? "is-on" : ""}
                  onClick={() => setAnswers(answers.map((x, j) => (j === i ? v : x)))}
                >{label}</button>
              ))}
            </div>
          </li>
        ))}
      </ol>
      <aside className="esg__result" aria-live="polite">
        <div className="ring" data-level={level}>
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle cx="60" cy="60" r="52" className="ring__track" />
            <circle cx="60" cy="60" r="52" className="ring__bar" style={{ strokeDasharray: circ, strokeDashoffset: circ * (1 - score / 12) }} />
          </svg>
          <p className="ring__score"><strong>{score}</strong><span>/ 12</span></p>
        </div>
        <p className="esg__band">{done ? band.name : answered ? `Keep going: ${QUESTIONS.length - answered} to go` : "Answer the questions to see your score"}</p>
        {done && (
          <>
            <p className="esg__advice">{band.text}</p>
            <p className="esg__h">Your next steps</p>
            <ol className="esg__steps">
              {(tips.length ? tips.map((t) => t.t) : ["Have your data checked independently before you publish, so lenders and investors can rely on it."]).map((t) => <li key={t}>{t}</li>)}
            </ol>
            <Link href="/request-a-proposal" className="btn btn--primary btn--block">Get help with ESG <Icon name="arrow-right" size={18} /></Link>
          </>
        )}
      </aside>
    </div>
  );
}
